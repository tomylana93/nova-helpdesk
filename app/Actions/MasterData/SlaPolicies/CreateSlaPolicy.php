<?php

namespace App\Actions\MasterData\SlaPolicies;

use App\Models\SlaPolicy;

class CreateSlaPolicy
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): SlaPolicy
    {
        return SlaPolicy::query()->create($data);
    }
}
