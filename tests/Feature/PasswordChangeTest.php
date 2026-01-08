<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_password_and_login_with_new_password()
    {
        $user = User::factory()->create([
            'email' => 'test@change.com',
            'password' => 'oldpassword', // Factory/Model interaction might be complex, but let's assume this works for initial
        ]);

        // Login first to prove old password works (or fails if factory is broken)
        // Actually, let's just assert Hash check first
        // If Model has hashed cast, User::factory()->create(['password' => 'plain']) should work?
        // Wait, factory uses Hash::make. If cast exists, factory might be double hashing too.
        // Let's create user with just 'plain' in factory override to check behavior.
        
        // Step 1: Update Password via API
        $response = $this->actingAs($user)->putJson('/api/profile', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);

        // Step 2: Try to login with new password
        // We need to refresh user from DB to see what is stored
        $user->refresh();
        
        // This check simulates what AuthController::login does
        $check = Hash::check('newpassword123', $user->password);
        
        $this->assertTrue($check, 'Password hash verification failed. Likely double-hashing occurred.');
    }
}
