<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

// 기본 카운트 및 조회 쿼리 (직원 정보와 조인)
$rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM TB_CERT_PRINT A LEFT JOIN PSNL_INFO B ON A.EMP_NO = B.PSNL_CD";
$sql = "SELECT 
            A.*, 
            B.PSNL_NM,
            (SELECT ORG_NM FROM ORG_INFO WHERE ORG_CD = (SELECT ORG_CD FROM PSNL_TRANSFER WHERE PSNL_CD = A.EMP_NO ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1)) AS ORG_NM,
            (SELECT POSITION FROM PSNL_TRANSFER WHERE PSNL_CD = A.EMP_NO ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1) AS POSITION
        FROM TB_CERT_PRINT A
        LEFT JOIN PSNL_INFO B ON A.EMP_NO = B.PSNL_CD";

$whereSql = " WHERE 1=1 ";
$params = [];
$types = "";

// 필터링 조건 처리
if (@$_REQUEST['EMP_NM']) {
    $whereSql .= " AND B.PSNL_NM LIKE ?";
    $params[] = '%' . $_REQUEST['EMP_NM'] . '%';
    $types .= "s";
}
if (@$_REQUEST['CERT_TYPE']) {
    $whereSql .= " AND A.CERT_TYPE = ?";
    $params[] = $_REQUEST['CERT_TYPE'];
    $types .= "s";
}
if (@$_REQUEST['ISSUE_NO']) {
    $whereSql .= " AND A.ISSUE_NO LIKE ?";
    $params[] = '%' . $_REQUEST['ISSUE_NO'] . '%';
    $types .= "s";
}
if (@$_REQUEST['ISSUE_DT_STT']) {
    $whereSql .= " AND A.ISSUE_DT >= ?";
    $params[] = $_REQUEST['ISSUE_DT_STT'];
    $types .= "s";
}
if (@$_REQUEST['ISSUE_DT_END']) {
    $whereSql .= " AND A.ISSUE_DT <= ?";
    $params[] = $_REQUEST['ISSUE_DT_END'];
    $types .= "s";
}

$allowedColumns = ['ISSUE_NO', 'EMP_NO', 'CERT_TYPE', 'ISSUE_DT', 'PSNL_NM', 'ORG_NM'];
$orderSql = safeOrderBy(@$_REQUEST['ORDER'], $allowedColumns);
if (!$orderSql) $orderSql = " ORDER BY A.ISSUE_DT DESC, A.ISSUE_NO DESC ";

$limitSql = safeLimit(@$_REQUEST['LIMIT']);

$totalResult = executeQuery($conn, $rowCntSql, "", []);
$totalCnt = $totalResult[0]['ROW_CNT'] ?? 0;

$filterResult = executeQuery($conn, $rowCntSql . $whereSql, $types, $params);
$filterCnt = $filterResult[0]['ROW_CNT'] ?? 0;

$data = executeQuery($conn, $sql . $whereSql . $orderSql . $limitSql, $types, $params);

jsonResponse($conn, [
    "data" => $data ?: null, 
    "totalCnt" => (int)$totalCnt, 
    "filterCnt" => (int)$filterCnt
]);
?>
