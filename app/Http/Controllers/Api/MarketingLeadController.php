<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MarketingLeadRequest;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\DemoRequest;
use App\Services\ConversionTrackingService;
use App\Services\LeadScoringService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Carbon\CarbonImmutable;

class MarketingLeadController extends Controller
{
    public function store(
        MarketingLeadRequest $request,
        LeadScoringService $leadScoring,
        ConversionTrackingService $tracking
    ): JsonResponse {
        $payload = $request->validated();

        return DB::transaction(function () use ($payload, $leadScoring, $tracking): JsonResponse {
            $contact = Contact::updateOrCreate(
                ['email' => $payload['email']],
                [
                    'type' => 'prospect',
                    'name' => $payload['name'],
                    'phone' => $payload['phone'] ?? null,
                    'address' => $payload['company'],
                    'notes' => $payload['notes'] ?? null,
                ]
            );

            $lead = Lead::create([
                'type' => 'demo',
                'status' => 'new',
                'contact_id' => $contact->id,
                'notes' => $payload['notes'] ?? null,
            ]);

            $lead->score = $leadScoring->score($lead);
            $lead->save();

            $scheduledAt = CarbonImmutable::parse($payload['preferred_demo_at'], $payload['timezone'])
                ->setTimezone('UTC');

            $demo = DemoRequest::create([
                'lead_id' => $lead->id,
                'contact_id' => $contact->id,
                'scheduled_at' => $scheduledAt,
                'timezone' => $payload['timezone'],
                'status' => 'scheduled',
                'metadata' => [
                    'company_size' => $payload['company_size'] ?? null,
                    'role' => $payload['role'] ?? null,
                    'source' => $payload['source'] ?? null,
                    'utm' => $payload['utm'] ?? [],
                ],
            ]);

            $tracking->record(
                'marketing.lead_captured',
                [
                    'lead_id' => $lead->id,
                    'contact_id' => $contact->id,
                    'demo_id' => $demo->id,
                    'scheduled_at' => $demo->scheduled_at?->toIso8601String(),
                    'timezone' => $demo->timezone,
                    'source' => $payload['source'] ?? null,
                    'utm' => $payload['utm'] ?? [],
                ],
                $payload['tracking_session'] ?? null
            );

            return response()->json([
                'message' => 'Demo scheduled successfully.',
                'lead_id' => $lead->id,
                'demo_id' => $demo->id,
                'scheduled_at' => $demo->scheduled_at?->toIso8601String(),
                'timezone' => $demo->timezone,
            ], 201);
        });
    }
}
