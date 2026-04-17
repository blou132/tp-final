<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_user_can_create_payment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->post(route('payments.store'), [
            'amount' => 149.99,
            'status' => PaymentStatus::PENDING->value,
        ]);

        $response->assertRedirect(route('payments.index'));

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'status' => PaymentStatus::PENDING->value,
        ]);
    }

    public function test_user_cannot_delete_someone_else_payment(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('user');

        $other = User::factory()->create();
        $other->assignRole('user');

        $payment = Payment::factory()->for($owner)->create();

        $response = $this->actingAs($other)->delete(route('payments.destroy', $payment));

        $response->assertForbidden();
    }

    public function test_admin_can_update_any_payment(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $owner = User::factory()->create();
        $owner->assignRole('user');

        $payment = Payment::factory()->for($owner)->create([
            'status' => PaymentStatus::PENDING->value,
        ]);

        $response = $this->actingAs($admin)->put(route('payments.update', $payment), [
            'amount' => 222.50,
            'status' => PaymentStatus::PAID->value,
        ]);

        $response->assertRedirect(route('payments.index'));

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::PAID->value,
        ]);
    }
}
