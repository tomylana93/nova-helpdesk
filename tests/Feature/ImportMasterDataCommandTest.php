<?php

use App\Models\Branch;
use App\Models\Department;
use App\Models\Queue;
use App\Models\TicketCategory;

test('it imports master data from CSV files successfully', function (): void {
    // Assert database is empty initially (or at least our custom data isn't there)
    expect(Branch::query()->where('code', 'BR-JKT')->exists())->toBeFalse();

    // Run the artisan command
    $this->artisan('master-data:import')
        ->expectsOutputToContain('Starting master data import...')
        ->expectsOutputToContain('Imported branches: 5')
        ->expectsOutputToContain('Imported departments: 12')
        ->expectsOutputToContain('Imported queues: 1')
        ->expectsOutputToContain('Imported ticket categories: 11')
        ->expectsOutputToContain('Master data import completed successfully!')
        ->assertSuccessful();

    // Assert counts are correct
    expect(Branch::query()->count())->toBe(5)
        ->and(Department::query()->count())->toBe(12)
        ->and(Queue::query()->count())->toBe(1)
        ->and(TicketCategory::query()->count())->toBe(11);

    // Verify JKT branch and a department
    $branch = Branch::query()->where('code', 'BR-JKT')->first();
    expect($branch)->not->toBeNull();
    expect($branch?->name)->toBe('SLOG Jakarta');

    $dept = Department::query()->where('code', 'JKT-DEPT-HR')->first();
    expect($dept)->not->toBeNull();
    expect($dept?->branch_id)->toBe($branch?->id);
    expect($dept?->name)->toBe('Human Resource');

    // Verify parent-child categories
    $parent = TicketCategory::query()->where('name', 'Hardware')->whereNull('parent_id')->first();
    expect($parent)->not->toBeNull();

    $child = TicketCategory::query()->where('name', 'Laptop atau PC')->first();
    expect($child)->not->toBeNull();
    expect($child?->parent_id)->toBe($parent?->id);
});
