<?php
// error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mock authentication so we don't die
function verifyApiKey($conn, $key) { return true; }

// Use ob_start to capture everything
ob_start();
$_REQUEST = [
    'key' => 'mocked',
];

// Include the target file. Since we redefine verifyApiKey, we might get a "cannot redeclare" if sql_safe_helper is included. 
// So let's run it from command line with a php wrapper.
