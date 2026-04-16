<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class FaqRequest extends BaseDataRequest
{
    public function fillableFields(): array
    {
        return ['question_en', 'question_ar', 'answer_en', 'answer_ar'];
    }

    public function rules(?Model $model = null): array
    {
        $id = $model?->getKey();

        return [
            'question_en' => ['required','string','max:191'],
            'question_ar' => ['required','string','max:191'],
            'answer_en' => ['required','string'],
            'answer_ar' => ['required','string'],
        ];
    }
}
