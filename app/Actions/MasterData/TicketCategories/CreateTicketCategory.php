<?php

namespace App\Actions\MasterData\TicketCategories;

use App\Models\TicketCategory;

class CreateTicketCategory
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): TicketCategory
    {
        return TicketCategory::query()->create($data);
    }
}
