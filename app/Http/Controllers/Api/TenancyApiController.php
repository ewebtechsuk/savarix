<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavarixTenancy;
use App\Http\Resources\TenancyResource;
use Illuminate\Http\Request;

class TenancyApiController extends Controller
{
    public function index(Request $request)
    {
        $query = SavarixTenancy::query();
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        $tenancies = $query->paginate(20);
        return TenancyResource::collection($tenancies);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'contact_id' => 'required|exists:contacts,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'rent' => 'required|numeric',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);
        $tenancy = SavarixTenancy::create($validated);
        return new TenancyResource($tenancy);
    }

    public function show(SavarixTenancy $tenancy)
    {
        return new TenancyResource($tenancy);
    }

    public function update(Request $request, SavarixTenancy $tenancy)
    {
        $validated = $request->validate([
            'property_id' => 'sometimes|required|exists:properties,id',
            'contact_id' => 'sometimes|required|exists:contacts,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date',
            'rent' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|string',
            'notes' => 'nullable|string',
        ]);
        $tenancy->update($validated);
        return new TenancyResource($tenancy);
    }

    public function destroy(SavarixTenancy $tenancy)
    {
        $tenancy->delete();
        return response()->json(['message' => 'Deleted'], 204);
    }
}
