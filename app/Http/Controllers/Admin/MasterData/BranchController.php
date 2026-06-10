<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Actions\MasterData\Branches\CreateBranch;
use App\Actions\MasterData\Branches\UpdateBranch;
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

    public function store(StoreBranchRequest $request, CreateBranch $createBranch): RedirectResponse
    {
        $this->authorize('create', Branch::class);

        $createBranch->handle($request->validated());

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

    public function update(UpdateBranchRequest $request, Branch $branch, UpdateBranch $updateBranch): RedirectResponse
    {
        $this->authorize('update', $branch);

        $updateBranch->handle($branch, $request->validated());

        Inertia::flash('success', trans('admin.master_data.branch.message.updated.success'));

        return to_route('admin.master-data.branches.index');
    }
}
