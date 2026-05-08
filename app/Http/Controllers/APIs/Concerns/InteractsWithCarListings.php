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
        return ['id', 'name_en', 'price_daily', 'price_weekly', 'price_monthly', 'sorting', 'featured_sorting', 'stock'];
    }

    protected function buildPublicCarListingQuery(Request $request, ?callable $scope = null): Builder
    {
        $query = Car::query()->with($this->carListingRelations());

        if ($scope) {
            $scope($query);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function (Builder $inner) use ($search) {
                $inner->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhereHas('brand', function (Builder $brandQuery) use ($search) {
                        $brandQuery->where('name_en', 'like', "%{$search}%")
                            ->orWhere('name_ar', 'like', "%{$search}%");
                    })
                    ->orWhereHas('categories', function (Builder $categoryQuery) use ($search) {
                        $categoryQuery->where('name_en', 'like', "%{$search}%")
                            ->orWhere('name_ar', 'like', "%{$search}%");
                    });
            });
        }

        $brandIds = $this->resolveCarBrandIds($request);
        if ($brandIds !== []) {
            $query->whereIn('brand_id', $brandIds);
        }

        $categoryIds = $this->resolveCarCategoryIds($request);
        if ($categoryIds !== []) {
            $query->whereHas('categories', fn (Builder $categoryQuery) => $categoryQuery->whereIn('categories.id', $categoryIds));
        }

        if ($request->filled('featured')) {
            $query->where('featured', (int) $request->input('featured'));
        }

        if ($request->filled('stock_status')) {
            $request->input('stock_status') === 'in_stock'
                ? $query->where('stock', '>', 0)
                : $query->where('stock', '<=', 0);
        }

        if ($this->applyCarPriceSorting($query, $request)) {
            return $query;
        }

        $sortBy = $request->get('sort_by');
        $sortDirection = strtolower((string) $request->get('sort_direction', 'desc'));
        $sortDirection = in_array($sortDirection, ['asc', 'desc'], true) ? $sortDirection : 'desc';

        if (in_array($sortBy, $this->carSortableColumns(), true)) {
            return $query->reorder()->orderBy((string) $sortBy, $sortDirection);
        }

        return $query->reorder()->orderedForListing();
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
            $query->reorder()->orderByRaw('CAST(price_daily AS DECIMAL(10,2)) DESC');

            return true;
        }

        if (in_array($sortBy, $priceLowToHighValues, true)) {
            $query->reorder()->orderByRaw('CAST(price_daily AS DECIMAL(10,2)) ASC');

            return true;
        }

        if ($sortBy === 'price_daily') {
            $direction = in_array($sortDirection, ['asc', 'desc'], true) ? $sortDirection : 'asc';
            $query->reorder()->orderByRaw('CAST(price_daily AS DECIMAL(10,2)) ' . strtoupper($direction));

            return true;
        }

        return false;
    }
}
