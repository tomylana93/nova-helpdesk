<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum TicketStatus: string
{
    use HasOptions;

    case New = 'new';
    case Triaged = 'triaged';
    case WaitingForApproval = 'waiting_for_approval';
    case InProgress = 'in_progress';
    case WaitingForRequester = 'waiting_for_requester';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Triaged => 'Triaged',
            self::WaitingForApproval => 'Waiting for Approval',
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
            self::New => 'default',
            self::Triaged => 'secondary',
            self::WaitingForApproval => 'warning',
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
}
