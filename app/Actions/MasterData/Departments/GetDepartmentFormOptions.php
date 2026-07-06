<?php

namespace App\Actions\MasterData\Departments;

use App\Enums\GeneralStatus;
use App\Http\Resources\BranchOptionResource;
use App\Models\Branch;

class GetDepartmentFormOptions
{
    /**
     * @return array{branchOptions: list<array<string, mixed>>}
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

        return ['branchOptions' => $branchOptions];
    }
}
