<?php

namespace App\Jobs;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncPropertyToPortals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Property $property)
    {
    }

    public function handle(): void
    {
        $data = $this->property->toArray();

        foreach (['rightmove', 'zoopla'] as $portal) {
            $config = config("services.$portal");
            if (! empty($config['endpoint'])) {
                Http::withHeaders([
                    'X-API-KEY' => $config['api_key'] ?? null,
                    'X-API-SECRET' => $config['api_secret'] ?? null,
                ])->post($config['endpoint'], $data);
            }
        }
    }
}
