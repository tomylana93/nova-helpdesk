<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Actions\MasterData\Departments\CreateDepartment;
use App\Actions\MasterData\Departments\GetDepartmentFormOptions;
use App\Actions\MasterData\Departments\UpdateDepartment;
use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MasterData\StoreDepartmentRequest;
use App\Http\Requests\Admin\MasterData\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
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

    public function create(GetDepartmentFormOptions $formOptions): Response
    {
        $this->authorize('create', Department::class);

        return Inertia::render('admin/master-data/departments/Create', [
            'statusOptions' => GeneralStatus::options(),
            ...$formOptions->handle(),
        ]);
    }

    public function store(StoreDepartmentRequest $request, CreateDepartment $createDepartment): RedirectResponse
    {
        $this->authorize('create', Department::class);

        $createDepartment->handle($request->validated());

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

    public function edit(Department $department, GetDepartmentFormOptions $formOptions): Response
    {
        $this->authorize('update', $department);

        return Inertia::render('admin/master-data/departments/Edit', [
            'department' => DepartmentResource::make($department)->resolve(),
            'statusOptions' => GeneralStatus::options(),
            ...$formOptions->handle(),
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department, UpdateDepartment $updateDepartment): RedirectResponse
    {
        $this->authorize('update', $department);

        $updateDepartment->handle($department, $request->validated());

        Inertia::flash('success', trans('admin.master_data.department.message.updated.success'));

        return to_route('admin.master-data.departments.index');
    }
}
