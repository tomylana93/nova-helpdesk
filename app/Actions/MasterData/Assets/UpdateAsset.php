<?php

namespace App\Actions\MasterData\Assets;

use App\Models\Asset;

class UpdateAsset
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Asset $asset, array $data): void
    {
        $asset->update($data);
    }
}
