<?php

namespace Database\Factories;

use App\Models\SlaPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SlaPolicy>
 */
class SlaPolicyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true).' SLA',
            'ticket_type' => $this->faker->optional()->randomElement(['incident', 'service_request']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'first_response_target_minutes' => $this->faker->randomElement([30, 60, 120, 240]),
            'resolution_target_minutes' => $this->faker->randomElement([240, 480, 1440, 2880]),
            'is_active' => true,
        ];
    }
}
