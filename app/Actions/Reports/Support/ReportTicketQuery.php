<?php

namespace App\Actions\Reports\Support;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

class ReportTicketQuery
{
    /**
     * @return Builder<Ticket>
     */
    public function base(User $user, ReportFilters $filters): Builder
    {
        $query = Ticket::query()
            ->with([
                'requester:id,name',
                'assignee:id,name',
                'branch:id,name',
                'department:id,name',
                'category:id,name',
            ]);

        return $this->applyFilters($query, $user, $filters);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function applyFilters(Builder $query, User $user, ReportFilters $filters): Builder
    {
        if ($user->hasRole(UserRole::ItAgent->value)) {
            $query->where('assigned_to', $user->id);
        } elseif ($filters->assigneeId !== null) {
            $query->where('assigned_to', $filters->assigneeId);
        }

        return $query
            ->when($filters->branchId !== null, fn (Builder $query): Builder => $query->where('branch_id', $filters->branchId))
            ->when($filters->departmentId !== null, fn (Builder $query): Builder => $query->where('department_id', $filters->departmentId))
            ->when($filters->categoryId !== null, fn (Builder $query): Builder => $query->where('category_id', $filters->categoryId))
            ->when($filters->status !== null, fn (Builder $query): Builder => $query->where('status', $filters->status))
            ->when($filters->priority !== null, fn (Builder $query): Builder => $query->where('priority', $filters->priority))
            ->when($filters->type !== null, fn (Builder $query): Builder => $query->where('type', $filters->type));
    }

    /**
     * @return Builder<Ticket>
     */
    public function createdInPeriod(User $user, ReportFilters $filters): Builder
    {
        return $this->base($user, $filters)
            ->whereBetween('created_at', [$filters->period->start(), $filters->period->end()]);
    }

    /**
     * @return Builder<Ticket>
     */
    public function resolvedInPeriod(User $user, ReportFilters $filters): Builder
    {
        return $this->base($user, $filters)
            ->whereIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
            ->whereBetween('resolved_at', [$filters->period->start(), $filters->period->end()]);
    }

    /**
     * @return Builder<Ticket>
     */
    public function active(User $user, ReportFilters $filters): Builder
    {
        return $this->base($user, $filters)
            ->whereIn('status', TicketStatus::activeCases());
    }

    /**
     * @return Builder<Ticket>
     */
    public function overdue(User $user, ReportFilters $filters): Builder
    {
        return $this->active($user, $filters)
            ->whereNotNull('resolution_due_at')
            ->where('resolution_due_at', '<', Date::now());
    }
}
