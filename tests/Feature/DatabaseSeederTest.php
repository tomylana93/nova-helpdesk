<?php

use Database\Seeders\DatabaseSeeder;

test('database seeder can not run in production', function (): void {
    $this->app->detectEnvironment(fn (): string => 'production');

    expect(app()->isProduction())->toBeTrue();

    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('DatabaseSeeder cannot be run in the production environment.');

    app(DatabaseSeeder::class)->run();
});
