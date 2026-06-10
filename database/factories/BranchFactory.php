<?php

namespace Database\Factories;

use App\Enums\GeneralStatus;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(fake()->unique()->lexify('BR-????')),
            'name' => fake()->company().' Branch',
            'status' => GeneralStatus::Active,
        ];
    }
}
