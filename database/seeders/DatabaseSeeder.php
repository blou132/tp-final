<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
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

        $users = User::factory(3)->create();
        $users->each(fn (User $user) => $user->assignRole('user'));

        Ticket::factory(5)->for($admin)->create();
        Ticket::factory(10)->for($standardUser)->create();

        Payment::factory(5)->for($admin)->create();
        Payment::factory(10)->for($standardUser)->create();
    }
}
