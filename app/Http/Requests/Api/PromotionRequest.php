<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PromotionRequest extends BaseDataRequest
{
    public function fillableFields(): array
    {
        return ['name_en', 'name_ar', 'description_en', 'description_ar', 'seo_title_en', 'seo_title_ar', 'seo_brief_en', 'seo_brief_ar', 'slug', 'image', 'top_offer'];
    }

    public function rules(?Model $model = null): array
    {
        $id = $model?->getKey();

        return [
            'name_en' => ['required','string','max:191'],
            'name_ar' => ['required','string','max:191'],
            'description_en' => ['nullable','string'],
            'description_ar' => ['nullable','string'],
            'seo_title_en' => ['nullable','string','max:191'],
            'seo_title_ar' => ['nullable','string','max:191'],
            'seo_brief_en' => ['nullable','string'],
            'seo_brief_ar' => ['nullable','string'],
            'slug' => ['nullable','string','max:191'],
            'image' => ['nullable','string','max:191'],
            'top_offer' => ['nullable','integer'],
            'slug' => ['required','string', Rule::unique('promotions','slug')->ignore($id)],
        ];
    }
}
