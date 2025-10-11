<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PartnerIntegration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerIntegrationController extends Controller
{
    public function index(): JsonResponse
    {
        $integrations = PartnerIntegration::query()
            ->orderBy('name')
            ->get();

        return response()->json($integrations);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validate($request, $this->rules());

        $integration = PartnerIntegration::create($data);

        return response()->json($integration, 201);
    }

    public function show(PartnerIntegration $integration): JsonResponse
    {
        return response()->json($integration);
    }

    public function update(Request $request, PartnerIntegration $integration): JsonResponse
    {
        $data = $this->validate($request, $this->rules($integration->id));

        $integration->update($data);

        return response()->json($integration);
    }

    public function destroy(PartnerIntegration $integration): JsonResponse
    {
        $integration->delete();

        return response()->json(null, 204);
    }

    protected function rules(?int $integrationId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'provider' => ['required', 'string', 'max:190'],
            'type' => ['required', 'string', 'max:190'],
            'credentials' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}

