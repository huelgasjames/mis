<?php
// Test login script
$ch = curl_init('http://localhost:8000/api/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email'=>'admin@misd.inventory.com','password'=>'password123']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo 'HTTP Code: ' . $httpcode . PHP_EOL;

// Try to extract error message from HTML
if (preg_match('/<title>(.*?)<\/title>/', $response, $matches)) {
    echo 'Page Title: ' . $matches[1] . PHP_EOL;
}
if (preg_match('/Whoops!<\/span>(.*?)<\/div>/s', $response, $matches)) {
    echo 'Error: ' . strip_tags($matches[1]) . PHP_EOL;
}
if (preg_match('/ErrorException.*?(Error.*?)<\/td>/s', $response, $matches)) {
    echo 'Exception: ' . strip_tags($matches[1]) . PHP_EOL;
}

// Also save to file for inspection
file_put_contents('test_response.html', $response);
echo 'Full response saved to test_response.html' . PHP_EOL;

curl_close($ch);
