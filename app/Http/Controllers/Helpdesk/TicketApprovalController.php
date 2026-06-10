<?php

namespace App\Http\Controllers\Helpdesk;

use App\Actions\Helpdesk\ApproveTicket;
use App\Actions\Helpdesk\RejectTicket;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketApprovalController extends Controller
{
    public function approve(Request $request, Ticket $ticket, ApproveTicket $approveTicket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        abort_unless($ticket->status === TicketStatus::WaitingForApproval, 422, 'Ticket is not pending approval.');

        $validated = $request->validate([
            'decision_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $approveTicket->handle($ticket, $request->user(), $validated['decision_note'] ?? null);

        Inertia::flash('success', trans('helpdesk.approval.message.approved'));

        return to_route('tickets.show', $ticket);
    }

    public function reject(Request $request, Ticket $ticket, RejectTicket $rejectTicket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        abort_unless($ticket->status === TicketStatus::WaitingForApproval, 422, 'Ticket is not pending approval.');

        $validated = $request->validate([
            'decision_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $rejectTicket->handle($ticket, $request->user(), $validated['decision_note'] ?? null);

        Inertia::flash('success', trans('helpdesk.approval.message.rejected'));

        return to_route('tickets.show', $ticket);
    }
}
