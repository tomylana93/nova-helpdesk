<?php

use App\Actions\Dashboard\Support\Delta;

test('computes positive delta', function (): void {
    expect(Delta::compute(40, 33))->toBe([
        'deltaPercent' => 21,
        'direction' => 'up',
    ]);
});

test('computes negative delta', function (): void {
    expect(Delta::compute(8, 10))->toBe([
        'deltaPercent' => -20,
        'direction' => 'down',
    ]);
});

test('flat when equal', function (): void {
    expect(Delta::compute(10, 10))->toBe([
        'deltaPercent' => 0,
        'direction' => 'flat',
    ]);
});

test('both zero is flat zero', function (): void {
    expect(Delta::compute(0, 0))->toBe([
        'deltaPercent' => 0,
        'direction' => 'flat',
    ]);
});

test('growth from zero baseline yields null percent and up direction', function (): void {
    expect(Delta::compute(5, 0))->toBe([
        'deltaPercent' => null,
        'direction' => 'up',
    ]);
});
