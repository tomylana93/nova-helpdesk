<?php

namespace App\Models;

use App\Enums\GeneralStatus;
use Database\Factories\QueueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property GeneralStatus $status
 */
#[Fillable(['name', 'description', 'status'])]
class Queue extends Model
{
    /** @use HasFactory<QueueFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GeneralStatus::class,
        ];
    }
}
