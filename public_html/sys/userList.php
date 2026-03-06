<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

$rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.USER_TB";
$sql = "SELECT * FROM BONDANG_HR.USER_TB";

$whereSql = " WHERE 1=1 ";
$params = [];
$types = "";

if (@$_REQUEST['USER_ID']) {
    $whereSql .= " AND USER_ID LIKE ?";
    $params[] = '%' . $_REQUEST['USER_ID'] . '%';
    $types .= "s";
}
if (@$_REQUEST['USER_NM']) {
    $whereSql .= " AND USER_NM LIKE ?";
    $params[] = '%' . $_REQUEST['USER_NM'] . '%';
    $types .= "s";
}
if (@$_REQUEST['USER_PASS']) {
    $whereSql .= " AND USER_PASS LIKE ?";
    $params[] = '%' . $_REQUEST['USER_PASS'] . '%';
    $types .= "s";
}
if (@$_REQUEST['EMAIL']) {
    $whereSql .= " AND EMAIL LIKE ?";
    $params[] = '%' . $_REQUEST['EMAIL'] . '%';
    $types .= "s";
}
if (@$_REQUEST['MEMO']) {
    $whereSql .= " AND MEMO LIKE ?";
    $params[] = '%' . $_REQUEST['MEMO'] . '%';
    $types .= "s";
}
if (@$_REQUEST['REG_DT_From']) {
    $whereSql .= " AND REG_DT >= ?";
    $params[] = $_REQUEST['REG_DT_From'] . " 00:00:00";
    $types .= "s";
}
if (@$_REQUEST['REG_DT_To']) {
    $whereSql .= " AND REG_DT <= ?";
    $params[] = $_REQUEST['REG_DT_To'] . " 23:59:59";
    $types .= "s";
}

$allowedColumns = ['USER_CD', 'USER_ID', 'USER_NM', 'USER_PASS', 'USER_AUTH', 'EMAIL', 'POSITION', 'ORG_NM', 'REG_DT', 'MEMO'];
$orderSql = safeOrderBy(@$_REQUEST['ORDER'], $allowedColumns);
$limitSql = safeLimit(@$_REQUEST['LIMIT']);

$totalCnt = mysqli_fetch_assoc(mysqli_query($conn, $rowCntSql));
$filterResult = executeQuery($conn, $rowCntSql . $whereSql, $types, $params);
$filterCnt = $filterResult[0];
$data = executeQuery($conn, $sql . $whereSql . $orderSql . $limitSql, $types, $params);
jsonResponse($conn, ["data" => $data ?: null, "totalCnt" => $totalCnt["ROW_CNT"], "filterCnt" => $filterCnt["ROW_CNT"]]);

?>