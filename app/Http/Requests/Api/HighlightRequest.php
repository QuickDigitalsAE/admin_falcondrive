<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class HighlightRequest extends BaseDataRequest
{
    public function fillableFields(): array
    {
        return ['title_en', 'title_ar', 'image', 'sorting'];
    }

    public function rules(?Model $model = null): array
    {
        $id = $model?->getKey();

        return [
            'title_en' => ['required','string','max:191'],
            'title_ar' => ['required','string','max:191'],
            'image' => ['nullable','string','max:191'],
            'sorting' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
