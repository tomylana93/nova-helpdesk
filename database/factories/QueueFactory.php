<?php

namespace Database\Factories;

use App\Enums\GeneralStatus;
use App\Models\Queue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'name' => Str::title(fake()->unique()->words(2, true)).' Queue',
            'description' => fake()->sentence(),
            'status' => GeneralStatus::Active,
        ];
    }
}
