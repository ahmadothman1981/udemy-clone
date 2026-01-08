<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset_link()
    {
        $user = User::factory()->create(['email' => 'forget@example.com']);

        // Mock Notification to avoid actual email sending (though Password broker might not use Notification facade directly depending on setup, usually it uses Mail)
        // For testing Password broker interaction, verifying response status is key.
        
        $response = $this->postJson('/api/forgot-password', ['email' => 'forget@example.com']);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'forget@example.com']);
    }

    public function test_user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $token = Password::createToken($user);

        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'reset@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }
}
