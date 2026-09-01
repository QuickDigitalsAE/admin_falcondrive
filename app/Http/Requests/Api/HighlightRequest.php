<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;

class HighlightRequest extends BaseDataRequest
{
    public function fillableFields(): array
    {
        return ['title_en', 'title_ar', 'image', 'sorting', 'url'];
    }

    public function rules(?Model $model = null): array
    {
        return [
            'title_en' => ['required','string','max:191'],
            'title_ar' => ['required','string','max:191'],
            'image' => ['nullable','string','max:191'],
            'sorting' => ['nullable', 'integer', 'min:0'],
            'url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
