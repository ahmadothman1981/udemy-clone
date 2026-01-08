<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SavedPaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_payment_methods()
    {
        $user = User::factory()->create();
        SavedPaymentMethod::factory()->count(3)->create(['user_id' => $user->id, 'brand' => 'Visa', 'last4' => '4242', 'exp_month' => 12, 'exp_year' => 2030]);

        $response = $this->actingAs($user)->getJson('/api/payment-methods');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_add_card_payment_method()
    {
        $user = User::factory()->create();

        $data = [
            'type' => 'card',
            'brand' => 'Mastercard',
            'last4' => '8888',
            'exp_month' => 10,
            'exp_year' => 2028,
        ];

        $response = $this->actingAs($user)->postJson('/api/payment-methods', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('saved_payment_methods', [
            'user_id' => $user->id,
            'type' => 'card',
            'last4' => '8888',
        ]);
    }

    public function test_user_can_add_paypal_payment_method()
    {
        $user = User::factory()->create();

        $data = [
            'type' => 'paypal',
            'email' => 'test@example.com',
        ];

        $response = $this->actingAs($user)->postJson('/api/payment-methods', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('saved_payment_methods', [
            'user_id' => $user->id,
            'type' => 'paypal',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_can_delete_payment_method()
    {
        $user = User::factory()->create();
        $paymentMethod = SavedPaymentMethod::create([
            'user_id' => $user->id,
            'brand' => 'Visa',
            'last4' => '4242',
            'exp_month' => 12,
            'exp_year' => 2030,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/payment-methods/{$paymentMethod->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('saved_payment_methods', ['id' => $paymentMethod->id]);
    }

    public function test_user_cannot_delete_ethers_payment_method()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $paymentMethod = SavedPaymentMethod::create([
            'user_id' => $otherUser->id,
            'brand' => 'Visa',
            'last4' => '4242',
            'exp_month' => 12,
            'exp_year' => 2030,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/payment-methods/{$paymentMethod->id}");

        $response->assertStatus(404); // Or 403 depending on implementation, Laravel's findOrFail throws 404 for scoped queries usually
    }
}
