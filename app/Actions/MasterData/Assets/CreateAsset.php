<?php

namespace App\Actions\MasterData\Assets;

use App\Models\Asset;

class CreateAsset
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Asset
    {
        return Asset::query()->create($data);
    }
}
