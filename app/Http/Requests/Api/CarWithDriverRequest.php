<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class CarWithDriverRequest extends BaseDataRequest
{
    public function fillableFields(): array
    {
        return ['slug', 'display_en', 'display_ar', 'meta_title_en', 'meta_description_en', 'meta_title_ar', 'meta_description_ar', 'card_image', 'card_header_en', 'card_text_en', 'card_header_ar', 'card_text_ar', 'header_en', 'header_ar', 'cars', 'content_en', 'content_ar'];
    }

    public function rules(?Model $model = null): array
    {
        $id = $model?->getKey();

        return [
            'slug' => ['nullable','string','max:191'],
            'display_en' => ['nullable','string','max:191'],
            'display_ar' => ['nullable','string','max:191'],
            'meta_title_en' => ['nullable','string','max:191'],
            'meta_description_en' => ['nullable','string'],
            'meta_title_ar' => ['nullable','string','max:191'],
            'meta_description_ar' => ['nullable','string'],
            'card_image' => ['nullable','string','max:191'],
            'card_header_en' => ['nullable','string','max:191'],
            'card_text_en' => ['nullable','string'],
            'card_header_ar' => ['nullable','string','max:191'],
            'card_text_ar' => ['nullable','string'],
            'header_en' => ['nullable','string','max:191'],
            'header_ar' => ['nullable','string','max:191'],
            'cars' => ['nullable','string'],
            'content_en' => ['nullable','string'],
            'content_ar' => ['nullable','string'],
            'slug' => ['required','string', Rule::unique('car_with_drivers','slug')->ignore($id)],
        ];
    }
}
