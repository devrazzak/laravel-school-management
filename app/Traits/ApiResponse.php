<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

trait ApiResponse
{
    protected function successResponse(mixed $data, string $message = '', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    protected function errorResponse(string $message, int $statusCode = 400, mixed $error = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $error,
        ], $statusCode);
    }

    protected function paginatedResponse(AnonymousResourceCollection $collection, string $message = 'Success'): JsonResponse
    {
        $resource = $collection->response()->getData(true);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resource['data'],
            'pagination' => [
                'current_page' => $resource['meta']['current_page'],
                'last_page' => $resource['meta']['last_page'],
                'per_page' => $resource['meta']['per_page'],
                'total' => $resource['meta']['total'],
                'from' => $resource['meta']['from'],
                'to' => $resource['meta']['to'],
            ],
        ]);
    }
}
