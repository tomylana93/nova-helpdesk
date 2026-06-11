<?php

use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $this->versionFile = base_path('version.json');
    $this->originalContent = File::exists($this->versionFile) ? File::get($this->versionFile) : null;
    File::put($this->versionFile, json_encode(['version' => '0.8.0']));
});

afterEach(function (): void {
    if ($this->originalContent !== null) {
        File::put($this->versionFile, $this->originalContent);
    } else {
        File::delete($this->versionFile);
    }
});

test('it bumps patch version correctly', function (): void {
    $this->artisan('app:bump-version patch')
        ->expectsOutput('Version bumped from v0.8.0 to v0.8.1')
        ->assertSuccessful();

    $data = json_decode(File::get($this->versionFile), true);
    expect($data['version'])->toBe('0.8.1');
});

test('it bumps minor version correctly', function (): void {
    $this->artisan('app:bump-version minor')
        ->expectsOutput('Version bumped from v0.8.0 to v0.9.0')
        ->assertSuccessful();

    $data = json_decode(File::get($this->versionFile), true);
    expect($data['version'])->toBe('0.9.0');
});

test('it bumps major version correctly', function (): void {
    $this->artisan('app:bump-version major')
        ->expectsOutput('Version bumped from v0.8.0 to v1.0.0')
        ->assertSuccessful();

    $data = json_decode(File::get($this->versionFile), true);
    expect($data['version'])->toBe('1.0.0');
});

test('it bumps to specific version correctly', function (): void {
    $this->artisan('app:bump-version 1.2.3')
        ->expectsOutput('Version bumped from v0.8.0 to v1.2.3')
        ->assertSuccessful();

    $data = json_decode(File::get($this->versionFile), true);
    expect($data['version'])->toBe('1.2.3');
});

test('it handles auto mode bump correctly', function (): void {
    // This will execute git log on the actual repository
    // Since the repo has commits, it should detect a bump type (major, minor, or patch)
    // and successfully bump the version.
    $this->artisan('app:bump-version auto')
        ->assertSuccessful();
});

test('it rejects invalid bump type', function (): void {
    $this->artisan('app:bump-version invalid')
        ->expectsOutput("Invalid bump type or version format. Use 'major', 'minor', 'patch', 'auto', or a specific version like '0.8.0'.")
        ->assertFailed();
});
