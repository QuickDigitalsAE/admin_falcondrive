<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
class AboutUsResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_section_en' => $this->first_section_en,
            'first_section_ar' => $this->first_section_ar,
            'mission_en' => $this->mission_en,
            'mission_ar' => $this->mission_ar,
            'vision_en' => $this->vision_en,
            'vision_ar' => $this->vision_ar,
            'seo_title_en' => $this->seo_title_en,
            'seo_title_ar' => $this->seo_title_ar,
            'seo_brief_en' => $this->seo_brief_en,
            'seo_brief_ar' => $this->seo_brief_ar,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
