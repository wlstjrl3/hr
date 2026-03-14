<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$_REQUEST['STAT_MODE'] = '1';
$_REQUEST['STAT_TARGET'] = 'ALL';
$_REQUEST['STAT_BASE_YEAR'] = '2024';
$_REQUEST['key'] = 'xx';

$code = file_get_contents("c:\\projectCoding\\hr\\public_html\\sys\\psnlTotal.php");
// Strip verifyApiKey so we can mock safely
$code = str_replace("verifyApiKey(\$conn, @\$_REQUEST['key'])", "//verifyApiKey", $code);

// Instead of jsonResponse causing a TypeError from mock print_r, we'll redefine jsonResponse.
$code = "<?php\nif(!function_exists('jsonResponse')) { function jsonResponse(\$conn, \$data) { echo 'SUCCESS'; print_r(array_keys(\$data)); } }\n?>\n" . substr($code, 5);

file_put_contents("c:\\projectCoding\\hr\\public_html\\sys\\psnlTotal_dev2.php", $code);
include "c:\\projectCoding\\hr\\public_html\\sys\\psnlTotal_dev2.php";
