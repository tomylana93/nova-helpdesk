<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum TicketStatus: string
{
    use HasOptions;

    case Open = 'open';
    case PendingApproval = 'pending_approval';
    case InProgress = 'in_progress';
    case WaitingForRequester = 'waiting_for_requester';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::PendingApproval => 'Pending Approval',
            self::InProgress => 'In Progress',
            self::WaitingForRequester => 'Waiting for Requester',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Reopened => 'Reopened',
        };
    }

    public function variant(): string
    {
        return match ($this) {
            self::Open => 'default',
            self::PendingApproval => 'warning',
            self::InProgress => 'info',
            self::WaitingForRequester => 'warning',
            self::Resolved => 'success',
            self::Closed => 'outline',
            self::Reopened => 'destructive',
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Resolved, self::Closed], true);
    }

    /**
     * Whether a ticket may legally move from this status to the given status.
     *
     * The state machine is the single source of truth for status changes; controllers
     * and actions must consult it rather than mutating `status` directly.
     */
    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedTransitions(), true);
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Open => [self::InProgress, self::WaitingForRequester, self::Closed],
            self::PendingApproval => [self::InProgress, self::Closed],
            self::InProgress => [self::WaitingForRequester, self::Resolved, self::Closed],
            self::WaitingForRequester => [self::InProgress, self::Resolved, self::Closed],
            self::Resolved => [self::Closed, self::Reopened],
            self::Closed => [self::Reopened],
            self::Reopened => [self::InProgress, self::WaitingForRequester, self::Resolved],
        };
    }

    /**
     * Transitions an agent may trigger from the ticket detail UI. Approval moves (from
     * PendingApproval) go through the approve/reject form and Reopened is a requester
     * action, so both are excluded here.
     *
     * @return list<self>
     */
    public function agentActionableTransitions(): array
    {
        if ($this === self::PendingApproval) {
            return [];
        }

        return array_values(array_filter(
            $this->allowedTransitions(),
            static fn (self $to): bool => $to !== self::Reopened,
        ));
    }
}
