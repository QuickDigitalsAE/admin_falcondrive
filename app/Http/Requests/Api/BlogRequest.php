<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class BlogRequest extends BaseDataRequest
{
    public function fillableFields(): array
    {
        return ['title_en', 'title_ar', 'blog_description_en', 'blog_description_ar', 'slug', 'seo_title_en', 'seo_title_ar', 'seo_brief_en', 'seo_brief_ar', 'image', 'blog_schedule'];
    }

    public function rules(?Model $model = null): array
    {
        $id = $model?->getKey();

        return [
            'title_en' => ['required','string','max:191'],
            'title_ar' => ['required','string','max:191'],
            'blog_description_en' => ['nullable','string'],
            'blog_description_ar' => ['nullable','string'],
            'slug' => ['nullable','string','max:191'],
            'seo_title_en' => ['nullable','string','max:191'],
            'seo_title_ar' => ['nullable','string','max:191'],
            'seo_brief_en' => ['nullable','string'],
            'seo_brief_ar' => ['nullable','string'],
            'image' => ['nullable','string','max:191'],
            'blog_schedule' => ['nullable','date'],
            'slug' => ['required','string', Rule::unique('blogs','slug')->ignore($id)],
        ];
    }
}
