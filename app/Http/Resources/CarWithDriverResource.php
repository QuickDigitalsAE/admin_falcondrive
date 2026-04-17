<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
class CarWithDriverResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'display_en' => $this->display_en,
            'display_ar' => $this->display_ar,
            'meta_title_en' => $this->meta_title_en,
            'meta_description_en' => $this->meta_description_en,
            'meta_title_ar' => $this->meta_title_ar,
            'meta_description_ar' => $this->meta_description_ar,
            'card_image' => $this->card_image,
            'card_image_url' => $this->imageUrl($this->card_image),
            'card_header_en' => $this->card_header_en,
            'card_text_en' => $this->card_text_en,
            'card_header_ar' => $this->card_header_ar,
            'card_text_ar' => $this->card_text_ar,
            'header_en' => $this->header_en,
            'header_ar' => $this->header_ar,
            'cars' => CarResource::collection($this->whenLoaded('carsRelation')),
            'content_en' => $this->content_en,
            'content_ar' => $this->content_ar,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
