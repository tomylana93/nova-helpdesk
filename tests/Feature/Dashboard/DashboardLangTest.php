<?php

test('dashboard translations exist for both locales', function (): void {
    expect(__('dashboard.metric.created', [], 'en'))->toBe('Created')
        ->and(__('dashboard.metric.created', [], 'id'))->toBe('Dibuat')
        ->and(__('dashboard.live.unassigned', [], 'en'))->toBe('Unassigned')
        ->and(__('dashboard.compliance.tooltip', ['within' => 46, 'total' => 50], 'en'))
        ->toContain('46')
        ->toContain('50');
});
