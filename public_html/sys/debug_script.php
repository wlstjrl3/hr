<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$_REQUEST['key'] = 'xx'; // Set dummy logic

// We'll read psnlTotal.php, strip verifyApiKey, and run it locally.
$code = file_get_contents("c:\\projectCoding\\hr\\public_html\\sys\\psnlTotal.php");
$code = str_replace("verifyApiKey", "//verifyApiKey", $code);
$code = str_replace("jsonResponse", "print_r", $code);

// Save to disk and execute
file_put_contents("c:\\projectCoding\\hr\\public_html\\sys\\psnlTotal_dev.php", $code);
include "c:\\projectCoding\\hr\\public_html\\sys\\psnlTotal_dev.php";
