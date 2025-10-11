<?php

namespace App\Jobs;

use App\Models\PropertyPortals;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncPropertyToPortal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $propertyId, public string $portalKey)
    {
    }

    public function handle(): void
    {
        if (! in_array($this->portalKey, PropertyPortals::PORTALS, true)) {
            return;
        }

        $record = PropertyPortals::query()->firstOrCreate(['property' => $this->propertyId]);

        $record->fill([$this->portalKey => true]);
        $record->save();
    }
}

