<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MasterData\StoreDepartmentRequest;
use App\Http\Requests\Admin\MasterData\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Branch;
use App\Models\Department;
use App\Tables\MasterData\DepartmentTable;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(DepartmentTable $table): Response
    {
        $this->authorize('viewAny', Department::class);

        return Inertia::render('admin/master-data/departments/Index', [
            'table' => Inertia::defer(fn (): array => $table->toPayload()),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Department::class);

        $branchOptions = Branch::query()
            ->where('status', GeneralStatus::Active->value)
            ->select(['id as value', 'name as label'])
            ->orderBy('name')
            ->get()
            ->toArray();

        return Inertia::render('admin/master-data/departments/Create', [
            'statusOptions' => GeneralStatus::options(),
            'branchOptions' => $branchOptions,
        ]);
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Department::class);

        Department::query()->create($request->validated());

        Inertia::flash('success', trans('admin.master_data.department.message.created.success'));

        return to_route('admin.master-data.departments.index');
    }

    public function show(Department $department): Response
    {
        $this->authorize('view', $department);

        return Inertia::render('admin/master-data/departments/Show', [
            'department' => DepartmentResource::make($department->load('branch'))->resolve(),
        ]);
    }

    public function edit(Department $department): Response
    {
        $this->authorize('update', $department);

        $branchOptions = Branch::query()
            ->where('status', GeneralStatus::Active->value)
            ->select(['id as value', 'name as label'])
            ->orderBy('name')
            ->get()
            ->toArray();

        return Inertia::render('admin/master-data/departments/Edit', [
            'department' => DepartmentResource::make($department)->resolve(),
            'statusOptions' => GeneralStatus::options(),
            'branchOptions' => $branchOptions,
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        $department->update($request->validated());

        Inertia::flash('success', trans('admin.master_data.department.message.updated.success'));

        return to_route('admin.master-data.departments.index');
    }
}
