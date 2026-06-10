<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MasterData\StoreBranchRequest;
use App\Http\Requests\Admin\MasterData\UpdateBranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Tables\MasterData\BranchTable;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    public function index(BranchTable $table): Response
    {
        $this->authorize('viewAny', Branch::class);

        return Inertia::render('admin/master-data/branches/Index', [
            'table' => Inertia::defer(fn (): array => $table->toPayload()),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Branch::class);

        return Inertia::render('admin/master-data/branches/Create', [
            'statusOptions' => GeneralStatus::options(),
        ]);
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $this->authorize('create', Branch::class);

        Branch::query()->create($request->validated());

        Inertia::flash('success', trans('admin.master_data.branch.message.created.success'));

        return to_route('admin.master-data.branches.index');
    }

    public function show(Branch $branch): Response
    {
        $this->authorize('view', $branch);

        return Inertia::render('admin/master-data/branches/Show', [
            'branch' => BranchResource::make($branch)->resolve(),
        ]);
    }

    public function edit(Branch $branch): Response
    {
        $this->authorize('update', $branch);

        return Inertia::render('admin/master-data/branches/Edit', [
            'branch' => BranchResource::make($branch)->resolve(),
            'statusOptions' => GeneralStatus::options(),
        ]);
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $this->authorize('update', $branch);

        $branch->update($request->validated());

        Inertia::flash('success', trans('admin.master_data.branch.message.updated.success'));

        return to_route('admin.master-data.branches.index');
    }
}
