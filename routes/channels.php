<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin-notifications', function ($user) {
    \Log::info('Admin channel auth attempt', ['user_id' => $user->id, 'email' => $user->email]);
    return true; // Temporarily allow all authenticated users for debugging
});
