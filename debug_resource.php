<?php

use Illuminate\Contracts\Console\Kernel;
use App\Http\Resources\UserResource;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

try {
    echo "Creating test user...\n";
    $user = \App\Models\User::factory()->create();

    echo "User created: ID {$user->id}\n";

    echo "Testing UserResource...\n";
    $resource = new UserResource($user);
    $json = json_encode($resource->resolve());

    echo "UserResource resolved successfully:\n";
    echo $json . "\n";

    // Now try to access roles relationship directly
    echo "Accessing roles relationship...\n";
    $roles = $user->roles;
    echo "Roles count: " . $roles->count() . "\n";

    // Cleanup
    $user->delete();
    echo "Cleanup complete.\n";

} catch (\Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
