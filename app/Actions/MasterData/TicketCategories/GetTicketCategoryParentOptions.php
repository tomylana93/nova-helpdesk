<?php

namespace App\Actions\MasterData\TicketCategories;

use App\Enums\GeneralStatus;
use App\Http\Resources\TicketCategoryOptionResource;
use App\Models\TicketCategory;

class GetTicketCategoryParentOptions
{
    /**
     * @return list<array<string, mixed>>
     */
    public function handle(?string $excludeId = null): array
    {
        return TicketCategory::query()
            ->whereNull('parent_id')
            ->where('status', GeneralStatus::Active)
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapInto(TicketCategoryOptionResource::class)
            ->map->resolve()
            ->all();
    }
}
