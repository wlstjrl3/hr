<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// We'll simulate reading from the DB using test_psnlTotal_dev5.php
$php_code = <<<'PHP'
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mock the request that breaks everything
$_REQUEST = [
    'STAT_MODE' => '1',
    'STAT_TARGET' => 'ALL',
    'STAT_BASE_YEAR' => '2024',
    'key' => 'mocked',
    'LIMIT' => 10,
    'START' => 0
];

require_once "c:\\projectCoding\\hr\\public_html\\dbconn\\dbconn.php";
require_once "c:\\projectCoding\\hr\\public_html\\sys\\sql_safe_helper.php";

// Define a copy of psnlTotal
$content = file_get_contents("c:\\projectCoding\\hr\\public_html\\sys\\psnlTotal.php");
// Strip verifyApiKey
$content = str_replace("verifyApiKey(\$conn, @\$_REQUEST['key']);", "", $content);

// Wrap mysqli_query in a try-catch or just print the query to debug
$content = str_replace(
    '$totalCnt = mysqli_fetch_assoc(mysqli_query($conn, $rowCntSql));',
    "\$res = mysqli_query(\$conn, \$rowCntSql); if(!\$res) { echo 'SQL ERROR ROW_CNT: ' . mysqli_error(\$conn) . \"\\nQUERY: \$rowCntSql\\n\"; exit; } \$totalCnt = mysqli_fetch_assoc(\$res);",
    $content
);

$content = str_replace(
    'executeQuery($conn, $rowCntSql . $whereSql, $types, $params);',
    "executeQuery(\$conn, \$rowCntSql . \$whereSql, \$types, \$params); if(!\$filterResult) {} // handled by safe_helper",
    $content
);

file_put_contents("c:\\projectCoding\\hr\\public_html\\sys\\psnlTotal_dev5.php", $content);
include "c:\\projectCoding\\hr\\public_html\\sys\\psnlTotal_dev5.php";
PHP;

file_put_contents("c:\\projectCoding\\hr\\public_html\\sys\\test_direct5.php", $php_code);
