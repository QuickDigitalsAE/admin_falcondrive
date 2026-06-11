<?php

namespace App\Http\Resources;

use App\Http\Controllers\APIs\CarController;
use App\Models\DeliveryReturnLocation;
use Illuminate\Http\Request;

class CarResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'price_daily' => $this->price_daily,
            'price_weekly' => $this->price_weekly,
            'price_monthly' => $this->price_monthly,
            'full_insurance_amount' => $this->full_insurance_amount,
            'additional_driver_amount' => $this->additional_driver_amount,
            'baby_seat_amount' => $this->baby_seat_amount,
            'deposit_amount' => $this->deposit_amount,
            'waiver_amount' => $this->waiver_amount,
            'different_city_dropoff_fee' => $this->different_city_dropoff_fee,
            'cdw_daily' => $this->cdw_daily,
            'cdw_weekly' => $this->cdw_weekly,
            'cdw_monthly' => $this->cdw_monthly,
            'vehicle_group_id' => $this->vehicle_group_id,
            'tariff_group_id' => $this->tariff_group_id,
            'main_image' => $this->main_image,
            'main_image_url' => $this->imageUrl($this->main_image),
            'images' => $this->decodeImages($this->images),
            'images_urls' => array_map(fn ($img) => $this->imageUrl($img), $this->decodeImages($this->images)),
            'model' => $this->model,
            'featured' => (bool) $this->featured,
            'featured_sorting' => $this->featured_sorting,
            'engine' => $this->engine,
            'seats' => $this->seats,
            'doors' => $this->doors,
            'deposit' => $this->deposit,
            'luggage' => $this->luggage,
            'cruise_control' => (bool) $this->cruise_control,
            'bluetooth' => (bool) $this->bluetooth,
            'automatic' => (bool) $this->automatic,
            'parking_sensor' => (bool) $this->parking_sensor,
            'navigation' => (bool) $this->navigation,
            'carplay' => (bool) $this->carplay,
            'camera' => (bool) $this->camera,
            'slug' => $this->slug,
            'seo_title_en' => $this->seo_title_en,
            'seo_title_ar' => $this->seo_title_ar,
            'seo_brief_en' => $this->seo_brief_en,
            'seo_brief_ar' => $this->seo_brief_ar,
            'brand_id' => $this->brand_id,

            'brand_name_en' => $this->whenLoaded('brand', fn () => $this->brand?->name_en),
            'brand_name_ar' => $this->whenLoaded('brand', fn () => $this->brand?->name_ar),
            'brand_logo_url' => $this->whenLoaded('brand', fn () => $this->brand ? BrandResource::make($this->brand)->resolve()['logo_url'] ?? null : null),

            'primary_category_name_en' => $this->whenLoaded('categories', fn () => optional($this->categories->first())->name_en),
            'primary_category_name_ar' => $this->whenLoaded('categories', fn () => optional($this->categories->first())->name_ar),

            'category_names_en' => $this->whenLoaded('categories', function () {
                return $this->categories->pluck('name_en')->filter()->implode(', ');
            }),

            'category_names_ar' => $this->whenLoaded('categories', function () {
                return $this->categories->pluck('name_ar')->filter()->implode(', ');
            }),

            'stock' => $this->stock,
            'sorting' => $this->sorting,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];

        if ($this->shouldIncludeRelations($request)) {
            $data['brand'] = BrandResource::make($this->whenLoaded('brand'));
            $data['categories'] = CategoryResource::collection($this->whenLoaded('categories'));
            $data['locations'] = LocationResource::collection($this->whenLoaded('locations'));
            $data['driver_pages'] = CarWithDriverResource::collection($this->whenLoaded('driverPages'));
            $data['delivery_locations'] = DeliveryReturnLocation::query()
                ->where('type', 'Delivery location')
                ->orderBy('city')
                ->get(['id', 'city', 'price', 'type'])
                ->map(fn (DeliveryReturnLocation $location) => [
                    'id' => $location->id,
                    'city' => $location->city,
                    'price' => $location->price,
                    'type' => $location->type,
                ])
                ->all();
            $data['return_locations'] = DeliveryReturnLocation::query()
                ->where('type', 'Return location')
                ->orderBy('city')
                ->get(['id', 'city', 'price', 'type'])
                ->map(fn (DeliveryReturnLocation $location) => [
                    'id' => $location->id,
                    'city' => $location->city,
                    'price' => $location->price,
                    'type' => $location->type,
                ])
                ->all();
        }

        return $data;
    }

    private function shouldIncludeRelations(Request $request): bool
    {
        $route = $request->route();

        if (!$route) {
            return false;
        }

        return $route->getController() instanceof CarController
            && $route->getActionMethod() === 'publicShow';
    }
}
