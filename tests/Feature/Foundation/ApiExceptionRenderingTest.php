<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

it('keeps api exception rendering explicitly configured', function () {
    expect(file_get_contents(base_path('bootstrap/app.php')))
        ->toContain('shouldRenderJsonWhen')
        ->toContain("\$request->is('api/*')");
});

it('renders api route exceptions as json by default', function () {
    Route::get('/api/foundation-drift-test', static function (): void {
        abort(418, 'Foundation drift test');
    })->name('foundation-drift-test');

    app(Router::class)->getRoutes()->refreshNameLookups();

    $this->get(route('foundation-drift-test'))
        ->assertStatus(418)
        ->assertHeader('content-type', 'application/json')
        ->assertJsonPath('message', 'Foundation drift test');
});
