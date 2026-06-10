<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketApproval;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketApproval>
 */
class TicketApprovalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'reviewer_id' => null,
            'status' => 'pending',
            'decided_at' => null,
            'decision_note' => null,
        ];
    }
}
