<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    /**
     * Generate a standardized error response.
     */
    protected function errorResponse(string $message, int $statusCode): JsonResponse
    {
        return response()->json([
            'error' => $message,
        ], $statusCode);
    }

    protected function dataResponse($data, int $statusCode): JsonResponse
    {
        return response()->json([
            'data' => $data,
        ], $statusCode);
    }
}
