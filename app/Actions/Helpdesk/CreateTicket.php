<?php

namespace App\Actions\Helpdesk;

use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Models\Ticket;
use App\Models\User;

class CreateTicket
{
    public function __construct(
        private readonly AssignSlaPolicy $assignSla,
        private readonly RecordTicketActivity $recordActivity,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, User $requester): Ticket
    {
        $type = TicketType::from($data['type']);
        $initialStatus = $type === TicketType::ServiceRequest
            ? TicketStatus::WaitingForApproval
            : TicketStatus::New;

        $ticket = Ticket::query()->create([
            ...$data,
            'requester_id' => $requester->id,
            'status' => $initialStatus,
        ]);

        $this->assignSla->handle($ticket);
        $this->recordActivity->handle($ticket, 'created', $requester);

        return $ticket;
    }
}
