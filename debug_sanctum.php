<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

try {
    echo "Attempting to create a test user...\n";

    // Create a unique email to avoid duplicates
    $email = 'debug_' . time() . '@example.com';

    $user = \App\Models\User::create([
        'name' => 'Debug User',
        'email' => $email,
        'password' => '$2y$12$K.zG8.1.1.1.1.1.', // dummy hash
    ]);

    echo "User created: ID {$user->id}\n";

    echo "Attempting to create Sanctum token...\n";
    $token = $user->createToken('debug-token');

    echo "SUCCESS! Token created: " . substr($token->plainTextToken, 0, 15) . "...\n";

    // Cleanup
    $user->tokens()->delete();
    $user->delete();
    echo "Cleanup complete.\n";

} catch (\Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    // echo $e->getTraceAsString();
}
