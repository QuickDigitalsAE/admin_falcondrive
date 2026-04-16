<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
class CarResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'price_daily' => $this->price_daily,
            'price_weekly' => $this->price_weekly,
            'price_monthly' => $this->price_monthly,
            'cdw_daily' => $this->cdw_daily,
            'cdw_weekly' => $this->cdw_weekly,
            'cdw_monthly' => $this->cdw_monthly,
            'main_image' => $this->main_image,
            'main_image_url' => $this->imageUrl($this->main_image),
            'images' => $this->decodeImages($this->images),
            'images_urls' => array_map(fn ($img) => $this->imageUrl($img), $this->decodeImages($this->images)),
            'model' => $this->model,
            'featured' => (bool) $this->featured,
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
            'stock' => $this->stock,
            'sorting' => $this->sorting,
            'brand' => BrandResource::make($this->whenLoaded('brand')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'locations' => LocationResource::collection($this->whenLoaded('locations')),
            'driver_pages' => CarWithDriverResource::collection($this->whenLoaded('driverPages')),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
