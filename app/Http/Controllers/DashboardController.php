<?php

namespace App\Http\Controllers;

use App\Core\Application;
use Framework\Http\Request as LegacyRequest;
use Framework\Http\Response as LegacyResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController
{
    public function index(HttpRequest $request): HttpResponse
    {
        return response()->view('dashboard.index', [
            'user' => $request->user(),
            'stats' => $this->dashboardStats(),
        ]);
    }

    public function legacyIndex(LegacyRequest $request, array $context): LegacyResponse
    {
        $app = $context['app'] ?? null;

        if ($app instanceof Application) {
            $user = Auth::user();
            $content = $app->view('dashboard.index', [
                'user' => $user,
                'stats' => $this->dashboardStats(),
            ]);

            return LegacyResponse::view($content);
        }

        throw new \RuntimeException('Unable to resolve application context for the dashboard.');
    }

    private function dashboardStats(): array
    {
        return [
            'property_count' => 0,
            'tenancy_count' => 0,
            'lead_count' => 0,
            'payment_count' => 0,
        ];
    }
}
