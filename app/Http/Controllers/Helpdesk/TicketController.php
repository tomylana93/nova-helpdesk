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
use App\Models\Asset;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketComment;
use App\Tables\Helpdesk\TicketTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        $ticket->load(['requester', 'assignee', 'branch', 'department', 'category', 'attachments', 'assets']);

        $user = $request->user();
        $isAgent = $user?->hasRole(UserRole::ItAgent) ?? false;
        $isAuditor = $user?->hasRole(UserRole::Auditor) ?? false;
        $isSuperAdmin = $user?->hasRole(UserRole::SuperAdmin) ?? false;
        $isOwner = $user !== null && $ticket->requester_id === $user->id;
        // The ticket owner acts as a requester for their own ticket. An auditor who opened a ticket
        // gets requester-style controls on it; on every other ticket they are read-only oversight.
        $isRequesterActor = $isOwner && ! $isAgent && ! $isSuperAdmin;

        // super_admin and auditor are read-only oversight: they can see everything (incl. internal
        // notes) but act on nothing they do not own.
        $canSeeInternal = $isAgent || $isSuperAdmin || $isAuditor;
        $viewerRole = $isAgent ? UserRole::ItAgent->value : ($isAuditor ? UserRole::Auditor->value : ($isSuperAdmin ? UserRole::SuperAdmin->value : UserRole::Requester->value));

        $commentsQuery = TicketComment::query()
            ->with(['user:id,name', 'attachments'])
            ->where('ticket_id', $ticket->id)
            ->latest();

        if (! $canSeeInternal) {
            $commentsQuery->where('visibility', 'public');
        }

        return Inertia::render('tickets/Show', [
            'ticket' => TicketResource::make($ticket)->resolve(),
            'assetOptions' => Asset::query()
                ->where('user_id', $ticket->requester_id)
                ->get(['id', 'name', 'asset_tag'])
                ->map(fn ($asset): array => [
                    'value' => $asset->id,
                    'label' => "[{$asset->asset_tag}] {$asset->name}",
                ])
                ->all(),
            'viewerRole' => $viewerRole,
            'canSeeInternal' => $canSeeInternal,
            'canAct' => $isAgent,
            'canReply' => $isAgent || $isRequesterActor,
            'availableTransitions' => $isAgent
                ? array_map(
                    fn (TicketStatus $to): array => ['value' => $to->value, 'label' => $to->label()],
                    $ticket->status->agentActionableTransitions(),
                )
                : [],
            'canApprove' => $isAgent && $user->can('approve', $ticket),
            // Guarded by $isRequesterActor so super_admin's/auditor's Gate::before or oversight access never surfaces these controls on tickets they do not own.
            'canReopen' => $isRequesterActor && $user->can('reopen', $ticket),
            'canConfirm' => $isRequesterActor && $user->can('confirmResolution', $ticket),
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
                'attachments' => $c->attachments->map(fn (TicketAttachment $attachment): array => [
                    'id' => $attachment->id,
                    'original_name' => $attachment->original_name,
                    'size' => $attachment->size,
                    'mime_type' => $attachment->mime_type,
                    'url' => $attachment->url,
                ])->all(),
            ])->all(),
        ]);
    }

    public function edit(Ticket $ticket, GetTicketFormOptions $formOptions): Response
    {
        $this->authorize('update', $ticket);

        $ticket->load(['requester', 'assignee', 'branch', 'department', 'category', 'assets']);

        return Inertia::render('tickets/Edit', [
            'ticket' => TicketResource::make($ticket)->resolve(),
            'statusOptions' => TicketStatus::options(),
            'priorityOptions' => TicketPriority::options(),
            ...$formOptions->handle(includeAgents: true, requesterId: $ticket->requester_id),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket, UpdateTicket $updateTicket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $updateTicket->handle($ticket, $request->validated(), $request->user());

        Inertia::flash('success', trans('helpdesk.ticket.message.updated.success'));

        return to_route('tickets.show', $ticket);
    }

    public function syncAssets(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'asset_ids' => ['nullable', 'array'],
            'asset_ids.*' => [
                'required',
                'uuid',
                Rule::exists('assets', 'id')->where('user_id', $ticket->requester_id),
            ],
        ]);

        $ticket->assets()->sync($validated['asset_ids'] ?? []);

        Inertia::flash('success', trans('helpdesk.ticket.message.assets_synced.success'));

        return back();
    }
}
