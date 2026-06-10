<?php

namespace App\Actions\MasterData\Departments;

use App\Models\Department;

class CreateDepartment
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Department
    {
        return Department::query()->create($data);
    }
}
