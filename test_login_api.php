<?php

$url = 'http://127.0.0.1:8000/api/login';
$data = [
    'email' => 'test_debug@example.com',
    'password' => 'password'
];

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n" .
            "Accept: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true // Fetch content even on failure (e.g. 500)
    ]
];

$context = stream_context_create($options);
echo "Sending request to $url...\n";

$result = file_get_contents($url, false, $context);
$headers = $http_response_header;

// Extract status code
preg_match('#HTTP/\d\.\d (\d+)#', $headers[0], $matches);
$statusCode = $matches[1] ?? 'unknown';

echo "Status Code: $statusCode\n";
echo "Response Body:\n";
echo $result . "\n";
