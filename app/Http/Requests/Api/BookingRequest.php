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
            'rental_price' => $this->sanitizeDecimal($this->input('rental_price')),
            'rental_duration' => $this->sanitizeText($this->input('rental_duration')),
            'resident_tourist' => $this->sanitizeText($this->input('resident_tourist')),
            'full_insurance' => $this->sanitizeBool($this->input('full_insurance')),
            'full_insurance_price' => $this->sanitizeDecimal($this->input('full_insurance_price')),
            'additional_driver' => $this->sanitizeBool($this->input('additional_driver')),
            'additional_driver_charges' => $this->sanitizeDecimal($this->input('additional_driver_charges')),
            'baby_seat' => $this->sanitizeBool($this->input('baby_seat')),
            'baby_seat_price' => $this->sanitizeDecimal($this->input('baby_seat_price')),
            'deposit_waiver' => $this->sanitizeText($this->input('deposit_waiver')),
            'deposit_waiver_price' => $this->sanitizeDecimal($this->input('deposit_waiver_price')),
            'delivery_location' => $this->sanitizeLongText($this->input('delivery_location')),
            'delivery_location_price' => $this->sanitizeDecimal($this->input('delivery_location_price')),
            'different_city_dropoff_fee' => $this->sanitizeDecimal($this->input('different_city_dropoff_fee')),
            'self_pickup_location' => $this->sanitizeLongText($this->input('self_pickup_location')),
            'self_pickup_address' => $this->sanitizeText($this->input('self_pickup_address')),
            'return_location' => $this->sanitizeLongText($this->input('return_location')),
            'return_location_price' => $this->sanitizeDecimal($this->input('return_location_price')),
            'self_return_location' => $this->sanitizeLongText($this->input('self_return_location')),
            'self_return_address' => $this->sanitizeText($this->input('self_return_address')),
            'coupon_code' => $this->sanitizeText($this->input('coupon_code')),
            'coupon_amount' => $this->sanitizeDecimal($this->input('coupon_amount')),
            'pay_now_discount' => $this->sanitizeDecimal($this->input('pay_now_discount')),
            'discount_percentage' => $this->sanitizeDecimal($this->input('discount_percentage')),
            'subtotal' => $this->sanitizeDecimal($this->input('subtotal')),
            'vat_percentage' => $this->sanitizeDecimal($this->input('vat_percentage')),
            'vat_amount' => $this->sanitizeDecimal($this->input('vat_amount')),
            'total_amount' => $this->sanitizeDecimal($this->input('total_amount')),
            'payment_flow' => $this->sanitizeText($this->input('payment_flow')),
            'pay_now_20%_to_Reserve' => $this->sanitizeDecimal($this->input('pay_now_20%_to_Reserve')),
            'pay_at_pickup_80%' => $this->sanitizeDecimal($this->input('pay_at_pickup_80%')),
            'paid_id' => $this->sanitizeText($this->input('paid_id')),
            'paid_date' => $this->sanitizeDateTime($this->input('paid_date')),
            'paid_status' => $this->sanitizeText($this->input('paid_status')),
            'paid_via' => $this->sanitizeText($this->input('paid_via')),
            'contact_preference' => $this->sanitizeText($this->input('contact_preference')),
            'term_22_years' => $this->sanitizeBool($this->input('term_22_years')),
            'term_6_month_experience' => $this->sanitizeBool($this->input('term_6_month_experience')),
            'send_booking_id' => $this->sanitizeText($this->input('send_booking_id')),
            'notes' => $this->sanitizeLongText($this->input('notes')),
            'speed_response' => $this->sanitizeLongText($this->input('speed_response')),
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
            'rental_price',
            'rental_duration',
            'resident_tourist',
            'full_insurance',
            'full_insurance_price',
            'additional_driver',
            'additional_driver_charges',
            'baby_seat',
            'baby_seat_price',
            'deposit_waiver',
            'deposit_waiver_price',
            'delivery_location',
            'delivery_location_price',
            'different_city_dropoff_fee',
            'self_pickup_location',
            'self_pickup_address',
            'return_location',
            'return_location_price',
            'self_return_location',
            'self_return_address',
            'coupon_code',
            'coupon_amount',
            'pay_now_discount',
            'discount_percentage',
            'subtotal',
            'vat_percentage',
            'vat_amount',
            'total_amount',
            'payment_flow',
            'pay_now_20%_to_Reserve',
            'pay_at_pickup_80%',
            'paid_id',
            'paid_date',
            'paid_status',
            'paid_via',
            'contact_preference',
            'term_22_years',
            'term_6_month_experience',
            'send_booking_id',
            'notes',
            'speed_response',
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
            'rental_price' => ['nullable', 'numeric', 'min:0'],
            'rental_duration' => ['nullable', 'string', 'max:191'],
            'resident_tourist' => ['nullable', 'in:resident,tourist'],
            'full_insurance' => ['nullable', 'boolean'],
            'full_insurance_price' => ['nullable', 'numeric', 'min:0'],
            'additional_driver' => ['nullable', 'boolean'],
            'additional_driver_charges' => ['nullable', 'numeric', 'min:0'],
            'baby_seat' => ['nullable', 'boolean'],
            'baby_seat_price' => ['nullable', 'numeric', 'min:0'],
            'deposit_waiver' => ['nullable', 'in:Deposit,Waiver'],
            'deposit_waiver_price' => ['nullable', 'numeric', 'min:0'],
            'delivery_location' => ['nullable', 'string', 'max:5000'],
            'delivery_location_price' => ['nullable', 'numeric', 'min:0'],
            'different_city_dropoff_fee' => ['nullable', 'numeric', 'min:0'],
            'self_pickup_location' => ['nullable', 'string', 'max:5000'],
            'self_pickup_address' => ['nullable', 'string', 'max:191'],
            'return_location' => ['nullable', 'string', 'max:5000'],
            'return_location_price' => ['nullable', 'numeric', 'min:0'],
            'self_return_location' => ['nullable', 'string', 'max:5000'],
            'self_return_address' => ['nullable', 'string', 'max:191'],
            'coupon_code' => ['nullable', 'string', 'max:191'],
            'coupon_amount' => ['nullable', 'numeric', 'min:0'],
            'pay_now_discount' => ['nullable', 'numeric', 'min:0'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'vat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vat_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_flow' => ['nullable', 'in:now,later'],
            'pay_now_20%_to_Reserve' => ['nullable', 'numeric', 'min:0'],
            'pay_at_pickup_80%' => ['nullable', 'numeric', 'min:0'],
            'paid_id' => ['nullable', 'string', 'max:191'],
            'paid_date' => ['nullable', 'date'],
            'paid_status' => ['nullable', 'string', 'max:191'],
            'paid_via' => ['nullable', 'string', 'max:191'],
            'contact_preference' => ['nullable', 'in:whatsapp,phone'],
            'term_22_years' => ['nullable', 'boolean'],
            'term_6_month_experience' => ['nullable', 'boolean'],
            'send_booking_id' => ['nullable', 'string', 'max:191'],
            'notes' => ['nullable', 'string', 'max:200000'],
            'speed_response' => ['nullable', 'string', 'max:200000'],
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

    private function sanitizeLongText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

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
