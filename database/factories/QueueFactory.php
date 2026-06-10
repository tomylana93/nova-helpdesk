<?php

namespace Database\Factories;

use App\Enums\GeneralStatus;
use App\Models\Queue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Queue>
 */
class QueueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucwords($this->faker->unique()->words(2, true)).' Queue',
            'description' => $this->faker->sentence(),
            'status' => GeneralStatus::Active,
        ];
    }
}
