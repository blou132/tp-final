<?php

namespace Tests\Feature;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
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

    public function test_admin_can_assign_ticket_on_create(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $assignee = User::factory()->create();
        $assignee->assignRole('user');

        $response = $this->actingAs($admin)->post(route('tickets.store'), [
            'title' => 'Incident de paiement critique',
            'description' => 'Le client indique un débit sans confirmation.',
            'status' => TicketStatus::OPEN->value,
            'priority' => TicketPriority::URGENT->value,
            'category' => TicketCategory::BILLING->value,
            'assigned_to' => $assignee->id,
        ]);

        $response->assertRedirect(route('tickets.index'));

        $this->assertDatabaseHas('tickets', [
            'user_id' => $admin->id,
            'assigned_to' => $assignee->id,
            'priority' => TicketPriority::URGENT->value,
            'category' => TicketCategory::BILLING->value,
        ]);
    }

    public function test_standard_user_cannot_assign_ticket_on_create(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $assignee = User::factory()->create();
        $assignee->assignRole('user');

        $response = $this->actingAs($user)->from(route('tickets.create'))->post(route('tickets.store'), [
            'title' => 'Ticket non assignable',
            'description' => 'Le champ assigned_to doit rester réservé aux admins.',
            'status' => TicketStatus::OPEN->value,
            'assigned_to' => $assignee->id,
        ]);

        $response->assertRedirect(route('tickets.create'));
        $response->assertSessionHasErrors('assigned_to');

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_ticket_export_only_contains_user_visible_rows(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $other = User::factory()->create();
        $other->assignRole('user');

        Ticket::factory()->for($user)->create([
            'title' => 'Ticket visible utilisateur',
            'status' => TicketStatus::OPEN->value,
        ]);

        Ticket::factory()->for($other)->create([
            'title' => 'Ticket non visible utilisateur',
            'status' => TicketStatus::OPEN->value,
        ]);

        $response = $this->actingAs($user)->get(route('tickets.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Ticket visible utilisateur', $csv);
        $this->assertStringNotContainsString('Ticket non visible utilisateur', $csv);
    }
}
