<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

// Get unique account names from ORG_FINANCIAL for selection UI
$sql = "SELECT DISTINCT ACC_NM FROM BONDANG_HR.ORG_FINANCIAL ORDER BY ACC_NM ASC";
$data = executeQuery($conn, $sql);

jsonResponse($conn, ["data" => $data]);
?>
