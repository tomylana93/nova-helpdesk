<?php

namespace Database\Factories;

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_tag' => 'AST-'.fake()->unique()->numerify('#####'),
            'name' => fake()->words(2, true),
            'category' => fake()->randomElement(AssetCategory::cases()),
            'status' => fake()->randomElement(AssetStatus::cases()),
            'branch_id' => null,
            'user_id' => null,
        ];
    }
}
