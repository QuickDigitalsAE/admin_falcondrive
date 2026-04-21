<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
class BlogResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title_en' => $this->title_en,
            'title_ar' => $this->title_ar,
            'blog_description_en' => $this->blog_description_en,
            'blog_description_ar' => $this->blog_description_ar,
            'slug' => $this->slug,
            'seo_title_en' => $this->seo_title_en,
            'seo_title_ar' => $this->seo_title_ar,
            'seo_brief_en' => $this->seo_brief_en,
            'seo_brief_ar' => $this->seo_brief_ar,
            'image' => $this->image,
            'image_url' => $this->imageUrl($this->image),
            'blog_schedule' => $this->blog_schedule,
            'post_datetime' => optional($this->publishedAt())?->toISOString(),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
