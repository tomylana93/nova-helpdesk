<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

test('exceptions under the api path render as json even without a json accept header', function (): void {
    Route::get('/api/exception-rendering-test', static function (): void {
        abort(404);
    })->name('api.exception-rendering-test');
    app(Router::class)->getRoutes()->refreshNameLookups();

    // No Accept: application/json header — rendering as JSON must be driven by the
    // api/* path rule (shouldRenderJsonWhen), not by content negotiation.
    $response = $this->get(route('api.exception-rendering-test'));

    $response->assertNotFound();

    expect($response->headers->get('content-type'))->toContain('application/json');
    $response->assertJsonStructure(['message']);
});

test('non-api web exceptions still render as html', function (): void {
    Route::get('/web-exception-rendering-test', static function (): void {
        abort(404);
    })->name('web.exception-rendering-test');
    app(Router::class)->getRoutes()->refreshNameLookups();

    $response = $this->get(route('web.exception-rendering-test'));

    $response->assertNotFound();

    expect($response->headers->get('content-type'))->toContain('text/html');
});
