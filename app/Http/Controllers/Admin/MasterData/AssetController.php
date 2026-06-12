<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Actions\MasterData\Assets\CreateAsset;
use App\Actions\MasterData\Assets\GetAssetFormOptions;
use App\Actions\MasterData\Assets\UpdateAsset;
use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MasterData\StoreAssetRequest;
use App\Http\Requests\Admin\MasterData\UpdateAssetRequest;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Tables\MasterData\AssetTable;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AssetController extends Controller
{
    public function index(AssetTable $table): Response
    {
        $this->authorize('viewAny', Asset::class);

        return Inertia::render('admin/master-data/assets/Index', [
            'table' => Inertia::defer(fn (): array => $table->toPayload()),
        ]);
    }

    public function create(GetAssetFormOptions $formOptions): Response
    {
        $this->authorize('create', Asset::class);

        return Inertia::render('admin/master-data/assets/Create', [
            'categoryOptions' => AssetCategory::options(),
            'statusOptions' => AssetStatus::options(),
            ...$formOptions->handle(),
        ]);
    }

    public function store(StoreAssetRequest $request, CreateAsset $createAsset): RedirectResponse
    {
        $this->authorize('create', Asset::class);

        $createAsset->handle($request->validated());

        Inertia::flash('success', trans('admin.master_data.asset.message.created.success'));

        return to_route('admin.master-data.assets.index');
    }

    public function show(Asset $asset): Response
    {
        $this->authorize('view', $asset);

        return Inertia::render('admin/master-data/assets/Show', [
            'asset' => AssetResource::make($asset->load(['branch', 'user', 'tickets']))->resolve(),
        ]);
    }

    public function edit(Asset $asset, GetAssetFormOptions $formOptions): Response
    {
        $this->authorize('update', $asset);

        return Inertia::render('admin/master-data/assets/Edit', [
            'asset' => AssetResource::make($asset)->resolve(),
            'categoryOptions' => AssetCategory::options(),
            'statusOptions' => AssetStatus::options(),
            ...$formOptions->handle(),
        ]);
    }

    public function update(UpdateAssetRequest $request, Asset $asset, UpdateAsset $updateAsset): RedirectResponse
    {
        $this->authorize('update', $asset);

        $updateAsset->handle($asset, $request->validated());

        Inertia::flash('success', trans('admin.master_data.asset.message.updated.success'));

        return to_route('admin.master-data.assets.index');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $this->authorize('delete', $asset);

        $asset->delete();

        Inertia::flash('success', trans('admin.master_data.asset.message.deleted.success'));

        return to_route('admin.master-data.assets.index');
    }
}
