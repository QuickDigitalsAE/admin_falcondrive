<?php
namespace App\Http\Resources;

use App\Http\Controllers\APIs\CategoryController;
use Illuminate\Http\Request;

class CategoryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $data = [
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
            'image' => $this->image,
            'image_url' => $this->imageUrl($this->image),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];

        if ($this->shouldIncludeCars($request)) {
            $data['cars'] = CarResource::collection($this->whenLoaded('cars'));
        }

        return $data;
    }

    private function shouldIncludeCars(Request $request): bool
    {
        $route = $request->route();

        if (!$route) {
            return false;
        }

        return !($route->getController() instanceof CategoryController
            && $route->getActionMethod() === 'publicIndex');
    }
}
