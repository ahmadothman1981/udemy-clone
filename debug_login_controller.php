<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;

try {
    echo "Creating request...\n";
    $request = Request::create('/login', 'POST', [
        'email' => 'test_debug@example.com',
        'password' => 'password'
    ]);

    echo "Instantiating AuthController...\n";
    $controller = new AuthController();

    echo "Calling login method...\n";
    $response = $controller->login($request);

    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";

} catch (\Exception $e) {
    echo "CRITICAL EXCEPTION CAUGHT:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
