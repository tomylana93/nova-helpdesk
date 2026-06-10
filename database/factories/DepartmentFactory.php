<?php

namespace Database\Factories;

use App\Enums\GeneralStatus;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'code' => Str::upper(fake()->unique()->lexify('DEPT-????')),
            'name' => fake()->words(2, true).' Department',
            'status' => GeneralStatus::Active,
        ];
    }
}
