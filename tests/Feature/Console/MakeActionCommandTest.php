<?php

use Illuminate\Support\Facades\File;

afterEach(function () {
    File::delete(app_path('Actions/CreateUserAction.php'));
    File::delete(app_path('Actions/OverwriteAction.php'));
    File::delete(app_path('Actions/Generated/Users/CreateUserAction.php'));
    File::deleteDirectory(app_path('Actions/Generated'));
});

test('it generates action class using defaults', function () {
    $this->artisan('make:action', ['name' => 'CreateUser'])
        ->assertSuccessful();

    $path = app_path('Actions/CreateUserAction.php');

    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);

    expect($content)
        ->toContain('namespace App\Actions;')
        ->toContain('class CreateUserAction')
        ->toContain('public function handle(): void');
});

test('it normalizes action suffix in generated class name', function () {
    $this->artisan('make:action', ['name' => 'CreateUserAction'])
        ->assertSuccessful();

    $path = app_path('Actions/CreateUserAction.php');
    $content = File::get($path);

    expect($content)
        ->toContain('class CreateUserAction')
        ->not->toContain('class CreateUserActionAction');
});

test('it supports nested namespace from action name', function () {
    $this->artisan('make:action', ['name' => 'Generated/Users/CreateUser'])
        ->assertSuccessful();

    $path = app_path('Actions/Generated/Users/CreateUserAction.php');

    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);

    expect($content)
        ->toContain('namespace App\Actions\Generated\Users;')
        ->toContain('class CreateUserAction');
});

test('it requires force option to overwrite existing action class', function () {
    $path = app_path('Actions/OverwriteAction.php');
    File::put($path, '<?php

namespace App\Actions;

class OverwriteAction {}
');

    $this->artisan('make:action', ['name' => 'Overwrite'])
        ->expectsOutputToContain('already exists')
        ->assertExitCode(0);

    expect(File::get($path))->toContain('class OverwriteAction {}');

    $this->artisan('make:action', ['name' => 'Overwrite', '--force' => true])
        ->assertSuccessful();

    $content = File::get($path);

    expect($content)
        ->toContain('class OverwriteAction')
        ->toContain('public function handle(): void');
});

test('it registers make action command in artisan list', function () {
    $this->artisan('list')
        ->expectsOutputToContain('make:action')
        ->assertSuccessful();
});
