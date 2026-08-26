<?php

namespace App\Http\Controllers\APIs\Concerns;

use App\Http\Resources\CarResource;
use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait InteractsWithCarListings
{
    protected function carListingRelations(): array
    {
        return ['brand', 'categories', 'locations', 'driverPages'];
    }

    protected function carSortableColumns(): array
    {
        return ['id', 'name_en', 'price_daily', 'price_weekly', 'price_monthly', 'sorting', 'featured_sorting', 'fleet_sorting', 'stock'];
    }

    protected function buildPublicCarListingQuery(Request $request, ?callable $scope = null): Builder
    {
        $query = Car::query()->with($this->carListingRelations());

        if ($scope) {
            $scope($query);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $parts = preg_split('/\s+/', $search);

            $query->where(function (Builder $inner) use ($parts) {
                foreach ($parts as $part) {
                    $inner->where(function (Builder $q) use ($part) {
                        $q->where('cars.name_en', 'like', "%{$part}%")
                            ->orWhere('cars.name_ar', 'like', "%{$part}%")
                            ->orWhere('cars.slug', 'like', "%{$part}%")
                            ->orWhere('cars.model', 'like', "%{$part}%")
                            ->orWhereHas('brand', function (Builder $brandQuery) use ($part) {
                                $brandQuery->where('name_en', 'like', "%{$part}%")
                                    ->orWhere('name_ar', 'like', "%{$part}%");
                            })
                            ->orWhereHas('categories', function (Builder $categoryQuery) use ($part) {
                                $categoryQuery->where('name_en', 'like', "%{$part}%")
                                    ->orWhere('name_ar', 'like', "%{$part}%");
                            });
                    });
                }
            });
        }

        $brandIds = $this->resolveCarBrandIds($request);

        if ($brandIds !== []) {
            $query->whereIn('cars.brand_id', $brandIds);
        }

        $categoryIds = $this->resolveCarCategoryIds($request);

        if ($categoryIds !== []) {
            $query->whereHas('categories', function (Builder $categoryQuery) use ($categoryIds) {
                $categoryQuery->whereIn('categories.id', $categoryIds);
            });
        }

        if ($request->filled('featured')) {
            $query->where('cars.featured', (int) $request->input('featured'));
        }

        if ($request->filled('stock_status')) {
            $request->input('stock_status') === 'in_stock'
                ? $query->where('cars.stock', '>', 0)
                : $query->where(function (Builder $stockQuery) {
                    $stockQuery->where('cars.stock', '<=', 0)
                        ->orWhereNull('cars.stock');
                });
        }

        if ($this->applyCarPriceSorting($query, $request)) {
            return $query;
        }

        $sortBy = $request->get('sort_by');
        $sortDirection = strtolower((string) $request->get('sort_direction', 'desc'));
        $sortDirection = in_array($sortDirection, ['asc', 'desc'], true) ? $sortDirection : 'desc';

        if (in_array($sortBy, $this->carSortableColumns(), true)) {
            if ($sortBy === 'sorting') {
                return $this->applyBrandAwareCarOrdering($query, $sortDirection);
            }

            return $query->reorder()->orderBy('cars.' . $sortBy, $sortDirection);
        }

        return $this->applyFeaturedThenFleetCarOrdering($query);
    }

    protected function paginatedCarListingPayload(Request $request, ?callable $scope = null): array
    {
        $perPage = max(1, min((int) $request->get('per_page', 15), 100));

        $cars = $this->buildPublicCarListingQuery($request, $scope)
            ->paginate($perPage)
            ->appends($request->query());

        return [
            'cars' => CarResource::collection($cars)->resolve(),
            'pagination' => [
                'current_page' => $cars->currentPage(),
                'last_page' => $cars->lastPage(),
                'per_page' => $cars->perPage(),
                'total' => $cars->total(),
            ],
            'brand_list' => $this->allBrandList(),
            'categories_list' => $this->allCategoryList(),
        ];
    }

    protected function resolveCarCategoryIds(Request $request): array
    {
        $categoryIds = $request->input('category_ids', $request->input('category_id', []));

        if (!is_array($categoryIds)) {
            $categoryIds = explode(',', (string) $categoryIds);
        }

        return collect($categoryIds)
            ->map(fn ($categoryId) => (int) $categoryId)
            ->filter(fn (int $categoryId) => $categoryId > 0)
            ->unique()
            ->values()
            ->all();
    }

    protected function resolveCarBrandIds(Request $request): array
    {
        $brandIds = $request->input('brand_ids', $request->input('brand_id', []));

        if (!is_array($brandIds)) {
            $brandIds = explode(',', (string) $brandIds);
        }

        return collect($brandIds)
            ->map(fn ($brandId) => (int) $brandId)
            ->filter(fn (int $brandId) => $brandId > 0)
            ->unique()
            ->values()
            ->all();
    }

    protected function applyCarPriceSorting(Builder $query, Request $request): bool
    {
        $sortBy = strtolower(trim((string) $request->get('sort_by', '')));
        $sortDirection = strtolower(trim((string) $request->get('sort_direction', '')));

        $priceHighToLowValues = ['high_to_low', 'price_high_to_low', 'price-desc', 'price_desc'];
        $priceLowToHighValues = ['low_to_high', 'price_low_to_high', 'price-asc', 'price_asc'];

        if (in_array($sortBy, $priceHighToLowValues, true)) {
            $query->reorder()->orderByRaw('CAST(cars.price_daily AS DECIMAL(10,2)) DESC');

            return true;
        }

        if (in_array($sortBy, $priceLowToHighValues, true)) {
            $query->reorder()->orderByRaw('CAST(cars.price_daily AS DECIMAL(10,2)) ASC');

            return true;
        }

        if ($sortBy === 'price_daily') {
            $direction = in_array($sortDirection, ['asc', 'desc'], true) ? $sortDirection : 'asc';

            $query->reorder()->orderByRaw('CAST(cars.price_daily AS DECIMAL(10,2)) ' . strtoupper($direction));

            return true;
        }

        return false;
    }

    protected function applyBrandAwareCarOrdering(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query
            ->reorder()
            ->leftJoin('brands', 'brands.id', '=', 'cars.brand_id')
            ->select('cars.*')
            ->orderByRaw('CASE WHEN brands.sorting IS NULL THEN 1 ELSE 0 END ASC')
            ->orderByRaw('CAST(COALESCE(NULLIF(brands.sorting, \'\'), \'999999\') AS UNSIGNED) ' . strtoupper($direction))
            ->orderByRaw('LOWER(COALESCE(brands.name_en, "")) ASC')
            ->orderByRaw('CASE WHEN cars.sorting IS NULL OR TRIM(CAST(cars.sorting AS CHAR)) = "" THEN 1 ELSE 0 END ASC')
            ->orderByRaw('CASE WHEN cars.sorting IS NULL OR TRIM(CAST(cars.sorting AS CHAR)) = "" THEN 999999 ELSE CAST(cars.sorting AS UNSIGNED) END ' . strtoupper($direction))
            ->orderByRaw('LOWER(COALESCE(cars.name_en, "")) ASC')
            ->orderByDesc('cars.id');
    }

    protected function applyFleetAwareCarOrdering(Builder $query): Builder
    {
        return $query
            ->reorder()
            ->orderByRaw('CASE WHEN cars.fleet_sorting IS NULL OR TRIM(CAST(cars.fleet_sorting AS CHAR)) = "" THEN 1 ELSE 0 END ASC')
            ->orderByRaw('CASE WHEN cars.fleet_sorting IS NULL OR TRIM(CAST(cars.fleet_sorting AS CHAR)) = "" THEN 999999 ELSE CAST(cars.fleet_sorting AS UNSIGNED) END ASC')
            ->orderByRaw('CASE WHEN cars.brand_id IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('cars.brand_id')
            ->orderByRaw('CASE WHEN cars.sorting IS NULL OR TRIM(CAST(cars.sorting AS CHAR)) = "" THEN 1 ELSE 0 END ASC')
            ->orderByRaw('CASE WHEN cars.sorting IS NULL OR TRIM(CAST(cars.sorting AS CHAR)) = "" THEN 999999 ELSE CAST(cars.sorting AS UNSIGNED) END ASC')
            ->orderByRaw('LOWER(COALESCE(cars.name_en, "")) ASC')
            ->orderByDesc('cars.id');
    }

    protected function applyFeaturedThenFleetCarOrdering(Builder $query): Builder
    {
        return $query
            ->reorder()
            ->orderByRaw('
                CASE
                    WHEN cars.featured = 1 THEN 0
                    WHEN cars.fleet_sorting IS NOT NULL AND TRIM(CAST(cars.fleet_sorting AS CHAR)) <> "" THEN 1
                    ELSE 2
                END ASC
            ')
            ->orderByRaw('
                CASE
                    WHEN cars.featured = 1
                        AND cars.featured_sorting IS NOT NULL
                        AND TRIM(CAST(cars.featured_sorting AS CHAR)) <> ""
                    THEN CAST(cars.featured_sorting AS UNSIGNED)
                    ELSE 999999
                END ASC
            ')
            ->orderByRaw('
                CASE
                    WHEN cars.fleet_sorting IS NOT NULL
                        AND TRIM(CAST(cars.fleet_sorting AS CHAR)) <> ""
                    THEN CAST(cars.fleet_sorting AS UNSIGNED)
                    ELSE 999999
                END ASC
            ')
            ->orderByRaw('
                CASE
                    WHEN cars.featured = 1 THEN 0
                    WHEN cars.fleet_sorting IS NOT NULL AND TRIM(CAST(cars.fleet_sorting AS CHAR)) <> "" THEN 0
                    WHEN cars.stock > 0 THEN 0
                    ELSE 1
                END ASC
            ')
            ->orderByRaw('CASE WHEN cars.sorting IS NULL OR TRIM(CAST(cars.sorting AS CHAR)) = "" THEN 1 ELSE 0 END ASC')
            ->orderByRaw('CASE WHEN cars.sorting IS NULL OR TRIM(CAST(cars.sorting AS CHAR)) = "" THEN 999999 ELSE CAST(cars.sorting AS UNSIGNED) END ASC')
            ->orderByRaw('LOWER(COALESCE(cars.name_en, "")) ASC')
            ->orderByDesc('cars.id');
    }
}