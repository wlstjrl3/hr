<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

//갯수 카운트 쿼리
$rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_INSURANCE A";
//기본 쿼리
$sql = "SELECT C.ORG_NM,B.PSNL_NM,A.*,P.POSITION FROM BONDANG_HR.PSNL_INSURANCE A 
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
    LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
        SELECT TRS_CD FROM PSNL_TRANSFER AS P2
        WHERE P2.PSNL_CD = A.PSNL_CD
        ORDER BY P2.REG_DT DESC
        LIMIT 1
    )
    LEFT OUTER JOIN ORG_INFO C ON P.ORG_CD = C.ORG_CD
    ";

//조건문 지정
$whereSql = " WHERE 1=1";
$params = [];
$types = "";

if (@$_REQUEST['PSNL_CD']) {
    $whereSql .= " AND A.PSNL_CD=?";
    $params[] = $_REQUEST['PSNL_CD'];
    $types .= "s";
}
if (@$_REQUEST['INS_AMOUNT_From']) {
    $whereSql .= " AND INS_AMOUNT >= ?";
    $params[] = $_REQUEST['INS_AMOUNT_From'];
    $types .= "s";
}
if (@$_REQUEST['INS_AMOUNT_To']) {
    $whereSql .= " AND INS_AMOUNT <= ?";
    $params[] = $_REQUEST['INS_AMOUNT_To'];
    $types .= "s";
}
if (@$_REQUEST['INS_STT_DT_From']) {
    $whereSql .= " AND INS_STT_DT >= ?";
    $params[] = $_REQUEST['INS_STT_DT_From'];
    $types .= "s";
}
if (@$_REQUEST['INS_STT_DT_To']) {
    $whereSql .= " AND INS_STT_DT <= ?";
    $params[] = $_REQUEST['INS_STT_DT_To'];
    $types .= "s";
}
if (@$_REQUEST['INS_END_DT_From']) {
    $whereSql .= " AND INS_END_DT >= ?";
    $params[] = $_REQUEST['INS_END_DT_From'];
    $types .= "s";
}
if (@$_REQUEST['INS_END_DT_To']) {
    $whereSql .= " AND INS_END_DT <= ?";
    $params[] = $_REQUEST['INS_END_DT_To'];
    $types .= "s";
}
if (@$_REQUEST['INS_DTL']) {
    $whereSql .= " AND INS_DTL LIKE ?";
    $params[] = '%' . $_REQUEST['INS_DTL'] . '%';
    $types .= "s";
}
//정렬 기준 지정 - 화이트리스트
$allowedColumns = ['PSNL_CD', 'INS_AMOUNT', 'INS_STT_DT', 'INS_END_DT', 'INS_DTL', 'REG_DT', 'PSNL_NM', 'ORG_NM', 'INS_CD', 'POSITION'];
$orderSql = safeOrderBy(@$_REQUEST['ORDER'], $allowedColumns);
//리미트 지정
$limitSql = safeLimit(@$_REQUEST['LIMIT']);

$totalCnt = mysqli_fetch_assoc(mysqli_query($conn, $rowCntSql));

$filterResult = executeQuery($conn, $rowCntSql . $whereSql, $types, $params);
$filterCnt = $filterResult[0];

$data = executeQuery($conn, $sql . $whereSql . $orderSql . $limitSql, $types, $params);

jsonResponse($conn, [
    "data" => $data ?: null,
    "totalCnt" => $totalCnt["ROW_CNT"],
    "filterCnt" => $filterCnt["ROW_CNT"]
]);

?>