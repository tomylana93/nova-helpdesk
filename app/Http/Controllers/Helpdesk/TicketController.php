<?php

namespace App\Http\Controllers\Helpdesk;

use App\Actions\Helpdesk\CreateTicket;
use App\Actions\Helpdesk\GetTicketFormOptions;
use App\Actions\Helpdesk\UpdateTicket;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Helpdesk\StoreTicketRequest;
use App\Http\Requests\Helpdesk\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Tables\Helpdesk\TicketTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(TicketTable $table): Response
    {
        $this->authorize('viewAny', Ticket::class);

        return Inertia::render('tickets/Index', [
            'table' => Inertia::defer(fn (): array => $table->toPayload()),
        ]);
    }

    public function create(GetTicketFormOptions $formOptions): Response
    {
        $this->authorize('create', Ticket::class);

        return Inertia::render('tickets/Create', [
            'typeOptions' => TicketType::options(),
            'priorityOptions' => TicketPriority::options(),
            ...$formOptions->handle(),
        ]);
    }

    public function store(StoreTicketRequest $request, CreateTicket $createTicket): RedirectResponse
    {
        $this->authorize('create', Ticket::class);

        $ticket = $createTicket->handle($request->validated(), $request->user());

        Inertia::flash('success', trans('helpdesk.ticket.message.created.success'));

        return to_route('tickets.show', $ticket);
    }

    public function show(Request $request, Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load(['requester', 'assignee', 'branch', 'department', 'queue', 'category']);

        $isAgent = $request->user()?->hasRole(UserRole::ItAgent)
            || $request->user()?->hasRole(UserRole::SuperAdmin);

        $commentsQuery = TicketComment::query()
            ->with('user:id,name')
            ->where('ticket_id', $ticket->id)
            ->latest();

        if (! $isAgent) {
            $commentsQuery->where('visibility', 'public');
        }

        return Inertia::render('tickets/Show', [
            'ticket' => TicketResource::make($ticket)->resolve(),
            'canUpdate' => $request->user()?->can('update', $ticket),
            'canComment' => $request->user()?->can('view', $ticket),
            'isAgent' => $isAgent,
            'approval' => $ticket->approval ? [
                'status' => $ticket->approval->status,
                'reviewerName' => $ticket->approval->reviewer?->name,
                'decidedAt' => $ticket->approval->decided_at?->toJSON(),
                'decisionNote' => $ticket->approval->decision_note,
            ] : null,
            'comments' => $commentsQuery->get()->map(fn (TicketComment $c): array => [
                'id' => $c->id,
                'body' => $c->body,
                'visibility' => $c->visibility,
                'authorName' => $c->user->name,
                'createdAt' => $c->created_at?->toJSON(),
            ])->all(),
        ]);
    }

    public function edit(Ticket $ticket, GetTicketFormOptions $formOptions): Response
    {
        $this->authorize('update', $ticket);

        $ticket->load(['requester', 'assignee', 'branch', 'department', 'queue', 'category']);

        return Inertia::render('tickets/Edit', [
            'ticket' => TicketResource::make($ticket)->resolve(),
            'statusOptions' => TicketStatus::options(),
            'priorityOptions' => TicketPriority::options(),
            ...$formOptions->handle(includeAgents: true),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket, UpdateTicket $updateTicket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $updateTicket->handle($ticket, $request->validated(), $request->user());

        Inertia::flash('success', trans('helpdesk.ticket.message.updated.success'));

        return to_route('tickets.show', $ticket);
    }
}
