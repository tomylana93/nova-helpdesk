<?php

use App\Actions\Helpdesk\AssignTicketToAgent;
use App\Enums\TicketStatus;
use App\Enums\UserStatus;
use App\Models\Ticket;
use App\Models\TicketCategory;

function openTicketsFor(string $agentId, int $count): void
{
    Ticket::factory()->count($count)->create([
        'assigned_to' => $agentId,
        'status' => TicketStatus::InProgress,
    ]);
}

test('returns the only active agent', function (): void {
    $agent = createAgentUser();
    $ticket = Ticket::factory()->create(['category_id' => TicketCategory::factory()->create()->id]);

    expect(app(AssignTicketToAgent::class)->handle($ticket)?->id)->toBe($agent->id);
});

test('returns null when there is no active agent', function (): void {
    $ticket = Ticket::factory()->create(['category_id' => TicketCategory::factory()->create()->id]);

    expect(app(AssignTicketToAgent::class)->handle($ticket))->toBeNull();
});

test('ignores disabled agents', function (): void {
    $active = createAgentUser();
    $disabled = createAgentUser();
    $disabled->update(['status' => UserStatus::Disable]);

    $ticket = Ticket::factory()->create(['category_id' => TicketCategory::factory()->create()->id]);

    expect(app(AssignTicketToAgent::class)->handle($ticket)?->id)->toBe($active->id);
});

test('picks the agent with the least open-ticket load', function (): void {
    $busy = createAgentUser();
    $free = createAgentUser();

    openTicketsFor($busy->id, 3);
    openTicketsFor($free->id, 1);

    $ticket = Ticket::factory()->create(['category_id' => TicketCategory::factory()->create()->id]);

    expect(app(AssignTicketToAgent::class)->handle($ticket)?->id)->toBe($free->id);
});
