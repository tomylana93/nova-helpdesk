<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\TicketApprovalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $ticket_id
 * @property string|null $reviewer_id
 * @property string $status
 * @property Carbon|null $decided_at
 * @property string|null $decision_note
 * @property User|null $reviewer
 */
#[Fillable(['ticket_id', 'reviewer_id', 'status', 'decided_at', 'decision_note'])]
class TicketApproval extends Model
{
    /** @use HasFactory<TicketApprovalFactory> */
    use HasFactory, HasUuids;

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    protected function casts(): array
    {
        return ['decided_at' => 'datetime'];
    }
}
