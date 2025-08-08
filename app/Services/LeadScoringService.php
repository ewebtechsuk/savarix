<?php

namespace App\Services;

use App\Models\Lead;

class LeadScoringService
{
    /**
     * Produce a simple score for a lead based on its attributes.
     */
    public function score(Lead $lead): int
    {
        $score = 0;
        if ($lead->contact_id) {
            $score += 10;
        }
        if ($lead->property_id) {
            $score += 20;
        }
        if ($lead->notes) {
            $score += 5;
        }
        return $score;
    }
}
