<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class TestimonialRequest extends BaseDataRequest
{
    public function fillableFields(): array
    {
        return ['name_en', 'name_ar', 'description_en', 'description_ar', 'image'];
    }

    public function rules(?Model $model = null): array
    {
        $id = $model?->getKey();

        return [
            'name_en' => ['required','string','max:191'],
            'name_ar' => ['required','string','max:191'],
            'description_en' => ['nullable','string'],
            'description_ar' => ['nullable','string'],
            'image' => ['nullable','string','max:191'],
        ];
    }
}
