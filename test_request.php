<?php

// Simple test without Kohana framework
echo "Testing GraphQL request...\n";

$api_url = 'http://api-vallery-v1:3000';
$auth = base64_encode('We8T3am:Gotty251294*');

$query = sprintf("{
  Lead(id: %s) {
    id
    dataEmissao
    clientePOID
  }
}", 637672);

$url = $api_url . '/gql/?query=' . urlencode($query);

echo "URL: " . $url . "\n";
echo "Auth: " . $auth . "\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Authorization: Basic ' . $auth,
        'timeout' => 10
    ]
]);

echo "Making request...\n";
$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "Request failed\n";
    $error = error_get_last();
    echo "Error: " . $error['message'] . "\n";
} else {
    echo "Response length: " . strlen($response) . "\n";
    echo "Response: " . $response . "\n";
}