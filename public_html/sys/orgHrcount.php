<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

//갯수 카운트 쿼리
$rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.ORG_INFO A 
LEFT OUTER JOIN BONDANG_HR.ORG_INFO B ON A.UPPR_ORG_CD = B.ORG_CD
LEFT OUTER JOIN (
    SELECT H.ORG_CD, H.PERSON_CNT 
    FROM BONDANG_HR.ORG_HISTORY H
    JOIN (SELECT ORG_CD, MAX(OH_DT) AS MAX_DT FROM BONDANG_HR.ORG_HISTORY GROUP BY ORG_CD) H2
    ON H.ORG_CD = H2.ORG_CD AND H.OH_DT = H2.MAX_DT
) OH ON A.ORG_CD = OH.ORG_CD";

//기본 쿼리
$sql = "SELECT B.ORG_NM AS UPR_ORG_NM
        ,A.ORG_CD
        ,A.ORG_NM
        ,IFNULL(C.REGULAR_CNT, 0) AS REGULAR_CNT
        ,IFNULL(C.FUNC_CNT, 0) AS FUNC_CNT
        ,IFNULL(C.CONT_CNT, 0) AS CONT_CNT
        ,(IFNULL(C.REGULAR_CNT, 0) + IFNULL(C.FUNC_CNT, 0) + IFNULL(C.CONT_CNT, 0)) AS TOTAL_CNT
        ,IFNULL(OH.PERSON_CNT, 0) AS PERSON_CNT
        FROM BONDANG_HR.ORG_INFO A 
        LEFT OUTER JOIN BONDANG_HR.ORG_INFO B ON A.UPPR_ORG_CD = B.ORG_CD
        LEFT OUTER JOIN (
            SELECT H.ORG_CD, H.PERSON_CNT 
            FROM BONDANG_HR.ORG_HISTORY H
            JOIN (SELECT ORG_CD, MAX(OH_DT) AS MAX_DT FROM BONDANG_HR.ORG_HISTORY GROUP BY ORG_CD) H2
            ON H.ORG_CD = H2.ORG_CD AND H.OH_DT = H2.MAX_DT
        ) OH ON A.ORG_CD = OH.ORG_CD
        LEFT OUTER JOIN (
            SELECT 
                T.ORG_CD,
                SUM(CASE WHEN T.WORK_TYPE = '정규직' THEN 1 ELSE 0 END) AS REGULAR_CNT,
                SUM(CASE WHEN T.WORK_TYPE = '기능직' THEN 1 ELSE 0 END) AS FUNC_CNT,
                SUM(CASE WHEN T.WORK_TYPE LIKE '%계약직%' THEN 1 ELSE 0 END) AS CONT_CNT
            FROM PSNL_INFO P
            JOIN PSNL_TRANSFER T ON T.TRS_CD = (
                SELECT TRS_CD FROM PSNL_TRANSFER T2
                WHERE T2.PSNL_CD = P.PSNL_CD
                ORDER BY TRS_DT DESC LIMIT 1
            )
            WHERE T.TRS_TYPE != '2'
            GROUP BY T.ORG_CD
        ) C ON A.ORG_CD = C.ORG_CD
        ";

//조건문 지정 (조직구분 본당 고정)
$whereSql = " WHERE A.ORG_TYPE = '11' ";
$params = [];
$types = "";

if (@$_REQUEST['UUPR_ORG']) {
    $whereSql = $whereSql . " AND (B.UPPR_ORG_CD = '" . $_REQUEST['UUPR_ORG'] . "' OR A.UPPR_ORG_CD = '" . $_REQUEST['UUPR_ORG'] . "')";
}
if (@$_REQUEST['UPR_ORG']) {
    $whereSql .= " AND A.UPPR_ORG_CD = ?";
    $params[] = $_REQUEST['UPR_ORG'];
    $types .= "s";
}
if (@$_REQUEST['ORG_NM']) {
    $whereSql .= " AND A.ORG_NM LIKE ?";
    $params[] = '%' . $_REQUEST['ORG_NM'] . '%';
    $types .= "s";
}
if (@$_REQUEST['PERSON_CNT_From']) {
    $whereSql .= " AND IFNULL(OH.PERSON_CNT, 0) >= ?";
    $params[] = $_REQUEST['PERSON_CNT_From'];
    $types .= "s";
}
if (@$_REQUEST['PERSON_CNT_To']) {
    $whereSql .= " AND IFNULL(OH.PERSON_CNT, 0) <= ?";
    $params[] = $_REQUEST['PERSON_CNT_To'];
    $types .= "s";
}


//정렬 기준 지정
$orderSql = safeOrderBy(@$_REQUEST['ORDER'], []);
//리미트 지정
$limitSql = safeLimit(@$_REQUEST['LIMIT']);

$totalCntQuery = executeQuery($conn, $rowCntSql . " WHERE A.ORG_TYPE = '11' ", "", []);
$totalCnt = $totalCntQuery[0];

$filterResult = executeQuery($conn, $rowCntSql . $whereSql, $types, $params);
$filterCnt = $filterResult[0];
$data = executeQuery($conn, $sql . $whereSql . $orderSql . $limitSql, $types, $params);
jsonResponse($conn, ["data" => $data ?: null, "totalCnt" => $totalCnt["ROW_CNT"], "filterCnt" => $filterCnt["ROW_CNT"]]);

?>
