<?php

namespace App\Actions\Helpdesk;

use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Enums\UserRole;
use App\Events\TicketCreated;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketNotification;
use Illuminate\Support\Facades\Notification;

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

        $isAgent = $requester->hasRole(UserRole::ItAgent->value) || $requester->hasRole(UserRole::SuperAdmin->value);
        $branchId = $data['branch_id'] ?? null;
        $departmentId = $data['department_id'] ?? null;

        if (! $isAgent) {
            $branchId = $branchId ?: $requester->branch_id;
            $departmentId = $departmentId ?: $requester->department_id;
        }

        $ticket = Ticket::query()->create([
            ...$data,
            'branch_id' => $branchId,
            'department_id' => $departmentId,
            'requester_id' => $requester->id,
            'status' => $initialStatus,
        ]);

        $this->assignSla->handle($ticket);
        $this->recordActivity->handle($ticket, 'created', $requester);

        // 1. Notify Requester
        $requester->notify(new TicketNotification($ticket, 'created', "Your ticket {$ticket->ticket_number} has been created."));

        // 2. Notify Assignee OR Broadcast to all agents
        if (! empty($ticket->assigned_to)) {
            $assignee = User::query()->find($ticket->assigned_to);
            if ($assignee) {
                $assignee->notify(new TicketNotification($ticket, 'assigned', "Ticket {$ticket->ticket_number} has been assigned to you."));
            }
        } else {
            event(new TicketCreated($ticket));
        }

        // 3. Notify Super Admins if approval required
        if ($ticket->status === TicketStatus::WaitingForApproval) {
            $superAdmins = User::query()->role(UserRole::SuperAdmin->value)->get();
            Notification::send($superAdmins, new TicketNotification($ticket, 'approval_request', "Ticket {$ticket->ticket_number} requires approval."));
        }

        return $ticket;
    }
}
