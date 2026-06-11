<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use Carbon\Carbon;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * @property TicketType $type
 * @property TicketStatus $status
 * @property TicketPriority $priority
 * @property string $ticket_number
 * @property string $subject
 * @property string $description
 * @property string $requester_id
 * @property string|null $assigned_to
 * @property string|null $branch_id
 * @property string|null $department_id
 * @property string|null $category_id
 * @property Carbon $submitted_at
 * @property Carbon|null $first_response_due_at
 * @property Carbon|null $resolution_due_at
 * @property Carbon|null $resolved_at
 * @property Carbon|null $closed_at
 * @property Branch|null $branch
 * @property Department|null $department
 * @property User $requester
 * @property User|null $assignee
 * @property TicketCategory|null $category
 * @property TicketApproval|null $approval
 */
#[Fillable([
    'type',
    'branch_id',
    'department_id',
    'requester_id',
    'assigned_to',
    'category_id',
    'priority',
    'status',
    'subject',
    'description',
    'submitted_at',
    'resolved_at',
    'closed_at',
])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory, HasUuids;

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket): void {
            if (empty($ticket->ticket_number)) {
                $prefix = $ticket->type->prefix();

                // Find the latest ticket of this type to determine the next number
                $lastTicket = self::query()
                    ->where('type', $ticket->type)
                    ->orderByDesc('ticket_number')
                    ->first();

                $nextNumber = 1;
                if ($lastTicket) {
                    $parts = Str::of($lastTicket->ticket_number)->explode('-');
                    $lastNum = isset($parts[1]) ? (int) $parts[1] : 0;
                    $nextNumber = $lastNum + 1;
                }

                $ticket->ticket_number = sprintf('%s-%05d', $prefix, $nextNumber);
            }

            if (empty($ticket->status)) {
                $ticket->status = TicketStatus::Open;
            }

            if (empty($ticket->priority)) {
                $ticket->priority = TicketPriority::Low;
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class)->oldest('occurred_at');
    }

    public function approval(): HasOne
    {
        return $this->hasOne(TicketApproval::class)->latest();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TicketType::class,
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'submitted_at' => 'datetime',
            'first_response_due_at' => 'datetime',
            'resolution_due_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
