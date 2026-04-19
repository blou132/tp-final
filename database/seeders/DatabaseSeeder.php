<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $standardUser = User::query()->updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Standard User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['admin']);
        $standardUser->syncRoles(['user']);

        $extraUsers = collect([
            ['name' => 'Alice Martin', 'email' => 'alice@example.com'],
            ['name' => 'Bob Durant', 'email' => 'bob@example.com'],
            ['name' => 'Charlie Bernard', 'email' => 'charlie@example.com'],
        ])->map(function (array $data): User {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles(['user']);

            return $user;
        });

        $seedUsers = (new Collection([$admin, $standardUser]))->merge($extraUsers);
        $ticketStatuses = TicketStatus::values();
        $ticketPriorities = TicketPriority::values();
        $ticketCategories = TicketCategory::values();
        $paymentStatuses = PaymentStatus::values();

        foreach ($seedUsers as $userIndex => $user) {
            for ($ticketIndex = 1; $ticketIndex <= 5; $ticketIndex++) {
                $status = $ticketStatuses[($userIndex + $ticketIndex) % count($ticketStatuses)];
                $dueAt = in_array($status, [TicketStatus::OPEN->value, TicketStatus::IN_PROGRESS->value], true)
                    ? now()->addDays(($ticketIndex % 5) - 2)
                    : null;

                Ticket::query()->create([
                    'title' => "Ticket {$ticketIndex} - {$user->name}",
                    'description' => "Description du ticket {$ticketIndex} pour {$user->email}.",
                    'status' => $status,
                    'priority' => $ticketPriorities[($userIndex + $ticketIndex) % count($ticketPriorities)],
                    'category' => $ticketCategories[($userIndex + $ticketIndex) % count($ticketCategories)],
                    'due_at' => $dueAt,
                    'is_flagged' => ($ticketIndex + $userIndex) % 7 === 0,
                    'user_id' => $user->id,
                    'assigned_to' => $status === TicketStatus::CLOSED->value ? null : $admin->id,
                ]);
            }

            for ($paymentIndex = 1; $paymentIndex <= 3; $paymentIndex++) {
                Payment::query()->create([
                    'amount' => 50 + ($userIndex * 20) + ($paymentIndex * 10),
                    'status' => $paymentStatuses[($userIndex + $paymentIndex) % count($paymentStatuses)],
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
