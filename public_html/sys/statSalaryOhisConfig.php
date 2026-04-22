<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
include "budget_groups.php";

// Get unique account names from ORG_BUDGET for selection UI
$accType = @$_REQUEST['ACC_TYPE'] ?: '지출';
$sql = "SELECT DISTINCT ACC_NM FROM BONDANG_HR.ORG_BUDGET WHERE ACC_TYPE = ? ORDER BY ACC_NM ASC";
$data = executeQuery($conn, $sql, "s", [$accType]);

jsonResponse($conn, ["data" => $data, "groups" => $BUDGET_GROUPS]);
?>
