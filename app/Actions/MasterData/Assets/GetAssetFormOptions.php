<?php

namespace App\Actions\MasterData\Assets;

use App\Enums\GeneralStatus;
use App\Enums\UserStatus;
use App\Http\Resources\BranchOptionResource;
use App\Http\Resources\UserOptionResource;
use App\Models\Branch;
use App\Models\User;

class GetAssetFormOptions
{
    /**
     * @return array{branchOptions: list<array<string, mixed>>, userOptions: list<array<string, mixed>>}
     */
    public function handle(): array
    {
        $branchOptions = Branch::query()
            ->where('status', GeneralStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapInto(BranchOptionResource::class)
            ->map->resolve()
            ->all();

        $userOptions = User::query()
            ->where('status', UserStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapInto(UserOptionResource::class)
            ->map->resolve()
            ->all();

        return [
            'branchOptions' => $branchOptions,
            'userOptions' => $userOptions,
        ];
    }
}
