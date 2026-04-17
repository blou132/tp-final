<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
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
        $paymentStatuses = PaymentStatus::values();

        foreach ($seedUsers as $userIndex => $user) {
            for ($ticketIndex = 1; $ticketIndex <= 3; $ticketIndex++) {
                Ticket::query()->create([
                    'title' => "Ticket {$ticketIndex} - {$user->name}",
                    'description' => "Description du ticket {$ticketIndex} pour {$user->email}.",
                    'status' => $ticketStatuses[($userIndex + $ticketIndex) % count($ticketStatuses)],
                    'is_flagged' => false,
                    'user_id' => $user->id,
                ]);
            }

            for ($paymentIndex = 1; $paymentIndex <= 2; $paymentIndex++) {
                Payment::query()->create([
                    'amount' => 50 + ($userIndex * 20) + ($paymentIndex * 10),
                    'status' => $paymentStatuses[($userIndex + $paymentIndex) % count($paymentStatuses)],
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
