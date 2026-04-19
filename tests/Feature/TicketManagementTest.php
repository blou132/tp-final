<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_user_can_create_ticket_and_content_is_filtered(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->post(route('tickets.store'), [
            'title' => 'My shit title',
            'description' => 'This contains merde in text.',
            'status' => TicketStatus::OPEN->value,
        ]);

        $response->assertRedirect(route('tickets.index'));

        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'title' => 'My **** title',
            'is_flagged' => true,
        ]);
    }

    public function test_user_cannot_update_ticket_of_another_user(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('user');

        $other = User::factory()->create();
        $other->assignRole('user');

        $ticket = Ticket::factory()->for($owner)->create();

        $response = $this->actingAs($other)->put(route('tickets.update', $ticket), [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'status' => TicketStatus::IN_PROGRESS->value,
        ]);

        $response->assertForbidden();
    }

    public function test_user_cannot_view_ticket_of_another_user(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('user');

        $other = User::factory()->create();
        $other->assignRole('user');

        $ticket = Ticket::factory()->for($owner)->create();

        $response = $this->actingAs($other)->get(route('tickets.show', $ticket));

        $response->assertForbidden();
    }

    public function test_admin_can_delete_any_ticket(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $owner = User::factory()->create();
        $owner->assignRole('user');

        $ticket = Ticket::factory()->for($owner)->create();

        $response = $this->actingAs($admin)->delete(route('tickets.destroy', $ticket));

        $response->assertRedirect(route('tickets.index'));

        $this->assertDatabaseMissing('tickets', [
            'id' => $ticket->id,
        ]);
    }
}
