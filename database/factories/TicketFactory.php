<?php

namespace Database\Factories;

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
            'is_flagged' => false,
            'user_id' => User::factory(),
        ];
    }
}
