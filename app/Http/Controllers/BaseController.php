<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class BaseController extends Controller 
{

    protected function success(mixed $payload = null, string $message = 'Success', int $status = 200): JsonResponse 
	{
        return response()->json([
            'message' => $message,
            'payload' => $payload,
            'status' => $status,
        ], $status);
    }

    protected function fail(string $message = 'Failed', int $status = 400, mixed $payload = null, bool $global_error = false): JsonResponse 
	{
        return response()->json([
            'message' => $message,
            'payload' => $payload,
            'status' => $status,
			'global_error' => $global_error,
        ], $status);
    }

}
