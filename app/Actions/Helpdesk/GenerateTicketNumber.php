<?php

namespace App\Actions\Helpdesk;

use App\Enums\TicketType;
use App\Models\TicketNumberSequence;
use Illuminate\Support\Facades\DB;

class GenerateTicketNumber
{
    public function handle(TicketType $type): string
    {
        return DB::transaction(function () use ($type): string {
            $this->ensureSequenceExists($type);

            $sequence = TicketNumberSequence::query()
                ->whereKey($type->value)
                ->lockForUpdate()
                ->firstOrFail();

            $number = $sequence->next_number;

            $sequence->forceFill([
                'next_number' => $number + 1,
            ])->save();

            return sprintf('%s-%05d', $type->prefix(), $number);
        });
    }

    private function ensureSequenceExists(TicketType $type): void
    {
        $now = now();

        DB::table('ticket_number_sequences')->insertOrIgnore([
            'ticket_type' => $type->value,
            'next_number' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
