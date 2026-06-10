<?php

namespace App\Actions\MasterData\SlaPolicies;

use App\Models\SlaPolicy;

class UpdateSlaPolicy
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(SlaPolicy $slaPolicy, array $data): void
    {
        $slaPolicy->update($data);
    }
}
