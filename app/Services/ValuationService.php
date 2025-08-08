<?php

namespace App\Services;

use App\Models\Property;

class ValuationService
{
    /**
     * Estimate the valuation of a property.
     * This would normally call an external API or ML model.
     */
    public function estimate(Property $property): float
    {
        // Simple placeholder algorithm
        return round($property->price * 1.05, 2);
    }
}
