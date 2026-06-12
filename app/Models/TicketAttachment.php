<?php

namespace App\Models;

use Database\Factories\TicketAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $attachable_id
 * @property string $attachable_type
 * @property string $file_path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size
 */
#[Fillable([
    'attachable_id',
    'attachable_type',
    'file_path',
    'original_name',
    'mime_type',
    'size',
])]
class TicketAttachment extends Model
{
    /** @use HasFactory<TicketAttachmentFactory> */
    use HasFactory, HasUuids;

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the full URL to the attachment.
     */
    protected function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
