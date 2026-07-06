<?php

namespace App\Actions\MasterData\Users;

use App\Enums\GeneralStatus;
use App\Http\Resources\BranchOptionResource;
use App\Http\Resources\DepartmentOptionResource;
use App\Models\Branch;
use App\Models\Department;

class GetUserFormOptions
{
    /**
     * @return array{branchOptions: list<array<string, mixed>>, departmentOptions: list<array<string, mixed>>}
     */
    public function handle(): array
    {
        $branchOptions = array_values(Branch::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapInto(BranchOptionResource::class)
            ->map->resolve()
            ->all());

        $departmentOptions = array_values(Department::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id'])
            ->mapInto(DepartmentOptionResource::class)
            ->map->resolve()
            ->all());

        return ['branchOptions' => $branchOptions, 'departmentOptions' => $departmentOptions];
    }
}
