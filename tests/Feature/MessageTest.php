<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_send_message()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $response = $this->actingAs($sender)->postJson('/api/messages', [
            'receiver_id' => $receiver->id,
            'content' => 'Hello there!',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('messages', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => 'Hello there!',
        ]);
    }

    public function test_user_can_list_conversations()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Message::create([
            'sender_id' => $otherUser->id,
            'receiver_id' => $user->id,
            'content' => 'Hi!',
            'created_at' => now()->subMinutes(5)
        ]);

        $response = $this->actingAs($user)->getJson('/api/messages');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => $otherUser->name]);
    }

    public function test_user_can_view_conversation_history()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $msg1 = Message::create(['sender_id' => $user->id, 'receiver_id' => $otherUser->id, 'content' => 'One']);
        $msg2 = Message::create(['sender_id' => $otherUser->id, 'receiver_id' => $user->id, 'content' => 'Two']);

        $response = $this->actingAs($user)->getJson("/api/messages/{$otherUser->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['content' => 'One'])
            ->assertJsonFragment(['content' => 'Two']);
    }
}
