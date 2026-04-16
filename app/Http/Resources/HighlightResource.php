<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
class HighlightResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title_en' => $this->title_en,
            'title_ar' => $this->title_ar,
            'image' => $this->image,
            'image_url' => $this->imageUrl($this->image),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
