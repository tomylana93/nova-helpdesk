<?php

namespace Database\Seeders;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->active()->count(3)->create();
        User::factory()->disabled()->count(2)->create();

        User::factory()->active()->create([
            'name' => 'Admin',
            'email' => 'admin@nova.dev',
        ]);
    }
}
