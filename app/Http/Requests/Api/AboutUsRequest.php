<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class AboutUsRequest extends BaseDataRequest
{
    public function fillableFields(): array
    {
        return ['first_section_en', 'first_section_ar', 'mission_en', 'mission_ar', 'vision_en', 'vision_ar', 'seo_title_en', 'seo_title_ar', 'seo_brief_en', 'seo_brief_ar'];
    }

    public function rules(?Model $model = null): array
    {
        $id = $model?->getKey();

        return [
            'first_section_en' => ['required','string'],
            'first_section_ar' => ['required','string'],
            'mission_en' => ['required','string'],
            'mission_ar' => ['required','string'],
            'vision_en' => ['required','string'],
            'vision_ar' => ['required','string'],
            'seo_title_en' => ['nullable','string','max:191'],
            'seo_title_ar' => ['nullable','string','max:191'],
            'seo_brief_en' => ['nullable','string'],
            'seo_brief_ar' => ['nullable','string'],
        ];
    }
}
