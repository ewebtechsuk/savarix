<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Property;
use Illuminate\Support\Collection;

class ApplicantMatcher
{
    public function match(Property $property, int $limit = 5): Collection
    {
        $weights = config('matching.weights');

        return Applicant::query()
            ->whereNull('deleted_at')
            ->get()
            ->map(function (Applicant $applicant) use ($property, $weights) {
                $score = 0;

                if ($property->price && $applicant->min_budget && $applicant->max_budget) {
                    if ($property->price >= $applicant->min_budget && $property->price <= $applicant->max_budget) {
                        $score += $weights['budget_match'];
                    } else {
                        $score += $weights['budget_miss'];
                    }
                }

                if ($property->bedrooms && $applicant->preferred_bedrooms) {
                    $score += (1 - min(abs($property->bedrooms - $applicant->preferred_bedrooms) * 0.25, 1)) * $weights['bedrooms'];
                }

                if ($property->city && $applicant->preferred_city) {
                    $score += strcasecmp($property->city, $applicant->preferred_city) === 0
                        ? $weights['location_match']
                        : $weights['location_miss'];
                }

                if ($applicant->marketing_opt_in) {
                    $score += $weights['marketing_opt_in'];
                }

                return [
                    'applicant' => $applicant,
                    'score' => max(0, round($score)),
                ];
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }
}
