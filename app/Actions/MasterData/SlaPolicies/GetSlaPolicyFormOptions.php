<?php

namespace App\Actions\MasterData\SlaPolicies;

use App\Enums\TicketPriority;
use App\Enums\TicketType;

class GetSlaPolicyFormOptions
{
    /**
     * @return array{typeOptions: array<int, array<string, mixed>>, priorityOptions: array<int, array<string, mixed>>}
     */
    public function handle(): array
    {
        return [
            'typeOptions' => TicketType::options(),
            'priorityOptions' => TicketPriority::options(),
        ];
    }
}
