<?php

use App\Models\TemporaryUpload;
use App\Models\User;

test('temporary upload factory creates a valid persisted record', function (): void {
    $temporaryUpload = TemporaryUpload::factory()->create();

    expect($temporaryUpload->exists)->toBeTrue()
        ->and($temporaryUpload->getKey())->toBeString()
        ->and($temporaryUpload->disk)->not->toBeEmpty()
        ->and($temporaryUpload->path)->not->toBeEmpty()
        ->and($temporaryUpload->original_name)->not->toBeEmpty()
        ->and($temporaryUpload->size)->toBeGreaterThan(0);

    $this->assertDatabaseHas('temporary_uploads', ['id' => $temporaryUpload->id]);
});

test('temporary upload factory associates an owning user', function (): void {
    $temporaryUpload = TemporaryUpload::factory()->create();

    expect(User::query()->whereKey($temporaryUpload->user_id)->exists())->toBeTrue();
});

test('temporary upload factory accepts state overrides', function (): void {
    $user = User::factory()->create();

    $temporaryUpload = TemporaryUpload::factory()->create([
        'user_id' => $user->id,
        'original_name' => 'contract.pdf',
        'mime_type' => 'application/pdf',
    ]);

    expect($temporaryUpload->user_id)->toBe($user->id)
        ->and($temporaryUpload->original_name)->toBe('contract.pdf')
        ->and($temporaryUpload->mime_type)->toBe('application/pdf');
});
