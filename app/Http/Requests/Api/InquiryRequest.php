<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;

class InquiryRequest extends BaseDataRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->sanitizeText($this->input('name')),
            'number' => $this->sanitizePhone($this->input('number')),
            'email' => $this->sanitizeEmail($this->input('email')),
            'message' => $this->sanitizeText($this->input('message')),
            'promo_code' => $this->sanitizeText($this->input('promo_code')),
            'car_name' => $this->sanitizeText($this->input('car_name')),
            'from_date' => $this->sanitizeDate($this->input('from_date')),
            'to_date' => $this->sanitizeDate($this->input('to_date')),
            'website' => $this->sanitizeText($this->input('website')),
            'g-recaptcha-response' => $this->sanitizeText($this->input('g-recaptcha-response')),
        ]);
    }

    public function fillableFields(): array
    {
        return ['name', 'number', 'email', 'message', 'promo_code', 'car_name', 'from_date', 'to_date'];
    }

    public function rules(?Model $model = null): array
    {
        $id = $model?->getKey();

        return [
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\pM\s.\'-]+$/u'],
            'number' => ['required', 'string', 'min:7', 'max:25', 'regex:/^[0-9+\-\s()]+$/'],
            'message' => ['nullable', 'string', 'max:2000'],
            'promo_code' => ['nullable', 'string', 'max:100'],
            'car_name' => ['nullable', 'string', 'max:100'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'email' => ['nullable', 'email:rfc,dns', 'max:191'],
            'website' => ['nullable', 'prohibited'],
            'g-recaptcha-response' => ['nullable', 'string', 'max:5000'],
        ];
    }

    private function sanitizeText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(strip_tags((string) $value));
        $value = preg_replace('/\s+/u', ' ', $value);

        return $value === '' ? null : $value;
    }

    private function sanitizePhone(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/[^0-9+\-\s()]/', '', (string) $value);
        $value = preg_replace('/\s+/', ' ', trim((string) $value));

        return $value === '' ? null : $value;
    }

    private function sanitizeEmail(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = strtolower(trim((string) $value));

        return $value === '' ? null : $value;
    }

    private function sanitizeDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
