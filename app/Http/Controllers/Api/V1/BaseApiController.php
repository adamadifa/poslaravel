<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    /**
     * Return success response.
     */
    public function sendResponse(mixed $result, string $message = 'Success', int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'status' => 'success',
            'message' => $message,
            'data' => $result,
        ];

        return response()->json($response, $code);
    }

    /**
     * Return error response.
     */
    public function sendError(string $error, array|string $errorMessages = [], int $code = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'status' => 'error',
            'message' => $error,
        ];

        if (! empty($errorMessages)) {
            $response['errors'] = is_array($errorMessages) ? $errorMessages : [$errorMessages];
        }

        return response()->json($response, $code);
    }
}
