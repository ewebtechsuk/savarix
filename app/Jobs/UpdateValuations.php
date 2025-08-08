<?php

namespace App\Jobs;

use App\Models\Property;
use App\Services\ValuationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateValuations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ValuationService $valuationService): void
    {
        Property::chunk(100, function ($properties) use ($valuationService) {
            foreach ($properties as $property) {
                $property->valuation_estimate = $valuationService->estimate($property);
                $property->save();
            }
        });
    }
}
