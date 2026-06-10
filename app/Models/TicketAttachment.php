<?php

namespace App\Models;

use Database\Factories\TicketAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $ticket_id
 * @property string $file_path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size
 */
#[Fillable([
    'ticket_id',
    'file_path',
    'original_name',
    'mime_type',
    'size',
])]
class TicketAttachment extends Model
{
    /** @use HasFactory<TicketAttachmentFactory> */
    use HasFactory, HasUuids;

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the full URL to the attachment.
     */
    protected function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
