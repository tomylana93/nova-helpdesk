<?php

namespace App\Http\Controllers\Helpdesk;

use App\Actions\Helpdesk\TransitionTicketStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TicketLifecycleController extends Controller
{
    /**
     * Agent-driven status change, enforced by the state machine in TransitionTicketStatus.
     */
    public function transition(Request $request, Ticket $ticket, TransitionTicketStatus $transition): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(TicketStatus::class)],
        ]);

        $transition->handle($ticket, TicketStatus::from($validated['status']), $request->user());

        Inertia::flash('success', trans('helpdesk.ticket.message.updated.success'));

        return to_route('tickets.show', $ticket);
    }

    public function reopen(Request $request, Ticket $ticket, TransitionTicketStatus $transition): RedirectResponse
    {
        $this->authorize('reopen', $ticket);

        $transition->handle($ticket, TicketStatus::Reopened, $request->user());

        Inertia::flash('success', trans('helpdesk.ticket.message.reopened.success'));

        return to_route('tickets.show', $ticket);
    }

    public function confirm(Request $request, Ticket $ticket, TransitionTicketStatus $transition): RedirectResponse
    {
        $this->authorize('confirmResolution', $ticket);

        $transition->handle($ticket, TicketStatus::Closed, $request->user());

        Inertia::flash('success', trans('helpdesk.ticket.message.confirmed.success'));

        return to_route('tickets.show', $ticket);
    }
}
