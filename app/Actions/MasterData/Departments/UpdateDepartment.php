<?php

namespace App\Actions\MasterData\Departments;

use App\Models\Department;

class UpdateDepartment
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Department $department, array $data): void
    {
        $department->update($data);
    }
}
