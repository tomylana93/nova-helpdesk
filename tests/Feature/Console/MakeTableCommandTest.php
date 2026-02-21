<?php

use Illuminate\Support\Facades\File;

afterEach(function () {
    File::delete(app_path('Tables/FixtureUserTable.php'));
    File::delete(app_path('Tables/AccountTable.php'));
    File::delete(app_path('Tables/OverwriteTable.php'));
    File::delete(app_path('Tables/Admin/FixtureUserTable.php'));
    File::deleteDirectory(app_path('Tables/Admin'));
});

test('it generates table class using convention defaults', function () {
    $this->artisan('make:table', ['name' => 'FixtureUser'])
        ->assertSuccessful();

    $path = app_path('Tables/FixtureUserTable.php');

    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);

    expect($content)
        ->toContain('namespace App\Tables;')
        ->toContain('class FixtureUserTable extends AbstractTable')
        ->toContain('use App\Http\Resources\FixtureUserResource;')
        ->toContain('use App\Models\FixtureUser;')
        ->toContain('return FixtureUserResource::class;')
        ->toContain('return FixtureUser::query();');
});

test('it normalizes table suffix in generated class name', function () {
    $this->artisan('make:table', ['name' => 'FixtureUserTable'])
        ->assertSuccessful();

    $path = app_path('Tables/FixtureUserTable.php');
    $content = File::get($path);

    expect($content)
        ->toContain('class FixtureUserTable extends AbstractTable')
        ->not->toContain('class FixtureUserTableTable');
});

test('it resolves model and resource from options', function () {
    $this->artisan('make:table', [
        'name' => 'Account',
        '--model' => 'App\Models\User',
        '--resource' => 'App\Http\Resources\UserResource',
    ])->assertSuccessful();

    $path = app_path('Tables/AccountTable.php');
    $content = File::get($path);

    expect($content)
        ->toContain('class AccountTable extends AbstractTable')
        ->toContain('use App\Http\Resources\UserResource;')
        ->toContain('use App\Models\User;')
        ->toContain('return UserResource::class;')
        ->toContain('return User::query();');
});

test('it supports nested namespace from table name', function () {
    $this->artisan('make:table', ['name' => 'Admin/FixtureUser'])
        ->assertSuccessful();

    $path = app_path('Tables/Admin/FixtureUserTable.php');

    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);

    expect($content)
        ->toContain('namespace App\Tables\Admin;')
        ->toContain('class FixtureUserTable extends AbstractTable');
});

test('it requires force option to overwrite existing table class', function () {
    $path = app_path('Tables/OverwriteTable.php');
    File::put($path, '<?php

namespace App\Tables;

class OverwriteTable {}
');

    $this->artisan('make:table', ['name' => 'Overwrite'])
        ->expectsOutputToContain('already exists')
        ->assertExitCode(0);

    expect(File::get($path))->toContain('class OverwriteTable {}');

    $this->artisan('make:table', ['name' => 'Overwrite', '--force' => true])
        ->assertSuccessful();

    $content = File::get($path);

    expect($content)
        ->toContain('class OverwriteTable extends AbstractTable')
        ->toContain('return OverwriteResource::class;')
        ->toContain('return Overwrite::query();');
});
