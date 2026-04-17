<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\CarRequest;
use App\Http\Resources\CarResource;
use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CarController extends BaseApiController
{
    protected string $modelClass = Car::class;
    protected string $resourceClass = CarResource::class;
    protected string $storeRequestClass = CarRequest::class;
    protected string $updateRequestClass = CarRequest::class;
    protected array $searchable = ['name_en', 'name_ar', 'slug', 'model'];
    protected array $with = ['brand', 'categories', 'locations', 'driverPages'];
    protected string $publicMessage = 'Car list fetched successfully';
    protected string $singleMessage = 'Car fetched successfully';
    protected string $storeMessage = 'Car created successfully';
    protected string $updateMessage = 'Car updated successfully';
    protected string $deleteMessage = 'Car deleted successfully';
    protected array $metaDataKeys = [
        'title_en' => ['messages_cars_title_en'],
        'title_ar' => ['messages_cars_title_ar'],
        'description_en' => ['messages_cars_brief_en'],
        'description_ar' => ['messages_cars_brief_ar'],
    ];

    protected array $sortable = ['id', 'name_en', 'price_daily', 'price_weekly', 'price_monthly', 'sorting', 'featured_sorting', 'stock'];

    protected function query(Request $request): Builder
    {
        $query = parent::query($request);

        if (! $request->filled('sort_by')) {
            $query->reorder()->orderedForListing();
        }

        return $query;
    }

    protected function applyFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('brand_id')) {
            $query->where('brand_id', (int) $request->brand_id);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn (Builder $q) => $q->where('categories.id', (int) $request->category_id));
        }

        if ($request->filled('featured')) {
            $query->where('featured', (int) $request->featured);
        }

        if ($request->filled('stock_status')) {
            $request->stock_status === 'in_stock'
                ? $query->where('stock', '>', 0)
                : $query->where('stock', '<=', 0);
        }

        if ($request->filled('min_price')) {
            $query->whereRaw('CAST(price_daily AS DECIMAL(10,2)) >= ?', [(float) $request->min_price]);
        }

        if ($request->filled('max_price')) {
            $query->whereRaw('CAST(price_daily AS DECIMAL(10,2)) <= ?', [(float) $request->max_price]);
        }

        return parent::applyFilters($query, $request);
    }

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

    public function publicShow(\App\Models\Car $car)
    {
        return $this->successResponse($this->singleMessage, $this->transform($car->load($this->with)));
    }

}
