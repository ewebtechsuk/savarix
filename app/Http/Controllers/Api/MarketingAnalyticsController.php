<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConversionTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\CarbonImmutable;

class MarketingAnalyticsController extends Controller
{
    public function store(Request $request, ConversionTrackingService $tracking): JsonResponse
    {
        $data = $request->validate([
            'event' => 'required|string|max:120',
            'metadata' => 'nullable|array',
            'session' => 'nullable|string|max:64',
            'occurred_at' => 'nullable|date',
        ]);

        $tracking->record(
            $data['event'],
            $data['metadata'] ?? [],
            $data['session'] ?? null,
            isset($data['occurred_at']) ? CarbonImmutable::parse($data['occurred_at']) : null
        );

        return response()->json(['status' => 'ok'], 201);
    }
}
