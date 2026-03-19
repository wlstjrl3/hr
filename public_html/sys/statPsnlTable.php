<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

$baseDate = @$_REQUEST['STAT_BASE_DATE'] ?: date('Y-m-d');
$includeDomestic = @$_REQUEST['INCLUDE_DOMESTIC'] == 'true' ? true : false;

$wherePos = "";
if (!$includeDomestic) {
    $wherePos = " AND C.POSITION != '가사사용인' ";
}

$sortOrder = @$_REQUEST['SORT_ORDER'] ?: 'NAME';

$orderClause = "A.PSNL_NM ASC";
if ($sortOrder == 'POSITION') {
    $orderClause = "CASE 
        WHEN C.POSITION = '사무장' THEN 1
        WHEN C.POSITION = '사무원' THEN 2
        WHEN C.POSITION = '관리장' THEN 3
        WHEN C.POSITION = '관리원' THEN 4
        WHEN C.POSITION = '가사사용인' THEN 5
        ELSE 6
    END ASC, A.PSNL_NM ASC";
}

// 기본 쿼리: 기준일 현재 재직(1) 및 전보(3) 상태인 인원 조회
$sql = "SELECT 
            DISTINCT A.PSNL_CD,
            A.PSNL_NM,
            A.BAPT_NM,
            C.WORK_TYPE,
            C.POSITION,
            B.ORG_NM,
            B.ORG_CD,
            B.ORG_IN_TEL,
            B.ORG_OUT_TEL,
            D.ORG_NM AS UPPR_ORG_NM,
            D.ORG_CD AS UPPR_ORG_CD
        FROM PSNL_INFO A
        JOIN PSNL_TRANSFER C ON C.TRS_CD = (
            SELECT TRS_CD FROM PSNL_TRANSFER T2
            WHERE T2.PSNL_CD = A.PSNL_CD
            AND TRS_DT <= '{$baseDate}'
            ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1
        )
        LEFT OUTER JOIN ORG_INFO B ON C.ORG_CD = B.ORG_CD
        LEFT OUTER JOIN ORG_INFO D ON B.UPPR_ORG_CD = D.ORG_CD
        WHERE (C.TRS_TYPE IN ('1', '3') OR (C.TRS_TYPE = '2' AND C.TRS_DT = '{$baseDate}')) {$wherePos}
        ORDER BY D.ORG_NM ASC, B.ORG_NM ASC, {$orderClause}";

$data = executeQuery($conn, $sql, "", []);

jsonResponse($conn, ["data" => $data ?: null]);
?>
