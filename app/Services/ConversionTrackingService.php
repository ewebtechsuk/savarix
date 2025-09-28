<?php

namespace App\Services;

use App\Models\MarketingEvent;
use Carbon\CarbonInterface;

class ConversionTrackingService
{
    public function record(
        string $eventName,
        array $metadata = [],
        ?string $sessionId = null,
        ?CarbonInterface $occurredAt = null
    ): MarketingEvent {
        return MarketingEvent::create([
            'session_id' => $sessionId,
            'event_name' => $eventName,
            'metadata' => $metadata,
            'occurred_at' => $occurredAt ?? now(),
        ]);
    }
}
