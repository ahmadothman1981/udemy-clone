<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoCodeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->user = User::factory()->create();
    }

    public function test_can_validate_promo_code()
    {
        $promo = PromoCode::create([
            'code' => 'SAVE20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/promo-codes/validate', [
                'code' => 'SAVE20',
                'order_total' => 100,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'discount_amount' => 20,
                'new_total' => 80,
            ]);
    }

    public function test_invalid_promo_code_returns_error()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/promo-codes/validate', [
                'code' => 'INVALID',
                'order_total' => 100,
            ]);

        $response->assertStatus(404)
            ->assertJson(['valid' => false]);
    }

    public function test_expired_promo_code_fails()
    {
        PromoCode::create([
            'code' => 'EXPIRED',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'expires_at' => now()->subDay(),
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/promo-codes/validate', [
                'code' => 'EXPIRED',
                'order_total' => 100,
            ]);

        $response->assertStatus(400)
            ->assertJson(['valid' => false]);
    }

    public function test_fixed_discount_promo_code()
    {
        PromoCode::create([
            'code' => 'FLAT50',
            'discount_type' => 'fixed',
            'discount_value' => 50,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/promo-codes/validate', [
                'code' => 'FLAT50',
                'order_total' => 100,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'discount_amount' => 50,
                'new_total' => 50,
            ]);
    }

    public function test_min_purchase_requirement()
    {
        PromoCode::create([
            'code' => 'MIN100',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_purchase' => 100,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/promo-codes/validate', [
                'code' => 'MIN100',
                'order_total' => 50,
            ]);

        $response->assertStatus(400)
            ->assertJson(['valid' => false]);
    }
}
