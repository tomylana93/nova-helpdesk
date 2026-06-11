<?php

namespace App\Actions\Dashboard;

use App\Actions\Helpdesk\FormatTicketSla;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Date;

class GetDashboardMetrics
{
    public function __construct(
        private readonly FormatTicketSla $formatTicketSla,
    ) {}

    /**
     * Get dashboard metrics and charts data based on the authenticated user.
     *
     * @return array<string, mixed>
     */
    public function handle(User $user): array
    {
        $isAgent = $user->hasRole(UserRole::ItAgent->value);
        $isSuperAdmin = $user->hasRole(UserRole::SuperAdmin->value);
        $isRequester = ! $isAgent && ! $isSuperAdmin;

        $role = 'requester';
        if ($isAgent) {
            $role = 'it_agent';
        } elseif ($isSuperAdmin) {
            $role = 'super_admin';
        }

        $metrics = [];
        $recentTickets = [];
        $charts = [];

        if ($isRequester) {
            $baseQuery = Ticket::query()->where('requester_id', $user->id);

            $metrics = [
                [
                    'label' => 'Total Tickets',
                    'value' => (string) (clone $baseQuery)->count(),
                    'description' => 'Total tickets created by you',
                ],
                [
                    'label' => 'Active Tickets',
                    'value' => (string) (clone $baseQuery)->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed])->count(),
                    'description' => 'Tickets currently being processed',
                ],
                [
                    'label' => 'Resolved Tickets',
                    'value' => (string) (clone $baseQuery)->whereIn('status', [TicketStatus::Resolved, TicketStatus::Closed])->count(),
                    'description' => 'Tickets resolved or closed',
                ],
            ];

            $recentTickets = (clone $baseQuery)
                ->with(['category', 'requester', 'assignee'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Ticket $ticket): array => $this->recentTicket($ticket))
                ->all();

            // Breakdown by priority
            $priorityCounts = (clone $baseQuery)
                ->selectRaw('priority, count(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray();

            $priorityData = [];
            foreach (TicketPriority::cases() as $priority) {
                $priorityData[] = [
                    'name' => $priority->label(),
                    'value' => $priorityCounts[$priority->value] ?? 0,
                ];
            }

            $charts = [
                'priority' => $priorityData,
            ];
        } elseif ($isAgent) {
            $assignedQuery = Ticket::query()->where('assigned_to', $user->id);
            $unassignedQuery = Ticket::query()->whereNull('assigned_to');
            $pendingApprovalQuery = Ticket::query()->where('status', TicketStatus::PendingApproval);

            $activeAssignedCount = (clone $assignedQuery)->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed])->count();
            $activeUnassignedCount = (clone $unassignedQuery)->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed])->count();
            $pendingApprovalCount = $pendingApprovalQuery->count();

            // SLA Breached count for active tickets
            $slaBreachedCount = Ticket::query()
                ->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
                ->whereNotNull('resolution_due_at')
                ->where('resolution_due_at', '<', Date::now())
                ->count();

            $metrics = [
                [
                    'label' => 'Assigned Tickets',
                    'value' => (string) $activeAssignedCount,
                    'description' => 'Active tickets assigned to you',
                ],
                [
                    'label' => 'Unassigned Tickets',
                    'value' => (string) $activeUnassignedCount,
                    'description' => 'Tickets in the general pool',
                ],
                [
                    'label' => 'Pending Approvals',
                    'value' => (string) $pendingApprovalCount,
                    'description' => 'Service requests awaiting approval',
                ],
                [
                    'label' => 'SLA Breached',
                    'value' => (string) $slaBreachedCount,
                    'description' => 'Overdue tickets across the system',
                ],
            ];

            $recentTickets = Ticket::query()
                ->where(function ($q) use ($user): void {
                    $q->where('assigned_to', $user->id)
                        ->orWhereNull('assigned_to');
                })
                ->with(['category', 'requester', 'assignee'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Ticket $ticket): array => $this->recentTicket($ticket))
                ->all();

            // SLA Compliance rate calculation
            $totalResolved = (clone $assignedQuery)
                ->whereIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
                ->count();
            $resolvedWithinDue = (clone $assignedQuery)
                ->whereIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
                ->whereNotNull('resolution_due_at')
                ->whereColumn('resolved_at', '<=', 'resolution_due_at')
                ->count();
            $slaComplianceRate = $totalResolved > 0 ? (int) round(($resolvedWithinDue / $totalResolved) * 100) : 100;

            // Breakdown by status for all active tickets in system
            $statusCounts = Ticket::query()
                ->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $statusData = [];
            foreach (TicketStatus::cases() as $status) {
                if (in_array($status, [TicketStatus::Resolved, TicketStatus::Closed], true)) {
                    continue;
                }

                $statusData[] = [
                    'name' => $status->label(),
                    'value' => $statusCounts[$status->value] ?? 0,
                ];
            }

            $charts = [
                'status' => $statusData,
                'slaComplianceRate' => $slaComplianceRate,
            ];
        } elseif ($isSuperAdmin) {
            $totalTickets = Ticket::query()->count();
            $activeTickets = Ticket::query()->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed])->count();
            $resolvedTickets = Ticket::query()->whereIn('status', [TicketStatus::Resolved, TicketStatus::Closed])->count();
            $slaBreachedCount = Ticket::query()
                ->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
                ->whereNotNull('resolution_due_at')
                ->where('resolution_due_at', '<', Date::now())
                ->count();

            $metrics = [
                [
                    'label' => 'Total Tickets',
                    'value' => (string) $totalTickets,
                    'description' => 'All tickets in the database',
                ],
                [
                    'label' => 'Active Tickets',
                    'value' => (string) $activeTickets,
                    'description' => 'Tickets currently being processed',
                ],
                [
                    'label' => 'Resolved Tickets',
                    'value' => (string) $resolvedTickets,
                    'description' => 'Tickets resolved or closed',
                ],
                [
                    'label' => 'SLA Breached',
                    'value' => (string) $slaBreachedCount,
                    'description' => 'Active tickets past SLA due date',
                ],
            ];

            $recentTickets = Ticket::query()
                ->with(['category', 'requester', 'assignee'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Ticket $ticket): array => $this->recentTicket($ticket))
                ->all();

            // SLA Compliance rate calculation
            $totalResolved = Ticket::query()
                ->whereIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
                ->count();
            $resolvedWithinDue = Ticket::query()
                ->whereIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
                ->whereNotNull('resolution_due_at')
                ->whereColumn('resolved_at', '<=', 'resolution_due_at')
                ->count();
            $slaComplianceRate = $totalResolved > 0 ? (int) round(($resolvedWithinDue / $totalResolved) * 100) : 100;

            // Breakdown by priority
            $priorityCounts = Ticket::query()
                ->selectRaw('priority, count(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray();

            $priorityData = [];
            foreach (TicketPriority::cases() as $priority) {
                $priorityData[] = [
                    'name' => $priority->label(),
                    'value' => $priorityCounts[$priority->value] ?? 0,
                ];
            }

            $charts = [
                'priority' => $priorityData,
                'slaComplianceRate' => $slaComplianceRate,
            ];
        }

        return [
            'role' => $role,
            'metrics' => $metrics,
            'recentTickets' => $recentTickets,
            'charts' => $charts,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function recentTicket(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'subject' => $ticket->subject,
            'type' => $ticket->type->label(),
            'priority' => [
                'value' => $ticket->priority->value,
                'label' => $ticket->priority->label(),
                'variant' => $ticket->priority->variant(),
            ],
            'status' => [
                'value' => $ticket->status->value,
                'label' => $ticket->status->label(),
                'variant' => $ticket->status->variant(),
            ],
            'requester_name' => $ticket->requester->name,
            'assignee_name' => $ticket->assignee !== null ? $ticket->assignee->name : 'Unassigned',
            'sla' => $this->formatTicketSla->handle($ticket),
            'created_at' => $ticket->created_at?->toJSON(),
        ];
    }
}
