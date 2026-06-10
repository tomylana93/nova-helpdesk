<?php

namespace App\Actions\MasterData\TicketCategories;

use App\Models\TicketCategory;

class UpdateTicketCategory
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(TicketCategory $ticketCategory, array $data): void
    {
        $ticketCategory->update($data);
    }
}
