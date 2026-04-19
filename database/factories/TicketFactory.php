<?php

namespace Database\Factories;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(5),
            'description' => $this->faker->paragraph(3),
            'status' => $this->faker->randomElement(TicketStatus::values()),
            'priority' => $this->faker->randomElement(TicketPriority::values()),
            'category' => $this->faker->randomElement(TicketCategory::values()),
            'due_at' => $this->faker->optional(0.65)->dateTimeBetween('-2 days', '+7 days'),
            'is_flagged' => false,
            'user_id' => User::factory(),
            'assigned_to' => null,
        ];
    }
}
