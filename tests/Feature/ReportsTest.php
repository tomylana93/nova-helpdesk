<?php

use App\Enums\AdminPermission;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('guests are redirected away from reports', function (): void {
    $this->get(route('reports.index'))
        ->assertRedirect(route('login'));
});

test('requesters cannot access reports', function (): void {
    $this->actingAs(createRequesterUser())
        ->get(route('reports.index'))
        ->assertForbidden();
});

test('super admins can report across branch filtered helpdesk data', function (): void {
    $this->travelTo(Date::parse('2026-06-15 10:00:00'));

    $admin = grantSuperAdmin(User::factory()->create());
    $agent = createAgentUser();
    $otherAgent = createAgentUser();
    $branch = Branch::factory()->create(['name' => 'Jakarta']);
    $otherBranch = Branch::factory()->create(['name' => 'Surabaya']);
    $department = Department::factory()->create(['branch_id' => $branch->id, 'name' => 'IT']);
    $category = TicketCategory::factory()->create(['name' => 'Laptop']);

    $ticket = Ticket::factory()->create([
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'category_id' => $category->id,
        'assigned_to' => $agent->id,
        'type' => TicketType::Incident,
        'priority' => TicketPriority::High,
        'status' => TicketStatus::Resolved,
        'created_at' => Date::parse('2026-06-10 09:00:00'),
        'submitted_at' => Date::parse('2026-06-10 09:00:00'),
        'resolved_at' => Date::parse('2026-06-11 09:00:00'),
        'resolution_due_at' => Date::parse('2026-06-12 09:00:00'),
    ]);

    Ticket::factory()->create([
        'branch_id' => $otherBranch->id,
        'assigned_to' => $otherAgent->id,
        'status' => TicketStatus::Open,
        'created_at' => Date::parse('2026-06-10 09:00:00'),
        'submitted_at' => Date::parse('2026-06-10 09:00:00'),
    ]);

    TicketActivity::factory()->create([
        'ticket_id' => $ticket->id,
        'actor_id' => $agent->id,
        'event' => 'resolved',
        'occurred_at' => Date::parse('2026-06-11 09:05:00'),
    ]);

    $this->actingAs($admin)
        ->get(route('reports.index', [
            'mode' => 'monthly',
            'month' => 6,
            'year' => 2026,
            'branch_id' => $branch->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('reports/Index')
            ->where('filters.mode', 'monthly')
            ->where('filters.month', 6)
            ->where('filters.year', 2026)
            ->where('summary.created', 1)
            ->where('summary.resolved', 1)
            ->where('summary.complianceRate', 100)
            ->where('audit.rows.0.event', 'resolved')
            ->where('audit.rows.0.ticketNumber', $ticket->ticket_number)
        );
});

test('agents only report on assigned tickets', function (): void {
    $this->travelTo(Date::parse('2026-06-15 10:00:00'));

    $agent = createAgentUser();
    $otherAgent = createAgentUser();

    $visible = Ticket::factory()->create([
        'assigned_to' => $agent->id,
        'status' => TicketStatus::Open,
        'created_at' => Date::parse('2026-06-10 09:00:00'),
        'submitted_at' => Date::parse('2026-06-10 09:00:00'),
    ]);

    $hidden = Ticket::factory()->create([
        'assigned_to' => $otherAgent->id,
        'status' => TicketStatus::Open,
        'created_at' => Date::parse('2026-06-10 09:00:00'),
        'submitted_at' => Date::parse('2026-06-10 09:00:00'),
    ]);

    TicketActivity::factory()->create([
        'ticket_id' => $visible->id,
        'event' => 'visible_event',
        'occurred_at' => Date::parse('2026-06-10 10:00:00'),
    ]);
    TicketActivity::factory()->create([
        'ticket_id' => $hidden->id,
        'event' => 'hidden_event',
        'occurred_at' => Date::parse('2026-06-10 10:00:00'),
    ]);

    $this->actingAs($agent)
        ->get(route('reports.index', [
            'mode' => 'monthly',
            'month' => 6,
            'year' => 2026,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('summary.created', 1)
            ->where('summary.active', 1)
            ->where('audit.rows.0.event', 'visible_event')
            ->missing('audit.rows.1')
        );
});

test('auditors can report across all tickets', function (): void {
    $this->travelTo(Date::parse('2026-06-15 10:00:00'));

    $auditor = createAuditorUser();
    $agent = createAgentUser();
    $otherAgent = createAgentUser();

    Ticket::factory()->create([
        'assigned_to' => $agent->id,
        'status' => TicketStatus::Open,
        'created_at' => Date::parse('2026-06-10 09:00:00'),
        'submitted_at' => Date::parse('2026-06-10 09:00:00'),
    ]);

    Ticket::factory()->create([
        'assigned_to' => $otherAgent->id,
        'status' => TicketStatus::Open,
        'created_at' => Date::parse('2026-06-10 09:00:00'),
        'submitted_at' => Date::parse('2026-06-10 09:00:00'),
    ]);

    $this->actingAs($auditor)
        ->get(route('reports.index', [
            'mode' => 'monthly',
            'month' => 6,
            'year' => 2026,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('summary.created', 2)
            ->where('summary.active', 2)
        );
});

test('reports can be exported as xlsx downloads', function (): void {
    $agent = createAgentUser();
    Ticket::factory()->create([
        'assigned_to' => $agent->id,
        'created_at' => Date::parse('2026-06-10 09:00:00'),
        'submitted_at' => Date::parse('2026-06-10 09:00:00'),
    ]);

    $this->actingAs($agent)
        ->get(route('reports.export.operational', ['mode' => 'yearly', 'year' => 2026]))
        ->assertDownload('operational-report-yearly-2026.xlsx');

    $this->actingAs($agent)
        ->get(route('reports.export.audit', ['mode' => 'yearly', 'year' => 2026]))
        ->assertDownload('audit-report-yearly-2026.xlsx');
});

test('role sync grants report access to super admins agents and auditors only', function (): void {
    $this->artisan('permission:sync-roles')
        ->assertSuccessful();

    expect(Role::findByName(UserRole::SuperAdmin->value)->hasPermissionTo(AdminPermission::ViewReports->value))->toBeTrue()
        ->and(Role::findByName(UserRole::ItAgent->value)->hasPermissionTo(AdminPermission::ViewReports->value))->toBeTrue()
        ->and(Role::findByName(UserRole::Auditor->value)->hasPermissionTo(AdminPermission::ViewReports->value))->toBeTrue()
        ->and(Role::findByName(UserRole::Requester->value)->hasPermissionTo(AdminPermission::ViewReports->value))->toBeFalse();
});
