<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . "/../dbconn/dbconn.php";
include __DIR__ . "/sql_safe_helper.php";

// Let's run a debug context similar to psnlTotal
$_REQUEST['key'] = 'xxx'; // Bypass dummy
// Let's see if SQL fails directly
$sqlTest = "SELECT NULLIF(GREATEST(IFNULL('2025', '0000'), IFNULL('2024', '0000')), '0000')";
$res = mysqli_query($conn, $sqlTest);
if(!$res) echo "MySQL Error 1: " . mysqli_error($conn) . "\n";

// Let's do an explain of psnlTotal
$_REQUEST = [
    'TRS_TYPE' => '1',
    'key' => 'xxx'
];

ob_start();
// By replacing verifyApiKey in this script, we can include psnlTotal.
// Wait, we can't redefine verifyApiKey since sql_safe_helper loads it.
// We can just query the exact SQL from psnlTotal.
$conn2 = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
// ... it's easier to just do simple sed on psnlTotal in memory.
$content = file_get_contents('psnlTotal.php');
$content = str_replace("verifyApiKey", "//verifyApiKey", $content);
$content = str_replace("jsonResponse", "print_r", $content);
file_put_contents('psnlTotal_debug.php', $content);

include 'psnlTotal_debug.php';

$output = ob_get_clean();
echo substr($output, 0, 1000);
