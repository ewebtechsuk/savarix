<?php

namespace App\Jobs;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class TriggerMarketingCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Property $property)
    {
    }

    public function handle(): void
    {
        $data = $this->property->toArray();
        $providers = config('services.marketing.providers', []);

        foreach ($providers as $provider) {
            if (! empty($provider['endpoint'])) {
                Http::withHeaders([
                    'X-API-KEY' => $provider['api_key'] ?? null,
                ])->post($provider['endpoint'], $data);
            }
        }
    }
}
