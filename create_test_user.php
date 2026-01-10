<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

// Ensure test user doesn't exist
\App\Models\User::where('email', 'test_debug@example.com')->delete();

$user = \App\Models\User::create([
    'name' => 'Debug Test User',
    'email' => 'test_debug@example.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password'),
]);

echo "Test user created:\n";
echo "Email: test_debug@example.com\n";
echo "Password: password\n";
