<?php
// Test script to debug the 500 error
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing login POST request...\n";

$url = 'http://localhost:8000/login';
$data = [
    'username' => 'admin',
    'password' => 'pointofsale'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response:\n";
echo $response;
