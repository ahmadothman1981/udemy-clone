<?php

$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die("No .env file found.\n");
}

$lines = file($envFile);
$errors = [];

foreach ($lines as $i => $line) {
    if (trim($line) === '' || str_starts_with(trim($line), '#')) {
        continue;
    }

    // Check for "KEY = VALUE" (spaces around =)
    if (preg_match('/^\s*[A-Z_]+\s+=\s.*/', $line)) {
        $errors[] = "Line " . ($i + 1) . ": Spaces around '=' detected. Remove spaces. (Line: " . trim($line) . ")";
    }
    
    // Check for missing =
    if (!str_contains($line, '=')) {
         $errors[] = "Line " . ($i + 1) . ": No '=' found. (Line: " . trim($line) . ")";
    }
}

if (count($errors) > 0) {
    echo "Found syntax errors in .env:\n";
    foreach ($errors as $error) {
        echo $error . "\n";
    }
} else {
    // Check if keys exist in file content
    $content = file_get_contents($envFile);
    if (!str_contains($content, 'GOOGLE_CLIENT_ID')) {
        echo "GOOGLE_CLIENT_ID not found in file text.\n";
    } else {
        echo ".env Syntax looks OK. Keys found in text.\n";
    }
}
