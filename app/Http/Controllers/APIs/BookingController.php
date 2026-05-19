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
    protected array $searchable = ['name', 'number', 'email', 'coupon_code', 'paid_id'];
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

            if (empty($data['request_body'])) {
                $data['request_body'] = json_encode([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'payload' => $request->all(),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

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
        $options = (array) $request->input('options', []);

        [$startDate, $startTime] = $this->splitDateTime(
            $request->input('start_date', $request->input('from_datetime'))
        );
        [$endDate, $endTime] = $this->splitDateTime(
            $request->input('end_date', $request->input('to_datetime'))
        );

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

        return [
            'name' => $this->stringOrNull($request->input('name', $request->input('full_name', data_get($contact, 'full_name')))),
            'number' => $request->input('number', $normalizedNumber),
            'email' => $this->stringOrNull($request->input('email', data_get($contact, 'email'))),
            'start_date' => $request->input('start_date', $startDate),
            'end_date' => $request->input('end_date', $endDate),
            'start_time' => $request->input('start_time', $startTime),
            'end_time' => $request->input('end_time', $endTime),
            'rental_type' => $request->input('rental_type', $request->input('rental_period')),
            'resident_tourist' => $request->input('resident_tourist', $residentTourist),
            'full_insurance' => $request->input('full_insurance', data_get($options, 'full_insurance')),
            'additional_driver' => $request->input('additional_driver', data_get($options, 'add_driver')),
            'baby_seat' => $request->input('baby_seat', data_get($options, 'baby_seat')),
            'deposit_waiver' => $request->input('deposit_waiver', $this->mapDepositWaiver(data_get($options, 'deposit_waiver'))),
            'delivery_address' => $this->nullIfPlaceholder($request->input('delivery_address', $request->input('pickup_branch'))),
            'delivery_area' => $this->nullIfPlaceholder($request->input('delivery_area', $request->input('delivery_zone'))),
            'pickup_address' => $this->nullIfPlaceholder($request->input('pickup_address', $request->input('dropoff_branch'))),
            'pickup_area' => $this->nullIfPlaceholder($request->input('pickup_area', $request->input('return_zone'))),
            'coupon_code' => $request->input('coupon_code', $request->input('promo_code')),
            'payment_flow' => $request->input('payment_flow', $paymentFlow),
            'contact_preference' => $request->has('contact_preference')
                ? $request->input('contact_preference')
                : ($request->boolean('no_whatsapp', data_get($contact, 'no_whatsapp', false)) ? 'phone' : 'whatsapp'),
            'term_22_years' => $request->input('term_22_years', $request->input('confirm_age', data_get($contact, 'confirm_age'))),
            'term_6_month_experience' => $request->input('term_6_month_experience', $request->input('confirm_driving', data_get($contact, 'confirm_driving'))),
            'description' => $request->input('description', $this->buildDescription($request)),
            'notes' => $request->input('notes', $this->buildNotes($request)),
            'request_body' => $request->input(
                'request_body',
                json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ),
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

        $normalized = strtolower($text);

        if (str_starts_with($normalized, 'select ')) {
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

    private function buildDescription(Request $request): ?string
    {
        $parts = array_filter([
            $this->stringOrNull($request->input('car_name')),
            $this->stringOrNull($request->input('car_slug')),
            $this->stringOrNull($request->input('source')),
        ]);

        if (empty($parts)) {
            return null;
        }

        return implode(' | ', $parts);
    }

    private function buildNotes(Request $request): ?string
    {
        $notes = [
            'car_id' => $request->input('car_id'),
            'pickup_branch' => $this->stringOrNull($request->input('pickup_branch')),
            'dropoff_branch' => $this->stringOrNull($request->input('dropoff_branch')),
            'pricing' => $request->input('pricing'),
            'payment' => $request->input('payment'),
            'form_fields' => $request->input('form_fields'),
        ];

        $notes = array_filter($notes, static fn ($value) => !in_array($value, [null, '', []], true));

        if (empty($notes)) {
            return null;
        }

        return json_encode($notes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
