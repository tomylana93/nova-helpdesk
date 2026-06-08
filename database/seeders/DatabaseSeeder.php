<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LogicException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        throw_if(app()->isProduction(), LogicException::class, 'DatabaseSeeder cannot be run in the production environment.');
    }
}
