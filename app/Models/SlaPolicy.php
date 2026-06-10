<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketType;
use Database\Factories\SlaPolicyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $name
 * @property TicketType|null $ticket_type
 * @property TicketPriority $priority
 * @property string|null $queue_id
 * @property int $first_response_target_minutes
 * @property int $resolution_target_minutes
 * @property bool $is_active
 * @property Queue|null $queue
 */
#[Fillable([
    'name',
    'ticket_type',
    'priority',
    'queue_id',
    'first_response_target_minutes',
    'resolution_target_minutes',
    'is_active',
])]
class SlaPolicy extends Model
{
    /** @use HasFactory<SlaPolicyFactory> */
    use HasFactory, HasUuids;

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    protected function casts(): array
    {
        return [
            'ticket_type' => TicketType::class,
            'priority' => TicketPriority::class,
            'is_active' => 'boolean',
        ];
    }
}
