<?php

namespace Database\Factories;

use App\Models\Inspection;
use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionFactory extends Factory
{
    protected $model = Inspection::class;

    public function definition()
    {
        return [
            'property_id' => \App\Models\Property::factory(),
            'agent_id' => \App\Models\User::factory(),
            'scheduled_at' => $this->faker->dateTimeBetween('-1 week', '+1 week'),
            'status' => $this->faker->randomElement(['pending', 'completed']),
        ];
    }
}
