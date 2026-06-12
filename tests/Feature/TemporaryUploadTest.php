<?php

use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('authenticated users can store temporary uploads', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('temporary-uploads.store'), [
            'file' => UploadedFile::fake()->image('icon.png', 64, 64),
        ]);

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'id',
            'name',
            'size',
            'type',
        ]);

    $temporaryUpload = TemporaryUpload::query()->findOrFail($response->json('id'));

    expect($temporaryUpload->user_id)->toBe($user->id);
    Storage::disk('public')->assertExists($temporaryUpload->path);
});

test('authenticated users can delete their temporary uploads', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();

    $temporaryUploadId = $this
        ->actingAs($user)
        ->post(route('temporary-uploads.store'), [
            'file' => UploadedFile::fake()->image('logo.png', 128, 128),
        ])
        ->json('id');

    $temporaryUpload = TemporaryUpload::query()->findOrFail($temporaryUploadId);

    $this->actingAs($user)
        ->delete(route('temporary-uploads.destroy', $temporaryUpload))
        ->assertNoContent();

    expect(TemporaryUpload::query()->find($temporaryUploadId))->toBeNull();
    Storage::disk('public')->assertMissing($temporaryUpload->path);
});

test('temporary uploads reject unsupported mime types', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('temporary-uploads.store'), [
            'file' => UploadedFile::fake()->create(
                'run.exe',
                100,
                'application/x-msdownload',
            ),
        ]);

    $response->assertSessionHasErrors('file');
});

test('temporary uploads reject svg images', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('temporary-uploads.store'), [
            'file' => UploadedFile::fake()->create(
                'logo.svg',
                1,
                'image/svg+xml',
            ),
        ]);

    $response->assertSessionHasErrors('file');
});

test('temporary uploads limit active uploads per user', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();

    foreach (range(1, 20) as $index) {
        TemporaryUpload::query()->create([
            'user_id' => $user->id,
            'disk' => 'public',
            'path' => "temporary-uploads/active-{$index}.png",
            'original_name' => "active-{$index}.png",
            'mime_type' => 'image/png',
            'size' => 5,
        ]);
    }

    $this
        ->actingAs($user)
        ->post(route('temporary-uploads.store'), [
            'file' => UploadedFile::fake()->image('next.png', 64, 64),
        ])
        ->assertSessionHasErrors('file');
});

test('temporary uploads are rate limited', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();

    foreach (range(1, 20) as $index) {
        $this
            ->actingAs($user)
            ->post(route('temporary-uploads.store'), [
                'file' => UploadedFile::fake()->image("upload-{$index}.png", 64, 64),
            ])
            ->assertCreated();
    }

    $this
        ->actingAs($user)
        ->post(route('temporary-uploads.store'), [
            'file' => UploadedFile::fake()->image('blocked.png', 64, 64),
        ])
        ->assertTooManyRequests();
});

test('prune temporary uploads command deletes stale uploads only', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();

    Storage::disk('public')->put('temporary-uploads/stale.png', 'stale');
    Storage::disk('public')->put('temporary-uploads/recent.png', 'recent');

    $staleUpload = TemporaryUpload::query()->create([
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => 'temporary-uploads/stale.png',
        'original_name' => 'stale.png',
        'mime_type' => 'image/png',
        'size' => 5,
    ]);
    $staleUpload->forceFill([
        'created_at' => now()->subHours(30),
        'updated_at' => now()->subHours(30),
    ])->saveQuietly();

    $recentUpload = TemporaryUpload::query()->create([
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => 'temporary-uploads/recent.png',
        'original_name' => 'recent.png',
        'mime_type' => 'image/png',
        'size' => 6,
    ]);
    $recentUpload->forceFill([
        'created_at' => now()->subHours(2),
        'updated_at' => now()->subHours(2),
    ])->saveQuietly();

    $this->artisan('uploads:prune-temporary')->assertSuccessful();

    expect(TemporaryUpload::query()->find($staleUpload->id))->toBeNull()
        ->and(TemporaryUpload::query()->find($recentUpload->id))->not->toBeNull();

    Storage::disk('public')->assertMissing('temporary-uploads/stale.png');
    Storage::disk('public')->assertExists('temporary-uploads/recent.png');
});

test('temporary uploads prune command is scheduled', function (): void {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('uploads:prune-temporary')
        ->assertSuccessful();
});
