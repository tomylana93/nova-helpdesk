<?php

namespace App\Actions\MasterData\Branches;

use App\Models\Branch;

class UpdateBranch
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Branch $branch, array $data): void
    {
        $branch->update($data);
    }
}
