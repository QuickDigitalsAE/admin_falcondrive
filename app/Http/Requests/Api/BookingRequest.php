<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;

class BookingRequest extends BaseDataRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->sanitizeText($this->input('name')),
            'number' => $this->sanitizePhone($this->input('number')),
            'email' => $this->sanitizeEmail($this->input('email')),
            'start_date' => $this->sanitizeDate($this->input('start_date')),
            'end_date' => $this->sanitizeDate($this->input('end_date')),
            'start_time' => $this->sanitizeTime($this->input('start_time')),
            'end_time' => $this->sanitizeTime($this->input('end_time')),
            'rental_type' => $this->sanitizeText($this->input('rental_type')),
            'resident_tourist' => $this->sanitizeText($this->input('resident_tourist')),
            'full_insurance' => $this->sanitizeBool($this->input('full_insurance')),
            'additional_driver' => $this->sanitizeBool($this->input('additional_driver')),
            'baby_seat' => $this->sanitizeBool($this->input('baby_seat')),
            'deposit_waiver' => $this->sanitizeText($this->input('deposit_waiver')),
            'delivery_address' => $this->sanitizeText($this->input('delivery_address')),
            'delivery_area' => $this->sanitizeText($this->input('delivery_area')),
            'pickup_address' => $this->sanitizeText($this->input('pickup_address')),
            'pickup_area' => $this->sanitizeText($this->input('pickup_area')),
            'delivery_price' => $this->sanitizeDecimal($this->input('delivery_price')),
            'pickup_price' => $this->sanitizeDecimal($this->input('pickup_price')),
            'coupon_code' => $this->sanitizeText($this->input('coupon_code')),
            'discount_percentage' => $this->sanitizeDecimal($this->input('discount_percentage')),
            'payment_flow' => $this->sanitizeText($this->input('payment_flow')),
            'paid_id' => $this->sanitizeText($this->input('paid_id')),
            'paid_date' => $this->sanitizeDateTime($this->input('paid_date')),
            'paid_status' => $this->sanitizeText($this->input('paid_status')),
            'paid_via' => $this->sanitizeText($this->input('paid_via')),
            'contact_preference' => $this->sanitizeText($this->input('contact_preference')),
            'term_22_years' => $this->sanitizeBool($this->input('term_22_years')),
            'term_6_month_experience' => $this->sanitizeBool($this->input('term_6_month_experience')),
            'description' => $this->sanitizeText($this->input('description')),
            'notes' => $this->sanitizeText($this->input('notes')),
            'request_body' => $this->sanitizeText($this->input('request_body')),
            'website' => $this->sanitizeText($this->input('website')),
            'g-recaptcha-response' => $this->sanitizeText($this->input('g-recaptcha-response')),
        ]);
    }

    public function fillableFields(): array
    {
        return [
            'name',
            'number',
            'email',
            'start_date',
            'end_date',
            'start_time',
            'end_time',
            'rental_type',
            'resident_tourist',
            'full_insurance',
            'additional_driver',
            'baby_seat',
            'deposit_waiver',
            'delivery_address',
            'delivery_area',
            'pickup_address',
            'pickup_area',
            'delivery_price',
            'pickup_price',
            'coupon_code',
            'discount_percentage',
            'payment_flow',
            'paid_id',
            'paid_date',
            'paid_status',
            'paid_via',
            'contact_preference',
            'term_22_years',
            'term_6_month_experience',
            'description',
            'notes',
            'request_body',
        ];
    }

    public function rules(?Model $model = null): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:191', 'regex:/^[\pL\pM\s.\'-]+$/u'],
            'number' => ['required', 'string', 'min:7', 'max:25', 'regex:/^[0-9+\-\s()]+$/'],
            'email' => ['nullable', 'email:rfc,dns', 'max:191'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'regex:/^([01]\\d|2[0-3]):[0-5]\\d(:[0-5]\\d)?$/'],
            'end_time' => ['nullable', 'regex:/^([01]\\d|2[0-3]):[0-5]\\d(:[0-5]\\d)?$/'],
            'rental_type' => ['nullable', 'in:daily,weekly,monthly'],
            'resident_tourist' => ['nullable', 'in:resident,tourist'],
            'full_insurance' => ['nullable', 'boolean'],
            'additional_driver' => ['nullable', 'boolean'],
            'baby_seat' => ['nullable', 'boolean'],
            'deposit_waiver' => ['nullable', 'in:Deposit,Waiver'],
            'delivery_address' => ['nullable', 'string', 'max:2000'],
            'delivery_area' => ['nullable', 'string', 'max:191'],
            'pickup_address' => ['nullable', 'string', 'max:2000'],
            'pickup_area' => ['nullable', 'string', 'max:191'],
            'delivery_price' => ['nullable', 'numeric', 'min:0'],
            'pickup_price' => ['nullable', 'numeric', 'min:0'],
            'coupon_code' => ['nullable', 'string', 'max:191'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_flow' => ['nullable', 'in:now,later'],
            'paid_id' => ['nullable', 'string', 'max:191'],
            'paid_date' => ['nullable', 'date'],
            'paid_status' => ['nullable', 'string', 'max:191'],
            'paid_via' => ['nullable', 'string', 'max:191'],
            'contact_preference' => ['nullable', 'in:whatsapp,phone'],
            'term_22_years' => ['nullable', 'boolean'],
            'term_6_month_experience' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'request_body' => ['nullable', 'string', 'max:200000'],
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

    private function sanitizeTime(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function sanitizeDateTime(mixed $value): ?string
    {
        return $this->sanitizeDate($value);
    }

    private function sanitizeDecimal(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = preg_replace('/[^0-9.\-]/', '', $value);

        return $value === '' ? null : $value;
    }

    private function sanitizeBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
