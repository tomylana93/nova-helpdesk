<?php

namespace Database\Factories;

use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TemporaryUpload>
 */
class TemporaryUploadFactory extends Factory
{
    /**
     * @var class-string<TemporaryUpload>
     */
    protected $model = TemporaryUpload::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->word().'.png';

        return [
            'id' => (string) Str::ulid(),
            'user_id' => User::factory(),
            'disk' => 'public',
            'path' => 'tmp/uploads/'.Str::ulid().'/'.$name,
            'original_name' => $name,
            'mime_type' => 'image/png',
            'size' => fake()->numberBetween(1024, 1048576),
        ];
    }
}
