<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
class BrandResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'seo_title_en' => $this->seo_title_en,
            'seo_title_ar' => $this->seo_title_ar,
            'seo_brief_en' => $this->seo_brief_en,
            'seo_brief_ar' => $this->seo_brief_ar,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'logo_url' => $this->imageUrl($this->logo),
            'sorting' => $this->sorting,
            'cars_count' => $this->whenCounted('cars'),
            'cars' => CarResource::collection($this->whenLoaded('cars')),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
