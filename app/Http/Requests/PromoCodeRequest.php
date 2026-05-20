<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromoCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $promoCodeId = $this->route('id') ?? $this->route('promo_code') ?? null;

        return [
            'code' => [
                'required',
                'string',
                'max:60',
                Rule::unique('promo_codes', 'code')->ignore($promoCodeId),
            ],
            'title' => ['nullable', 'string', 'max:150'],
            'discount_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in([0, 1])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->code)),
            'minimum_amount' => $this->minimum_amount ?? 0,
            'status' => (int) ($this->status ?? 1),
        ]);
    }
}
