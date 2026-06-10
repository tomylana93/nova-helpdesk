<?php

namespace App\Actions\MasterData\Branches;

use App\Models\Branch;

class CreateBranch
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Branch
    {
        return Branch::query()->create($data);
    }
}
