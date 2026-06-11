<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(TicketType::cases()),
            'branch_id' => Branch::factory(),
            'department_id' => Department::factory(),
            'requester_id' => User::factory(),
            'assigned_to' => null,
            'category_id' => TicketCategory::factory(),
            'priority' => fake()->randomElement(TicketPriority::cases()),
            'status' => TicketStatus::Open,
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'submitted_at' => now(),
        ];
    }
}
