<?php

use Illuminate\Support\Facades\File;

test('it exports locale files to the configured frontend path', function (): void {
    $outputPath = storage_path('framework/testing/lang-export');

    File::deleteDirectory($outputPath);

    $this->artisan('lang:export', [
        '--locale' => ['en', 'id'],
        '--path' => $outputPath,
    ])->assertSuccessful();

    $exportedFile = "{$outputPath}/en.json";
    $indonesianExportedFile = "{$outputPath}/id.json";

    expect($exportedFile)->toBeFile()
        ->and(json_decode(File::get($exportedFile), true, flags: JSON_THROW_ON_ERROR))
        ->toHaveKey('auth.failed')
        ->toHaveKey('validation.required')
        ->and($indonesianExportedFile)->toBeFile()
        ->and(json_decode(File::get($indonesianExportedFile), true, flags: JSON_THROW_ON_ERROR))
        ->toHaveKey('admin.settings.locale.indonesian')
        ->toHaveKey('validation.required');

    File::deleteDirectory($outputPath);
});
