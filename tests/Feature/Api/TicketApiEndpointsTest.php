<?php

namespace Tests\Feature\Api;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_open_tickets_endpoint_returns_only_open_tickets_for_standard_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        Ticket::factory()->for($user)->create(['status' => TicketStatus::OPEN->value]);
        Ticket::factory()->for($user)->create(['status' => TicketStatus::CLOSED->value]);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.open-tickets'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.status', TicketStatus::OPEN->value);
    }

    public function test_open_tickets_endpoint_requires_authentication(): void
    {
        $response = $this->getJson(route('api.open-tickets'));

        $response->assertUnauthorized();
    }

    public function test_closed_tickets_endpoint_returns_closed_tickets(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        Ticket::factory()->for($user)->create(['status' => TicketStatus::CLOSED->value]);
        Ticket::factory()->for($user)->create(['status' => TicketStatus::OPEN->value]);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.closed-tickets'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.status', TicketStatus::CLOSED->value);
    }

    public function test_user_tickets_endpoint_supports_email_lookup_for_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $target = User::factory()->create();
        $target->assignRole('user');

        Ticket::factory()->for($target)->count(2)->create();

        Sanctum::actingAs($admin);

        $response = $this->getJson(route('api.user-tickets', ['email' => $target->email]));

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_user_tickets_endpoint_forbids_standard_user_on_other_email(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('user');

        $other = User::factory()->create();
        $other->assignRole('user');

        Ticket::factory()->for($other)->create();

        Sanctum::actingAs($actor);

        $response = $this->getJson(route('api.user-tickets', ['email' => $other->email]));

        $response->assertForbidden();
    }

    public function test_api_permission_is_required_even_if_user_can_view_any_tickets(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('tickets.viewAny');

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.open-tickets'));

        $response->assertForbidden();
    }

    public function test_stats_endpoint_returns_expected_shape(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Ticket::factory()->for($admin)->create(['status' => TicketStatus::OPEN->value]);
        Ticket::factory()->for($admin)->create(['status' => TicketStatus::IN_PROGRESS->value]);
        Ticket::factory()->for($admin)->create(['status' => TicketStatus::CLOSED->value]);

        Sanctum::actingAs($admin);

        $response = $this->getJson(route('api.stats'));

        $response->assertOk();
        $response->assertJsonPath('data.total', 3);
        $response->assertJsonPath('data.open', 1);
        $response->assertJsonPath('data.in_progress', 1);
        $response->assertJsonPath('data.closed', 1);
    }

    public function test_stats_endpoint_for_standard_user_does_not_expose_by_user_breakdown(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        Ticket::factory()->for($user)->create(['status' => TicketStatus::OPEN->value]);
        Ticket::factory()->for($user)->create(['status' => TicketStatus::CLOSED->value]);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.stats'));

        $response->assertOk();
        $response->assertJsonMissingPath('data.by_user');
    }
}
