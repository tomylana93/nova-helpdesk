<?php

use Illuminate\Support\Facades\File;

afterEach(function (): void {
    File::deleteDirectory(app_path('Actions/Testing'));
    File::deleteDirectory(app_path('Tables/Testing'));
});

test('it creates an action class in the actions namespace', function (): void {
    $path = app_path('Actions/Testing/CreateWidget.php');

    $this->artisan('make:action', [
        'name' => 'Testing/CreateWidget',
    ])->assertSuccessful();

    expect($path)->toBeFile()
        ->and(File::get($path))
        ->toContain('namespace App\Actions\Testing;')
        ->toContain('class CreateWidget')
        ->toContain('public function handle(array $data): void');
});

test('it normalizes action names with a leading actions segment', function (): void {
    $path = app_path('Actions/Testing/ArchiveWidget.php');

    $this->artisan('make:action', [
        'name' => 'Actions/Testing/ArchiveWidget',
    ])->assertSuccessful();

    expect($path)->toBeFile()
        ->and(File::get($path))
        ->toContain('namespace App\Actions\Testing;')
        ->toContain('class ArchiveWidget');
});

test('it creates a table class in the tables namespace', function (): void {
    $path = app_path('Tables/Testing/WidgetTable.php');

    $this->artisan('make:table', [
        'name' => 'Testing/WidgetTable',
    ])->assertSuccessful();

    expect($path)->toBeFile()
        ->and(File::get($path))
        ->toContain('namespace App\Tables\Testing;')
        ->toContain('class WidgetTable extends AbstractTable')
        ->toContain("throw new LogicException('Implement the query() method.');");
});

test('it normalizes table names with a leading tables segment', function (): void {
    $path = app_path('Tables/Testing/ReportTable.php');

    $this->artisan('make:table', [
        'name' => 'Tables/Testing/ReportTable',
    ])->assertSuccessful();

    expect($path)->toBeFile()
        ->and(File::get($path))
        ->toContain('namespace App\Tables\Testing;')
        ->toContain('class ReportTable extends AbstractTable');
});
