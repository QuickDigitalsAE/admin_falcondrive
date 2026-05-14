<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\BookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\Request;
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
}

