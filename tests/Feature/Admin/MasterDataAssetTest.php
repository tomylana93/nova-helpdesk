<?php

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Branch;
use App\Models\User;

test('assets can be created by super admin', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    $branch = Branch::factory()->create();
    $assignee = User::factory()->create(['branch_id' => $branch->id]);

    $response = $this
        ->actingAs($actor)
        ->post(route('admin.master-data.assets.store'), [
            'asset_tag' => 'AST-10001',
            'name' => 'Lenovo ThinkPad',
            'category' => AssetCategory::Laptop->value,
            'status' => AssetStatus::InUse->value,
            'branch_id' => $branch->id,
            'user_id' => $assignee->id,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.assets.index'));

    $asset = Asset::query()->where('asset_tag', 'AST-10001')->first();

    expect($asset)->not->toBeNull()
        ->and($asset?->name)->toBe('Lenovo ThinkPad')
        ->and($asset?->category)->toBe(AssetCategory::Laptop)
        ->and($asset?->status)->toBe(AssetStatus::InUse)
        ->and($asset?->branch_id)->toBe($branch->id)
        ->and($asset?->user_id)->toBe($assignee->id);
});

test('asset assignee must belong to the selected branch', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    $branch = Branch::factory()->create();
    $otherBranch = Branch::factory()->create();
    $assignee = User::factory()->create(['branch_id' => $otherBranch->id]);

    $this
        ->actingAs($actor)
        ->from(route('admin.master-data.assets.create'))
        ->post(route('admin.master-data.assets.store'), [
            'asset_tag' => 'AST-10002',
            'name' => 'Dell Monitor',
            'category' => AssetCategory::Monitor->value,
            'status' => AssetStatus::InUse->value,
            'branch_id' => $branch->id,
            'user_id' => $assignee->id,
        ])
        ->assertSessionHasErrors('user_id')
        ->assertRedirect(route('admin.master-data.assets.create'));
});

test('assets can be updated and deleted by super admin', function (): void {
    $actor = grantAdminPermissions(User::factory()->create());
    $asset = Asset::factory()->create([
        'asset_tag' => 'AST-10003',
        'name' => 'Old Asset',
        'category' => AssetCategory::Device,
        'status' => AssetStatus::InStorage,
    ]);

    $this
        ->actingAs($actor)
        ->put(route('admin.master-data.assets.update', $asset), [
            'asset_tag' => 'AST-10004',
            'name' => 'Updated Asset',
            'category' => AssetCategory::License->value,
            'status' => AssetStatus::Retired->value,
            'branch_id' => null,
            'user_id' => null,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.assets.index'));

    $asset->refresh();

    expect($asset->asset_tag)->toBe('AST-10004')
        ->and($asset->name)->toBe('Updated Asset')
        ->and($asset->category)->toBe(AssetCategory::License)
        ->and($asset->status)->toBe(AssetStatus::Retired);

    $this
        ->actingAs($actor)
        ->delete(route('admin.master-data.assets.destroy', $asset))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.master-data.assets.index'));

    $this->assertModelMissing($asset);
});

test('unauthorized users cannot manage assets', function (): void {
    $user = createRequesterUser();
    $asset = Asset::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.master-data.assets.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('admin.master-data.assets.destroy', $asset))
        ->assertForbidden();
});
