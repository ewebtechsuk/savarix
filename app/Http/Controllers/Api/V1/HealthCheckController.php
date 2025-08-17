<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}