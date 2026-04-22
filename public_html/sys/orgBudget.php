<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

// 갯수 카운트 쿼리
$rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.ORG_BUDGET F JOIN BONDANG_HR.ORG_INFO O ON F.ORG_CD = O.ORG_CD";

// 기본 쿼리
$sql = "SELECT CONCAT(F.FSC_YEAR, '|', F.ORG_CD, '|', F.ACC_NM, '|', F.ACC_TYPE) AS UID, F.FSC_YEAR, F.ORG_CD, O.ORG_NM, F.ACC_NM, F.ACC_TYPE, F.AMOUNT
        FROM BONDANG_HR.ORG_BUDGET F
        JOIN BONDANG_HR.ORG_INFO O ON F.ORG_CD = O.ORG_CD";

$whereSql = " WHERE 1=1 ";
$params = [];
$types = "";

if (@$_REQUEST['FSC_YEAR']) {
    $whereSql .= " AND F.FSC_YEAR = ?";
    $params[] = $_REQUEST['FSC_YEAR'];
    $types .= "s";
}
if (@$_REQUEST['ORG_NM']) {
    $whereSql .= " AND O.ORG_NM LIKE ?";
    $params[] = '%' . $_REQUEST['ORG_NM'] . '%';
    $types .= "s";
}
if (@$_REQUEST['ACC_NM']) {
    $whereSql .= " AND F.ACC_NM LIKE ?";
    $params[] = '%' . $_REQUEST['ACC_NM'] . '%';
    $types .= "s";
}
if (@$_REQUEST['ACC_TYPE']) {
    $whereSql .= " AND F.ACC_TYPE = ?";
    $params[] = $_REQUEST['ACC_TYPE'];
    $types .= "s";
}

// 정렬 기준 지정
$orderSql = safeOrderBy(@$_REQUEST['ORDER'], ['FSC_YEAR', 'ORG_CD', 'ORG_NM', 'ACC_NM', 'ACC_TYPE', 'AMOUNT']);
if (!$orderSql) $orderSql = " ORDER BY F.FSC_YEAR DESC, O.ORG_NM ASC";

// 리미트 지정
$limitSql = safeLimit(@$_REQUEST['LIMIT']);

$totalCntResult = mysqli_query($conn, $rowCntSql);
if (!$totalCntResult) {
    jsonResponse($conn, ["error" => true, "message" => "Total count query failed: " . mysqli_error($conn)]);
    exit;
}
$totalCnt = mysqli_fetch_assoc($totalCntResult);

try {
    $filterCntResult = executeQuery($conn, $rowCntSql . $whereSql, $types, $params);
    $filterCnt = $filterCntResult[0]['ROW_CNT'] ?? 0;

    $data = executeQuery($conn, $sql . $whereSql . $orderSql . $limitSql, $types, $params);

    jsonResponse($conn, [
        "data" => $data ?: null, 
        "totalCnt" => $totalCnt["ROW_CNT"] ?? 0, 
        "filterCnt" => $filterCnt
    ]);
} catch (Exception $e) {
    jsonResponse($conn, ["error" => true, "message" => "Query execution failed: " . $e->getMessage()]);
}
?>
