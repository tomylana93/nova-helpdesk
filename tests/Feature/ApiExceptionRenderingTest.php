<?php

test('exceptions under the api path render as json even without a json accept header', function (): void {
    // No Accept: application/json header — rendering as JSON must be driven by the
    // api/* path rule (shouldRenderJsonWhen), not by content negotiation.
    $response = $this->get('/api/does-not-exist');

    $response->assertNotFound();
    expect($response->headers->get('content-type'))->toContain('application/json');
    $response->assertJsonStructure(['message']);
});

test('non-api web exceptions still render as html', function (): void {
    $response = $this->get('/definitely-not-a-real-web-route');

    $response->assertNotFound();
    expect($response->headers->get('content-type'))->toContain('text/html');
});
