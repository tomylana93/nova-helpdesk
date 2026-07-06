<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $ticket_type
 * @property int $next_number
 */
#[Fillable(['ticket_type', 'next_number'])]
#[WithoutIncrementing]
class TicketNumberSequence extends Model
{
    /** @use HasFactory<Factory<TicketNumberSequence>> */
    use HasFactory;

    protected $primaryKey = 'ticket_type';

    protected $keyType = 'string';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'next_number' => 'integer',
        ];
    }
}
