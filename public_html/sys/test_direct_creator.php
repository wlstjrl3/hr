<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// We'll write to a file, then run it through php executable to see output directly.
$php_code = <<<'PHP'
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$_REQUEST = ['key' => 'mocked', 'LIMIT' => 10, 'START' => 0];
require_once "c:\\projectCoding\\hr\\public_html\\dbconn\\dbconn.php";
require_once "c:\\projectCoding\\hr\\public_html\\sys\\sql_safe_helper.php";

$content = file_get_contents("c:\\projectCoding\\hr\\public_html\\sys\\psnlTotal.php");
// skip verifyApiKey check
$content = str_replace("verifyApiKey(\$conn, @\$_REQUEST['key']);", "", $content);
file_put_contents("c:\\projectCoding\\hr\\public_html\\sys\\psnlTotal_dev4.php", $content);
include "c:\\projectCoding\\hr\\public_html\\sys\\psnlTotal_dev4.php";
PHP;

file_put_contents("c:\\projectCoding\\hr\\public_html\\sys\\test_direct.php", $php_code);
