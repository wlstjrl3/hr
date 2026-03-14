<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$_REQUEST = [
    'key' => 'dummy',
    'LIMIT' => 10,
    'START' => 0
];

function verifyApiKey() { return true; }

// Use curl to fetch the actual url outputs to see exactly what comes out
$url = "http://localhost/sys/psnlTotal.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// send request
$response = curl_exec($ch);
if($response === false) {
    echo curl_error($ch);
} else {
    echo "RESPONSE_START\n" . substr($response, 0, 500) . "\nRESPONSE_END";
}
curl_close($ch);
