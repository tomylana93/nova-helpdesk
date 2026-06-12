<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function roleUxTicket(TicketStatus $status, string $requesterId, ?string $agentId): Ticket
{
    return Ticket::factory()->create([
        'requester_id' => $requesterId,
        'assigned_to' => $agentId,
        'status' => $status,
        'category_id' => TicketCategory::factory()->create()->id,
    ]);
}

// --- Role-aware Show flags ---

test('the show page exposes lifecycle action controls to the assigned agent', function (): void {
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = roleUxTicket(TicketStatus::Open, $requester->id, $agent->id);

    $this->actingAs($agent)
        ->get(route('tickets.show', $ticket))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('tickets/Show')
            ->where('viewerRole', 'it_agent')
            ->where('canAct', true)
            ->where('canReply', true)
            ->where('canSeeInternal', true)
            ->where('canReopen', false)
            ->where('canConfirm', false)
            ->where('availableTransitions.0.value', 'in_progress')
        );
});

test('the show page is read-only oversight for a super admin', function (): void {
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $admin = grantSuperAdmin(User::factory()->create());
    $ticket = roleUxTicket(TicketStatus::Resolved, $requester->id, $agent->id);

    $this->actingAs($admin)
        ->get(route('tickets.show', $ticket))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('tickets/Show')
            ->where('viewerRole', 'super_admin')
            ->where('canAct', false)
            ->where('canReply', false)
            ->where('canSeeInternal', true)
            // Gate::before must not surface requester controls to the super admin.
            ->where('canReopen', false)
            ->where('canConfirm', false)
            ->where('availableTransitions', [])
        );
});

test('the show page is read-only oversight for an auditor on a ticket they do not own', function (): void {
    $auditor = createAuditorUser();
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = roleUxTicket(TicketStatus::Resolved, $requester->id, $agent->id);

    $this->actingAs($auditor)
        ->get(route('tickets.show', $ticket))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('tickets/Show')
            ->where('viewerRole', 'auditor')
            ->where('canAct', false)
            ->where('canReply', false)
            // Auditors keep full oversight visibility, including internal notes.
            ->where('canSeeInternal', true)
            ->where('canApprove', false)
            // Oversight access must never surface requester controls on tickets the auditor does not own.
            ->where('canReopen', false)
            ->where('canConfirm', false)
            ->where('availableTransitions', [])
        );
});

test('the show page gives an auditor requester controls on their own resolved ticket', function (): void {
    $auditor = createAuditorUser();
    $agent = createAgentUser();
    $ticket = roleUxTicket(TicketStatus::Resolved, $auditor->id, $agent->id);

    $this->actingAs($auditor)
        ->get(route('tickets.show', $ticket))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('tickets/Show')
            ->where('viewerRole', 'auditor')
            ->where('canAct', false)
            // As the ticket owner, the auditor may reply, confirm, and reopen their own ticket.
            ->where('canReply', true)
            ->where('canSeeInternal', true)
            ->where('canConfirm', true)
            ->where('canReopen', true)
            ->where('availableTransitions', [])
        );
});

test('the show page gives a requester confirm and reopen controls on a resolved ticket', function (): void {
    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = roleUxTicket(TicketStatus::Resolved, $requester->id, $agent->id);

    $this->actingAs($requester)
        ->get(route('tickets.show', $ticket))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('tickets/Show')
            ->where('viewerRole', 'requester')
            ->where('canAct', false)
            ->where('canReply', true)
            ->where('canSeeInternal', false)
            ->where('canConfirm', true)
            ->where('canReopen', true)
            ->where('availableTransitions', [])
        );
});

// --- Smoke: each role renders index/create/show without server errors ---

test('ticket pages render for the agent, requester, and super admin roles', function (Closure $factory): void {
    $user = $factory();
    // Owned by the acting user so the requester (who can only see their own) also passes.
    $ticket = roleUxTicket(TicketStatus::Open, $user->id, createAgentUser()->id);

    $this->actingAs($user)->get(route('tickets.index'))->assertOk();
    $this->actingAs($user)->get(route('tickets.create'))->assertOk();
    $this->actingAs($user)->get(route('tickets.show', $ticket))->assertOk();
})->with([
    'agent' => [createAgentUser(...)],
    'requester' => [createRequesterUser(...)],
    'super admin' => [fn (): User => grantSuperAdmin(User::factory()->create())],
]);

test('auditor can render ticket index, show, and create tickets like a requester', function (): void {
    $auditor = createAuditorUser();
    $ticket = roleUxTicket(TicketStatus::Open, createRequesterUser()->id, createAgentUser()->id);

    $this->actingAs($auditor)->get(route('tickets.index'))->assertOk();
    $this->actingAs($auditor)->get(route('tickets.show', $ticket))->assertOk();
    $this->actingAs($auditor)->get(route('tickets.create'))->assertOk();
});
