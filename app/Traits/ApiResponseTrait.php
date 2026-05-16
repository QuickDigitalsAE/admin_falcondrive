<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponseTrait
{
    protected function successResponse(string $message = 'Request completed successfully', mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data ?? new \stdClass(),
        ], $status);
    }

    protected function errorResponse(string $message = 'Something went wrong', mixed $data = null, int $status = 422): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data ?? new \stdClass(),
        ], $status);
    }

    protected function paginatedData(LengthAwarePaginator $paginator, mixed $items): array
    {
        return [
            'list' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }
}
