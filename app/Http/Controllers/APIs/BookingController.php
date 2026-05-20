<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\BookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookingController extends BaseApiController
{
    protected string $modelClass = Booking::class;
    protected string $resourceClass = BookingResource::class;
    protected string $storeRequestClass = BookingRequest::class;
    protected string $updateRequestClass = BookingRequest::class;
    protected array $searchable = ['name', 'number', 'email', 'coupon_code', 'paid_id', 'send_booking_id'];
    protected array $with = [];
    protected string $publicMessage = 'Booking list fetched successfully';
    protected string $singleMessage = 'Booking fetched successfully';
    protected string $storeMessage = 'Booking created successfully';
    protected string $updateMessage = 'Booking updated successfully';
    protected string $deleteMessage = 'Booking deleted successfully';

    public function storePublic(Request $request)
    {
        try {
            $request->merge($this->normalizeWebsitePayload($request));

            $formRequest = new BookingRequest();
            $request->validate($formRequest->rules());
            $data = $formRequest->sanitize($request);
            $record = Booking::create($data);

            return $this->successResponse($this->storeMessage, BookingResource::make($record)->resolve(), 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', ['errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            return $this->errorResponse($e->getMessage(), ['exception' => class_basename($e)], 500);
        }
    }

    private function normalizeWebsitePayload(Request $request): array
    {
        $payload = $request->all();
        $contact = (array) $request->input('contact', []);
        $pricing = (array) $request->input('pricing', []);

        [$startDate, $startTime] = $this->splitDateTime($request->input('start_date', $request->input('from_datetime')));
        [$endDate, $endTime] = $this->splitDateTime($request->input('end_date', $request->input('to_datetime')));

        $phoneCountry = $this->stringOrNull($request->input('phone_country', data_get($contact, 'phone_country')));
        $phone = $this->stringOrNull($request->input('phone', data_get($contact, 'phone')));
        $normalizedNumber = $this->normalizePhone($phoneCountry, $phone);

        $customerType = strtolower((string) $request->input('customer_type', $request->input('resident_tourist', '')));
        $residentTourist = in_array($customerType, ['resident', 'tourist'], true) ? $customerType : null;

        $paymentMode = strtolower((string) $request->input('payment_mode', $request->input('payment_flow', '')));
        $paymentFlow = match ($paymentMode) {
            'pay_now', 'now' => 'now',
            'pay_later', 'later' => 'later',
            default => null,
        };

        $deliveryZone = $this->nullIfPlaceholder($request->input('fd_delivery_zone'));
        $returnZone = $this->nullIfPlaceholder($request->input('fd_return_zone'));

        $deliveryLocation = $this->nullIfPlaceholder($request->input('delivery_location', $deliveryZone));
        $returnLocation = $this->nullIfPlaceholder($request->input('return_location', $returnZone));

        $fallbackPickup = $this->nullIfPlaceholder($request->input('fd_pickup'));
        $fallbackReturnPickup = $this->nullIfPlaceholder($request->input('fd_return_pickup'));

        return [
            'name' => $this->stringOrNull($request->input('name', $request->input('full_name', data_get($contact, 'full_name')))),
            'number' => $request->input('number', $normalizedNumber),
            'email' => $this->stringOrNull($request->input('email', data_get($contact, 'email'))),
            'start_date' => $request->input('start_date', $startDate),
            'end_date' => $request->input('end_date', $endDate),
            'start_time' => $request->input('start_time', $startTime),
            'end_time' => $request->input('end_time', $endTime),
            'rental_type' => $request->input('rental_type', $request->input('rental_period')),
            'rental_price' => $this->decimalOrNull($request->input('rental_price', data_get($pricing, 'rental_price'))),
            'rental_duration' => $this->stringOrNull($request->input('rental_duration', data_get($pricing, 'rental_duration'))),
            'resident_tourist' => $request->input('resident_tourist', $residentTourist),
            'full_insurance' => $this->booleanOrNull($request->input('full_insurance')),
            'full_insurance_price' => $this->decimalOrNull($request->input('full_insurance_price', data_get($pricing, 'full_insurance_price'))),
            'additional_driver' => $this->booleanOrNull($request->input('additional_driver')),
            'additional_driver_charges' => $this->decimalOrNull($request->input('additional_driver_charges', data_get($pricing, 'additional_driver_charges'))),
            'baby_seat' => $this->booleanOrNull($request->input('baby_seat')),
            'baby_seat_price' => $this->decimalOrNull($request->input('baby_seat_price', data_get($pricing, 'baby_seat_price'))),
            'deposit_waiver' => $request->input('deposit_waiver', $this->mapDepositWaiver($request->input('deposit_waiver_enabled'))),
            'deposit_waiver_price' => $this->decimalOrNull($request->input('deposit_waiver_price', data_get($pricing, 'deposit_waiver_price'))),
            'delivery_location' => $deliveryLocation,
            'delivery_location_price' => $this->decimalOrNull($request->input('delivery_location_price', data_get($pricing, 'delivery_location_price'))),
            'different_city_dropoff_fee' => $this->decimalOrNull($request->input('different_city_dropoff_fee', data_get($pricing, 'different_city_dropoff_fee'))),
            'self_pickup_location' => $deliveryLocation === null
                ? $this->nullIfPlaceholder($request->input('self_pickup_location', $fallbackPickup ?? $request->input('pickup_branch')))
                : $this->nullIfPlaceholder($request->input('self_pickup_location', $request->input('pickup_branch'))),
            'self_pickup_address' => $deliveryLocation === null
                ? $this->nullIfPlaceholder($request->input('self_pickup_address', $fallbackPickup ?? $request->input('pickup_branch')))
                : $this->nullIfPlaceholder($request->input('self_pickup_address', $request->input('pickup_branch'))),
            'return_location' => $returnLocation,
            'return_location_price' => $this->decimalOrNull($request->input('return_location_price', data_get($pricing, 'return_location_price'))),
            'self_return_location' => $returnLocation === null
                ? $this->nullIfPlaceholder($request->input('self_return_location', $fallbackReturnPickup ?? $request->input('dropoff_branch')))
                : $this->nullIfPlaceholder($request->input('self_return_location', $request->input('dropoff_branch'))),
            'self_return_address' => $returnLocation === null
                ? $this->nullIfPlaceholder($request->input('self_return_address', $fallbackReturnPickup ?? $request->input('dropoff_branch')))
                : $this->nullIfPlaceholder($request->input('self_return_address', $request->input('dropoff_branch'))),
            'coupon_code' => $this->stringOrNull($request->input('coupon_code', $request->input('promo_code'))),
            'coupon_amount' => $this->decimalOrNull($request->input('coupon_amount', data_get($pricing, 'coupon_amount'))),
            'pay_now_discount' => $this->decimalOrNull($request->input('pay_now_discount', data_get($pricing, 'pay_now_discount'))),
            'discount_percentage' => $this->decimalOrNull($request->input('discount_percentage', data_get($pricing, 'discount_percentage'))),
            'subtotal' => $this->decimalOrNull($request->input('subtotal', data_get($pricing, 'subtotal'))),
            'vat_percentage' => $this->decimalOrNull($request->input('vat_percentage', data_get($pricing, 'vat_percentage'))),
            'vat_amount' => $this->decimalOrNull($request->input('vat_amount', data_get($pricing, 'vat_amount'))),
            'total_amount' => $this->decimalOrNull($request->input('total_amount', data_get($pricing, 'total_amount'))),
            'payment_flow' => $request->input('payment_flow', $paymentFlow),
            'pay_now_20%_to_Reserve' => $this->decimalOrNull($request->input('pay_now_20%_to_Reserve', data_get($pricing, 'pay_now_20%_to_Reserve'))),
            'pay_at_pickup_80%' => $this->decimalOrNull($request->input('pay_at_pickup_80%', data_get($pricing, 'pay_at_pickup_80%'))),
            'paid_id' => $this->stringOrNull($request->input('paid_id')),
            'paid_date' => $request->input('paid_date'),
            'paid_status' => $this->stringOrNull($request->input('paid_status')),
            'paid_via' => $this->stringOrNull($request->input('paid_via')),
            'contact_preference' => $request->has('contact_preference')
                ? $request->input('contact_preference')
                : ($request->boolean('no_whatsapp', data_get($contact, 'no_whatsapp', false)) ? 'phone' : 'whatsapp'),
            'term_22_years' => $request->input('term_22_years', $request->input('confirm_age', data_get($contact, 'confirm_age'))),
            'term_6_month_experience' => $request->input('term_6_month_experience', $request->input('confirm_driving', data_get($contact, 'confirm_driving'))),
            'send_booking_id' => $this->stringOrNull($request->input('send_booking_id')),
            'notes' => $request->input('notes', $this->buildNotes($request, $payload)),
            'speed_response' => $request->input('speed_response'),
        ];
    }

    private function splitDateTime(mixed $value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [null, null];
        }

        try {
            $dateTime = Carbon::parse($value);

            return [$dateTime->format('Y-m-d'), $dateTime->format('H:i:s')];
        } catch (Throwable) {
            return [null, null];
        }
    }

    private function normalizePhone(?string $phoneCountry, ?string $phone): ?string
    {
        $segments = array_filter([
            $this->stringOrNull($phoneCountry),
            $this->stringOrNull($phone),
        ], static fn (?string $value) => $value !== null);

        if (empty($segments)) {
            return null;
        }

        return trim(implode(' ', $segments));
    }

    private function mapDepositWaiver(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 'Waiver' : 'Deposit';
    }

    private function nullIfPlaceholder(mixed $value): ?string
    {
        $text = $this->stringOrNull($value);

        if ($text === null) {
            return null;
        }

        if (str_starts_with(strtolower($text), 'select ')) {
            return null;
        }

        return $text;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function decimalOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', (string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function booleanOrNull(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    private function buildNotes(Request $request, array $payload): ?string
    {
        $notes = [
            'source' => $this->stringOrNull($request->input('source')),
            'car_id' => $request->input('car_id'),
            'car_slug' => $this->stringOrNull($request->input('car_slug')),
            'car_name' => $this->stringOrNull($request->input('car_name')),
            'pickup_branch' => $this->stringOrNull($request->input('pickup_branch')),
            'dropoff_branch' => $this->stringOrNull($request->input('dropoff_branch')),
            'form_fields' => $request->input('form_fields'),
            'raw_payload' => $payload,
        ];

        $notes = array_filter($notes, static fn ($value) => !in_array($value, [null, '', []], true));

        if (empty($notes)) {
            return null;
        }

        return json_encode($notes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
