<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Http\Resources\LeadResource;
use App\Services\LeadScoringService;
use Illuminate\Http\Request;

class LeadApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::query();
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        $leads = $query->paginate(20);
        return LeadResource::collection($leads);
    }

    public function store(Request $request, LeadScoringService $scoring)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'status' => 'required|string',
            'contact_id' => 'required|exists:contacts,id',
            'property_id' => 'nullable|exists:properties,id',
            'notes' => 'nullable|string',
        ]);
        $lead = Lead::create($validated);
        $lead->score = $scoring->score($lead);
        $lead->save();
        return new LeadResource($lead);
    }

    public function show(Lead $lead)
    {
        return new LeadResource($lead);
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'type' => 'sometimes|required|string',
            'status' => 'sometimes|required|string',
            'contact_id' => 'sometimes|required|exists:contacts,id',
            'property_id' => 'nullable|exists:properties,id',
            'notes' => 'nullable|string',
        ]);
        $lead->update($validated);
        return new LeadResource($lead);
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return response()->json(['message' => 'Deleted'], 204);
    }
}
