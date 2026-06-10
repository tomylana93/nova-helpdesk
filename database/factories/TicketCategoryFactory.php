<?php

namespace Database\Factories;

use App\Enums\GeneralStatus;
use App\Models\TicketCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketCategory>
 */
class TicketCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => ucwords($this->faker->unique()->words(2, true)),
            'description' => $this->faker->sentence(),
            'status' => GeneralStatus::Active,
        ];
    }

    /**
     * Indicate that the category is a subcategory.
     */
    public function subcategory(TicketCategory $parent): self
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id' => $parent->id,
        ]);
    }
}
