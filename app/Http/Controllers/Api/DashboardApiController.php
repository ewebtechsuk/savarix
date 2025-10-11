<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UnifiedAgencyDashboardService;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends Controller
{
    public function __construct(private UnifiedAgencyDashboardService $dashboardService)
    {
        $this->middleware('auth:sanctum');
    }

    public function unified(): JsonResponse
    {
        return response()->json($this->dashboardService->getSummary());
    }
}

