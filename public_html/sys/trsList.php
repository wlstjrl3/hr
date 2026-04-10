<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

$rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_TRANSFER A LEFT OUTER JOIN ORG_INFO C ON A.ORG_CD = C.ORG_CD";
$sql = "SELECT C.ORG_NM,B.PSNL_NM,A.*,
    CASE 
    WHEN TRS_TYPE=1 THEN '입사' 
    WHEN TRS_TYPE=2 THEN '퇴사' 
    WHEN TRS_TYPE=3 THEN '전보' END AS TRS_TYPE_KOR
     FROM BONDANG_HR.PSNL_TRANSFER A
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
    LEFT OUTER JOIN ORG_INFO C ON A.ORG_CD = C.ORG_CD";

$whereSql = " WHERE 1=1";
$params = [];
$types = "";

if (@$_REQUEST['PSNL_CD']) {
    $whereSql .= " AND A.PSNL_CD=?";
    $params[] = $_REQUEST['PSNL_CD'];
    $types .= "s";
}
if (@$_REQUEST['ORG_NM']) {
    $whereSql .= " AND ORG_NM LIKE ?";
    $params[] = '%' . $_REQUEST['ORG_NM'] . '%';
    $types .= "s";
}
if (@$_REQUEST['TRS_TYPE']) {
    $whereSql .= " AND TRS_TYPE = ?";
    $params[] = $_REQUEST['TRS_TYPE'];
    $types .= "s";
}
if (@$_REQUEST['TRS_DTL']) {
    $whereSql .= " AND TRS_DTL LIKE ?";
    $params[] = '%' . $_REQUEST['TRS_DTL'] . '%';
    $types .= "s";
}
if (@$_REQUEST['TRS_DT_From']) {
    $whereSql .= " AND TRS_DT >= ?";
    $params[] = $_REQUEST['TRS_DT_From'];
    $types .= "s";
}
if (@$_REQUEST['TRS_DT_To']) {
    $whereSql .= " AND TRS_DT <= ?";
    $params[] = $_REQUEST['TRS_DT_To'];
    $types .= "s";
}

$allowedColumns = ['PSNL_CD', 'ORG_NM', 'TRS_TYPE', 'TRS_DTL', 'TRS_DT', 'BNF_DT', 'REG_DT', 'PSNL_NM', 'TRS_CD', 'POSITION', 'WORK_TYPE', 'APP_DT'];
$orderSql = safeOrderBy(@$_REQUEST['ORDER'], $allowedColumns);
$limitSql = safeLimit(@$_REQUEST['LIMIT']);

$totalCnt = mysqli_fetch_assoc(mysqli_query($conn, $rowCntSql));
$filterResult = executeQuery($conn, $rowCntSql . $whereSql, $types, $params);
$filterCnt = $filterResult[0];
$data = executeQuery($conn, $sql . $whereSql . $orderSql . $limitSql, $types, $params);
jsonResponse($conn, ["data" => $data ?: null, "totalCnt" => $totalCnt["ROW_CNT"], "filterCnt" => $filterCnt["ROW_CNT"]]);

?>