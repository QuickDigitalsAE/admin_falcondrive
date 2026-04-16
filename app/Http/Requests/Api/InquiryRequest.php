<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class InquiryRequest extends BaseDataRequest
{
    public function fillableFields(): array
    {
        return ['name', 'number', 'email', 'message', 'promo_code', 'car_name'];
    }

    public function rules(?Model $model = null): array
    {
        $id = $model?->getKey();

        return [
            'name' => ['required','string','max:191'],
            'number' => ['required','string','max:191'],
            'message' => ['nullable','string'],
            'promo_code' => ['nullable','string','max:191'],
            'car_name' => ['nullable','string','max:191'],
            'email' => ['nullable','email','max:191']
        ];
    }
}
