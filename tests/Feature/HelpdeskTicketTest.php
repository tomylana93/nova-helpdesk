<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Models\Branch;
use App\Models\Department;
use App\Models\SlaPolicy;
use App\Models\TemporaryUpload;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

// --- INDEX ---

test('requester sees only their own tickets', function (): void {
    $requester = createRequesterUser();
    $other = User::factory()->create();

    Ticket::factory()->create(['requester_id' => $requester->id, 'subject' => 'My Ticket']);
    Ticket::factory()->create(['requester_id' => $other->id, 'subject' => 'Other Ticket']);

    $response = $this->actingAs($requester)->get(route('tickets.index'));

    $response->assertStatus(200);
});

test('agent can access ticket index', function (): void {
    $agent = createAgentUser();

    $response = $this->actingAs($agent)->get(route('tickets.index'));

    $response->assertStatus(200);
});

test('ticket index exposes remaining sla state in the deferred table payload', function (): void {
    $this->travelTo(Date::parse('2026-06-11 10:00:00'));

    $agent = createAgentUser();
    $requester = createRequesterUser();

    Ticket::factory()->create([
        'requester_id' => $requester->id,
        'subject' => 'Future SLA',
        'status' => TicketStatus::Open,
        'first_response_due_at' => Date::parse('2026-06-11 10:45:00'),
        'resolution_due_at' => Date::parse('2026-06-11 10:20:00'),
        'created_at' => Date::parse('2026-06-11 09:00:00'),
    ]);

    Ticket::factory()->create([
        'requester_id' => $requester->id,
        'subject' => 'Past SLA',
        'status' => TicketStatus::Open,
        'first_response_due_at' => Date::parse('2026-06-11 09:45:00'),
        'resolution_due_at' => Date::parse('2026-06-11 09:30:00'),
        'created_at' => Date::parse('2026-06-11 09:01:00'),
    ]);

    Ticket::factory()->create([
        'requester_id' => $requester->id,
        'subject' => 'Completed SLA',
        'status' => TicketStatus::Resolved,
        'first_response_due_at' => Date::parse('2026-06-11 10:45:00'),
        'resolution_due_at' => Date::parse('2026-06-11 11:00:00'),
        'created_at' => Date::parse('2026-06-11 09:02:00'),
    ]);

    Ticket::factory()->create([
        'requester_id' => $requester->id,
        'subject' => 'No SLA',
        'status' => TicketStatus::Open,
        'first_response_due_at' => null,
        'resolution_due_at' => null,
        'created_at' => Date::parse('2026-06-11 09:03:00'),
    ]);

    $this
        ->actingAs($agent)
        ->get(route('tickets.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('tickets/Index')
            ->missing('table')
            ->loadDeferredProps(fn (Assert $reload): Assert => $reload
                ->has('table.rows', 4)
                ->where('table.rows.0.subject', 'No SLA')
                ->where('table.rows.0.sla.firstResponse.state', 'no_sla')
                ->where('table.rows.0.sla.firstResponse.remainingSeconds', null)
                ->where('table.rows.1.subject', 'Completed SLA')
                ->where('table.rows.1.sla.firstResponse.state', 'completed')
                ->where('table.rows.1.sla.resolution.state', 'completed')
                ->where('table.rows.2.subject', 'Past SLA')
                ->where('table.rows.2.sla.firstResponse.state', 'overdue')
                ->where('table.rows.2.sla.resolution.remainingSeconds', -1800)
                ->where('table.rows.3.subject', 'Future SLA')
                ->where('table.rows.3.sla.firstResponse.state', 'on_track')
                ->where('table.rows.3.sla.firstResponse.remainingSeconds', 2700)
                ->where('table.rows.3.sla.resolution.state', 'due_soon')
                ->where('table.rows.3.sla.resolution.remainingSeconds', 1200)
            ));
});

test('ticket index marks first response completed while resolution remains active', function (): void {
    $this->travelTo(Date::parse('2026-06-11 10:00:00'));

    $agent = createAgentUser();
    $requester = createRequesterUser();

    Ticket::factory()->create([
        'requester_id' => $requester->id,
        'assigned_to' => $agent->id,
        'subject' => 'Responded SLA',
        'status' => TicketStatus::InProgress,
        'submitted_at' => Date::parse('2026-06-11 10:00:00'),
        'first_response_due_at' => Date::parse('2026-06-11 10:45:00'),
        'first_responded_at' => Date::parse('2026-06-11 10:05:00'),
        'resolution_due_at' => Date::parse('2026-06-11 10:20:00'),
    ]);

    $this
        ->actingAs($agent)
        ->get(route('tickets.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('tickets/Index')
            ->missing('table')
            ->loadDeferredProps(fn (Assert $reload): Assert => $reload
                ->has('table.rows', 1)
                ->where('table.rows.0.sla.firstResponse.state', 'completed')
                ->where('table.rows.0.sla.firstResponse.statusLabel', 'Completed in 5 mins')
                ->where('table.rows.0.sla.resolution.state', 'due_soon')
                ->where('table.rows.0.sla.resolution.remainingSeconds', 1200)
            ));
});

test('ticket index exposes completed sla durations for first response and resolution', function (): void {
    $this->travelTo(Date::parse('2026-06-11 12:00:00'));

    $agent = createAgentUser();
    $requester = createRequesterUser();

    Ticket::factory()->create([
        'requester_id' => $requester->id,
        'assigned_to' => $agent->id,
        'subject' => 'Completed duration SLA',
        'status' => TicketStatus::Resolved,
        'submitted_at' => Date::parse('2026-06-11 10:00:00'),
        'first_response_due_at' => Date::parse('2026-06-11 10:30:00'),
        'first_responded_at' => Date::parse('2026-06-11 10:05:00'),
        'resolution_due_at' => Date::parse('2026-06-11 12:00:00'),
        'resolved_at' => Date::parse('2026-06-11 11:30:00'),
    ]);

    $this
        ->actingAs($agent)
        ->get(route('tickets.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('tickets/Index')
            ->missing('table')
            ->loadDeferredProps(fn (Assert $reload): Assert => $reload
                ->has('table.rows', 1)
                ->where('table.rows.0.sla.firstResponse.state', 'completed')
                ->where('table.rows.0.sla.firstResponse.statusLabel', 'Completed in 5 mins')
                ->where('table.rows.0.sla.firstResponse.remainingSeconds', 1500)
                ->where('table.rows.0.sla.resolution.state', 'completed')
                ->where('table.rows.0.sla.resolution.statusLabel', 'Completed in 1 hr 30 mins')
                ->where('table.rows.0.sla.resolution.remainingSeconds', 1800)
            ));
});

test('unauthenticated user is redirected from ticket index', function (): void {
    $this->get(route('tickets.index'))->assertRedirect(route('login'));
});

// --- CREATE / STORE ---

test('requester can submit an incident ticket', function (): void {
    $requester = createRequesterUser();
    $category = TicketCategory::factory()->create();

    $response = $this
        ->actingAs($requester)
        ->post(route('tickets.store'), [
            'type' => TicketType::Incident->value,
            'subject' => 'My printer is broken',
            'description' => 'Cannot print from any device.',
            'priority' => TicketPriority::Medium->value,
            'category_id' => $category->id,
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $ticket = Ticket::query()->where('subject', 'My printer is broken')->first();

    expect($ticket)->not->toBeNull()
        ->and($ticket?->requester_id)->toBe($requester->id)
        ->and($ticket?->status)->toBe(TicketStatus::Open)
        ->and($ticket?->ticket_number)->toStartWith('INC-');
});

test('a ticket opened by an auditor inherits their organisation context', function (): void {
    $branch = Branch::factory()->create();
    $department = Department::factory()->create(['branch_id' => $branch->id]);
    $auditor = createAuditorUser(['branch_id' => $branch->id, 'department_id' => $department->id]);
    $category = TicketCategory::factory()->create();

    $this->actingAs($auditor)
        ->post(route('tickets.store'), [
            'type' => TicketType::Incident->value,
            'subject' => 'Audit workstation issue',
            'description' => 'Reporting on behalf of compliance.',
            'priority' => TicketPriority::Low->value,
            'category_id' => $category->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $ticket = Ticket::query()->where('subject', 'Audit workstation issue')->first();

    expect($ticket)->not->toBeNull()
        ->and($ticket?->requester_id)->toBe($auditor->id)
        ->and($ticket?->branch_id)->toBe($branch->id)
        ->and($ticket?->department_id)->toBe($department->id);
});

test('service_request ticket starts in waiting_for_approval status', function (): void {
    $requester = createRequesterUser();
    $category = TicketCategory::factory()->create();

    $this->actingAs($requester)->post(route('tickets.store'), [
        'type' => TicketType::ServiceRequest->value,
        'subject' => 'Need new laptop',
        'description' => 'My laptop is too old.',
        'priority' => TicketPriority::Low->value,
        'category_id' => $category->id,
    ]);

    $ticket = Ticket::query()->where('subject', 'Need new laptop')->first();

    expect($ticket?->status)->toBe(TicketStatus::PendingApproval);
});

test('ticket submission validates required fields', function (): void {
    $requester = createRequesterUser();

    $response = $this
        ->actingAs($requester)
        ->from(route('tickets.create'))
        ->post(route('tickets.store'), []);

    $response->assertSessionHasErrors(['type', 'subject', 'description', 'priority', 'category_id']);
});

// --- SHOW ---

test('requester can view their own ticket', function (): void {
    $requester = createRequesterUser();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    $this->actingAs($requester)->get(route('tickets.show', $ticket))->assertStatus(200);
});

test('requester cannot view another users ticket', function (): void {
    $requester = createRequesterUser();
    $other = User::factory()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $other->id]);

    $this->actingAs($requester)->get(route('tickets.show', $ticket))->assertForbidden();
});

test('agent can view any ticket', function (): void {
    $agent = createAgentUser();
    $ticket = Ticket::factory()->create();

    $this->actingAs($agent)->get(route('tickets.show', $ticket))->assertStatus(200);
});

// --- EDIT / UPDATE ---

test('agent can update a ticket', function (): void {
    $agent = createAgentUser();
    $category = TicketCategory::factory()->create();
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Open, 'category_id' => $category->id]);

    $response = $this
        ->actingAs($agent)
        ->patch(route('tickets.update', $ticket), [
            'subject' => 'Updated subject',
            'description' => 'Updated description',
            'status' => TicketStatus::InProgress->value,
            'priority' => TicketPriority::High->value,
            'category_id' => $category->id,
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::InProgress)
        ->and($ticket->priority)->toBe(TicketPriority::High);
});

test('requester cannot update a ticket', function (): void {
    $requester = createRequesterUser();
    $category = TicketCategory::factory()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id, 'category_id' => $category->id]);

    $this->actingAs($requester)->patch(route('tickets.update', $ticket), [
        'subject' => 'Updated subject',
        'description' => 'Updated description',
        'status' => TicketStatus::Closed->value,
        'priority' => TicketPriority::Low->value,
        'category_id' => $category->id,
    ])->assertForbidden();
});

// --- COMMENTS ---

test('requester can post a public comment on their ticket', function (): void {
    $requester = createRequesterUser();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    $response = $this
        ->actingAs($requester)
        ->post(route('tickets.comments.store', $ticket), [
            'body' => 'I still have the issue.',
            'visibility' => 'public',
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    expect($ticket->comments()->where('body', 'I still have the issue.')->exists())->toBeTrue();
});

test('an agent comment marks first response for a ticket', function (): void {
    $this->travelTo(Date::parse('2026-06-11 10:00:00'));

    $agent = createAgentUser();
    $requester = createRequesterUser();
    $ticket = Ticket::factory()->create([
        'assigned_to' => $agent->id,
        'requester_id' => $requester->id,
        'first_responded_at' => null,
    ]);

    $this
        ->actingAs($agent)
        ->post(route('tickets.comments.store', $ticket), [
            'body' => 'I am checking this now.',
            'visibility' => 'public',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($ticket->fresh()->first_responded_at?->toDateTimeString())->toBe('2026-06-11 10:00:00');
});

test('a requester comment does not mark first response for a ticket', function (): void {
    $this->travelTo(Date::parse('2026-06-11 10:00:00'));

    $requester = createRequesterUser();
    $ticket = Ticket::factory()->create([
        'requester_id' => $requester->id,
        'first_responded_at' => null,
    ]);

    $this
        ->actingAs($requester)
        ->post(route('tickets.comments.store', $ticket), [
            'body' => 'Any update?',
            'visibility' => 'public',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($ticket->fresh()->first_responded_at)->toBeNull();
});

// --- APPROVAL ---

test('agent can approve a waiting_for_approval ticket', function (): void {
    $agent = createAgentUser();
    $ticket = Ticket::factory()->create([
        'assigned_to' => $agent->id,
        'status' => TicketStatus::PendingApproval,
    ]);

    $this
        ->actingAs($agent)
        ->post(route('tickets.approve', $ticket), ['decision_note' => 'Looks good'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::InProgress);
    expect($ticket->approval)->not->toBeNull();
    expect($ticket->approval?->status)->toBe('approved');
});

test('agent can reject a waiting_for_approval ticket', function (): void {
    $agent = createAgentUser();
    $ticket = Ticket::factory()->create([
        'assigned_to' => $agent->id,
        'status' => TicketStatus::PendingApproval,
    ]);

    $this
        ->actingAs($agent)
        ->post(route('tickets.reject', $ticket), ['decision_note' => 'Not justified'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Closed);
    expect($ticket->approval?->status)->toBe('rejected');
});

test('user without manage approvals permission cannot approve or reject a ticket', function (): void {
    $userWithoutPerm = createRequesterUser();
    $ticket = Ticket::factory()->create(['status' => TicketStatus::PendingApproval]);

    $this
        ->actingAs($userWithoutPerm)
        ->post(route('tickets.approve', $ticket), ['decision_note' => 'Looks good'])
        ->assertStatus(403);

    $this
        ->actingAs($userWithoutPerm)
        ->post(route('tickets.reject', $ticket), ['decision_note' => 'Not justified'])
        ->assertStatus(403);
});

test('an agent cannot approve or reject a ticket assigned to another agent', function (): void {
    $assignedAgent = createAgentUser();
    $otherAgent = createAgentUser();
    $ticket = Ticket::factory()->create([
        'assigned_to' => $assignedAgent->id,
        'status' => TicketStatus::PendingApproval,
    ]);

    $this
        ->actingAs($otherAgent)
        ->post(route('tickets.approve', $ticket), ['decision_note' => 'Looks good'])
        ->assertForbidden();

    $this
        ->actingAs($otherAgent)
        ->post(route('tickets.reject', $ticket), ['decision_note' => 'Not justified'])
        ->assertForbidden();

    expect($ticket->fresh()->status)->toBe(TicketStatus::PendingApproval);
});

test('non-agent cannot post internal comment', function (): void {
    $requester = createRequesterUser();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    $this
        ->actingAs($requester)
        ->post(route('tickets.comments.store', $ticket), [
            'body' => 'Internal note.',
            'visibility' => 'internal',
        ])
        ->assertSessionHasErrors(['visibility']);
});

test('cannot assign ticket to non-agent user', function (): void {
    $agent = createAgentUser();
    $category = TicketCategory::factory()->create();
    $ticket = Ticket::factory()->create(['category_id' => $category->id]);
    $nonAgent = createRequesterUser();

    $this
        ->actingAs($agent)
        ->patch(route('tickets.update', $ticket), [
            'subject' => 'Updated Subject',
            'description' => 'Updated Desc',
            'status' => TicketStatus::InProgress->value,
            'priority' => TicketPriority::High->value,
            'assigned_to' => $nonAgent->id,
            'category_id' => $category->id,
        ])
        ->assertSessionHasErrors(['assigned_to']);
});

test('a submitted ticket inherits branch and department from the requester profile', function (): void {
    $branch = Branch::factory()->create();
    $department = Department::factory()->create(['branch_id' => $branch->id]);
    $requester = createRequesterUser();
    $requester->update(['branch_id' => $branch->id, 'department_id' => $department->id]);

    $category = TicketCategory::factory()->create();

    $this
        ->actingAs($requester)
        ->post(route('tickets.store'), [
            'type' => TicketType::Incident->value,
            'subject' => 'Inherit org context',
            'description' => 'Test',
            'priority' => TicketPriority::Low->value,
            'category_id' => $category->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $ticket = Ticket::query()->latest()->first();
    expect($ticket?->branch_id)->toBe($branch->id)
        ->and($ticket?->department_id)->toBe($department->id);
});

test('sla due dates are computed correctly using submitted_at when creating a ticket with active SLA policy', function (): void {
    $requester = createRequesterUser();
    $category = TicketCategory::factory()->create();

    // Create an active SLA policy for Incident Critical
    SlaPolicy::factory()->create([
        'ticket_type' => 'incident',
        'priority' => 'critical',
        'first_response_target_minutes' => 15,
        'resolution_target_minutes' => 120,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($requester)
        ->post(route('tickets.store'), [
            'type' => TicketType::Incident->value,
            'subject' => 'Critical Incident',
            'description' => 'System is completely down.',
            'priority' => TicketPriority::Critical->value,
            'category_id' => $category->id,
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $ticket = Ticket::query()->where('subject', 'Critical Incident')->first();

    expect($ticket)->not->toBeNull()
        ->and($ticket?->submitted_at)->not->toBeNull()
        ->and($ticket?->first_response_due_at)->not->toBeNull()
        ->and($ticket?->resolution_due_at)->not->toBeNull();
});

test('requester can submit a ticket with attachments', function (): void {
    Storage::fake('public');

    $requester = createRequesterUser();
    $category = TicketCategory::factory()->create();

    $tempUpload = TemporaryUpload::query()->create([
        'id' => (string) Str::uuid(),
        'user_id' => $requester->id,
        'path' => 'temporary-uploads/test.png',
        'disk' => 'public',
        'original_name' => 'test.png',
        'mime_type' => 'image/png',
        'size' => 1024,
    ]);

    Storage::disk('public')->put($tempUpload->path, 'dummy content');

    $response = $this
        ->actingAs($requester)
        ->post(route('tickets.store'), [
            'type' => TicketType::Incident->value,
            'subject' => 'My printer is broken',
            'description' => 'Cannot print from any device.',
            'priority' => TicketPriority::Medium->value,
            'category_id' => $category->id,
            'attachment_upload_ids' => [$tempUpload->id],
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $ticket = Ticket::query()->where('subject', 'My printer is broken')->first();
    expect($ticket)->not->toBeNull()
        ->and($ticket->attachments)->toHaveCount(1)
        ->and($ticket->attachments->first()->original_name)->toBe('test.png');

    expect(TemporaryUpload::query()->find($tempUpload->id))->toBeNull();
    Storage::disk('public')->assertExists($ticket->attachments->first()->file_path);
});

test('requester can add a comment with attachments', function (): void {
    Storage::fake('public');

    $requester = createRequesterUser();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    $tempUpload = TemporaryUpload::query()->create([
        'id' => (string) Str::uuid(),
        'user_id' => $requester->id,
        'path' => 'temporary-uploads/comment.png',
        'disk' => 'public',
        'original_name' => 'comment.png',
        'mime_type' => 'image/png',
        'size' => 1024,
    ]);

    Storage::disk('public')->put($tempUpload->path, 'dummy content');

    $response = $this
        ->actingAs($requester)
        ->post(route('tickets.comments.store', $ticket), [
            'body' => 'I still have the issue.',
            'visibility' => 'public',
            'attachment_upload_ids' => [$tempUpload->id],
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $comment = $ticket->comments()->where('body', 'I still have the issue.')->first();
    expect($comment)->not->toBeNull()
        ->and($comment->attachments)->toHaveCount(1)
        ->and($comment->attachments->first()->original_name)->toBe('comment.png');

    expect(TemporaryUpload::query()->find($tempUpload->id))->toBeNull();
    Storage::disk('public')->assertExists($comment->attachments->first()->file_path);
});
