<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

$subqueryC = "SELECT 
                    T.ORG_CD,
                    SUM(CASE WHEN (T.WORK_TYPE = '정규직' OR T.WORK_TYPE = '기능직') AND (PT.PTT_HOUR >= 40 OR PT.PTT_HOUR IS NULL) AND SUBSTR(REPLACE(P.PSNL_NUM, '-', ''), 7, 1) IN ('1', '3', '5', '7', '9') THEN 1 ELSE 0 END) AS REG_MALE,
                    SUM(CASE WHEN (T.WORK_TYPE = '정규직' OR T.WORK_TYPE = '기능직') AND (PT.PTT_HOUR >= 40 OR PT.PTT_HOUR IS NULL) AND SUBSTR(REPLACE(P.PSNL_NUM, '-', ''), 7, 1) IN ('2', '4', '6', '8', '0') THEN 1 ELSE 0 END) AS REG_FEMALE,
                    SUM(CASE WHEN (T.WORK_TYPE LIKE '%계약직%' OR T.WORK_TYPE = '무기계약직') AND (PT.PTT_HOUR >= 40 OR PT.PTT_HOUR IS NULL) AND SUBSTR(REPLACE(P.PSNL_NUM, '-', ''), 7, 1) IN ('1', '3', '5', '7', '9') THEN 1 ELSE 0 END) AS CONT_MALE,
                    SUM(CASE WHEN (T.WORK_TYPE LIKE '%계약직%' OR T.WORK_TYPE = '무기계약직') AND (PT.PTT_HOUR >= 40 OR PT.PTT_HOUR IS NULL) AND SUBSTR(REPLACE(P.PSNL_NUM, '-', ''), 7, 1) IN ('2', '4', '6', '8', '0') THEN 1 ELSE 0 END) AS CONT_FEMALE,
                    SUM(CASE WHEN PT.PTT_HOUR < 40 AND SUBSTR(REPLACE(P.PSNL_NUM, '-', ''), 7, 1) IN ('1', '3', '5', '7', '9') THEN 1 ELSE 0 END) AS SHORT_MALE,
                    SUM(CASE WHEN PT.PTT_HOUR < 40 AND SUBSTR(REPLACE(P.PSNL_NUM, '-', ''), 7, 1) IN ('2', '4', '6', '8', '0') THEN 1 ELSE 0 END) AS SHORT_FEMALE
                FROM PSNL_INFO P
                JOIN PSNL_TRANSFER T ON T.TRS_CD = (
                    SELECT TRS_CD FROM PSNL_TRANSFER T2
                    WHERE T2.PSNL_CD = P.PSNL_CD
                    ORDER BY TRS_DT DESC LIMIT 1
                )
                LEFT JOIN (
                    SELECT PSNL_CD, PTT_HOUR FROM PSNL_PARTTIME P1
                    WHERE PTT_CD = (
                        SELECT MAX(PTT_CD) FROM PSNL_PARTTIME P2
                        WHERE P2.PSNL_CD = P1.PSNL_CD
                    )
                ) PT ON P.PSNL_CD = PT.PSNL_CD
                WHERE T.TRS_TYPE != '2'
                GROUP BY T.ORG_CD";

$target = @$_REQUEST['TARGET_TYPE'] ?: 'ALL';
$params = [];
$types = "";
$whereSql = "";

if ($target == 'ALL') {
    $sqlSelect = "SELECT '-' AS UPR_ORG_NM, '13061001' AS ORG_CD, '제1대리구 합계' AS ORG_NM,
                    SUM(IFNULL(C.REG_MALE, 0)) AS REG_MALE,
                    SUM(IFNULL(C.REG_FEMALE, 0)) AS REG_FEMALE,
                    SUM(IFNULL(C.CONT_MALE, 0)) AS CONT_MALE,
                    SUM(IFNULL(C.CONT_FEMALE, 0)) AS CONT_FEMALE,
                    SUM(IFNULL(C.SHORT_MALE, 0)) AS SHORT_MALE,
                    SUM(IFNULL(C.SHORT_FEMALE, 0)) AS SHORT_FEMALE
                  FROM BONDANG_HR.ORG_INFO A
                  LEFT OUTER JOIN ($subqueryC) C ON A.ORG_CD = C.ORG_CD";
    $whereSql = " WHERE (A.UPPR_ORG_CD = '13061001' OR A.UPPR_ORG_CD IN (SELECT ORG_CD FROM BONDANG_HR.ORG_INFO WHERE UPPR_ORG_CD = '13061001') OR A.ORG_CD = '13061001') ";
    $groupBy = "";
} else if ($target == 'DISTRICT') {
    $sqlSelect = "SELECT '제1대리구' AS UPR_ORG_NM, D.ORG_CD, D.ORG_NM,
                    SUM(IFNULL(C.REG_MALE, 0)) AS REG_MALE,
                    SUM(IFNULL(C.REG_FEMALE, 0)) AS REG_FEMALE,
                    SUM(IFNULL(C.CONT_MALE, 0)) AS CONT_MALE,
                    SUM(IFNULL(C.CONT_FEMALE, 0)) AS CONT_FEMALE,
                    SUM(IFNULL(C.SHORT_MALE, 0)) AS SHORT_MALE,
                    SUM(IFNULL(C.SHORT_FEMALE, 0)) AS SHORT_FEMALE
                  FROM BONDANG_HR.ORG_INFO D
                  LEFT OUTER JOIN BONDANG_HR.ORG_INFO A ON (A.UPPR_ORG_CD = D.ORG_CD OR A.ORG_CD = D.ORG_CD)
                  LEFT OUTER JOIN ($subqueryC) C ON A.ORG_CD = C.ORG_CD";
    $whereSql = " WHERE D.ORG_TYPE = '9' AND D.UPPR_ORG_CD = '13061001' ";
    $groupBy = " GROUP BY D.ORG_CD, D.ORG_NM ";
} else if ($target == 'HOLY') {
    $sqlSelect = "SELECT IFNULL(B.ORG_NM, '-') AS UPR_ORG_NM, A.ORG_CD, A.ORG_NM,
                    IFNULL(C.REG_MALE, 0) AS REG_MALE,
                    IFNULL(C.REG_FEMALE, 0) AS REG_FEMALE,
                    IFNULL(C.CONT_MALE, 0) AS CONT_MALE,
                    IFNULL(C.CONT_FEMALE, 0) AS CONT_FEMALE,
                    IFNULL(C.SHORT_MALE, 0) AS SHORT_MALE,
                    IFNULL(C.SHORT_FEMALE, 0) AS SHORT_FEMALE
                  FROM BONDANG_HR.ORG_INFO A
                  LEFT OUTER JOIN BONDANG_HR.ORG_INFO B ON A.UPPR_ORG_CD = B.ORG_CD
                  LEFT OUTER JOIN ($subqueryC) C ON A.ORG_CD = C.ORG_CD";
    $whereSql = " WHERE A.ORG_TYPE = '1' AND (A.UPPR_ORG_CD = '13061001' OR B.UPPR_ORG_CD = '13061001' OR A.ORG_CD = '13061001') ";
    $groupBy = "";
} else { // PARISH (Workplaces)
    $sqlSelect = "SELECT IFNULL(B.ORG_NM, '-') AS UPR_ORG_NM, A.ORG_CD, A.ORG_NM,
                    IFNULL(C.REG_MALE, 0) AS REG_MALE,
                    IFNULL(C.REG_FEMALE, 0) AS REG_FEMALE,
                    IFNULL(C.CONT_MALE, 0) AS CONT_MALE,
                    IFNULL(C.CONT_FEMALE, 0) AS CONT_FEMALE,
                    IFNULL(C.SHORT_MALE, 0) AS SHORT_MALE,
                    IFNULL(C.SHORT_FEMALE, 0) AS SHORT_FEMALE
                  FROM BONDANG_HR.ORG_INFO A
                  LEFT OUTER JOIN BONDANG_HR.ORG_INFO B ON A.UPPR_ORG_CD = B.ORG_CD
                  LEFT OUTER JOIN ($subqueryC) C ON A.ORG_CD = C.ORG_CD";
    $whereSql = " WHERE A.ORG_TYPE = '11' AND (A.UPPR_ORG_CD = '13061001' OR B.UPPR_ORG_CD = '13061001' OR A.ORG_CD = '13061001') ";
    $groupBy = "";

    if (@$_REQUEST['UPR_ORG']) {
        $whereSql .= " AND A.UPPR_ORG_CD = ? ";
        $params[] = $_REQUEST['UPR_ORG'];
        $types .= "s";
    }
}

$orderSql = ($target == 'ALL') ? "" : safeOrderBy(@$_REQUEST['ORDER'], ['ORG_NM' => 'A.ORG_NM']);
$limitSql = ($target == 'ALL') ? "" : safeLimit(@$_REQUEST['LIMIT']);

if ($target == 'ALL') {
    $totalCnt = 1;
} else {
    $totalSql = "SELECT COUNT(*) AS ROW_CNT FROM (" . $sqlSelect . $whereSql . $groupBy . ") AS T";
    $totalResult = executeQuery($conn, $totalSql, $types, $params);
    $totalCnt = $totalResult[0]['ROW_CNT'];
}

$data = executeQuery($conn, $sqlSelect . $whereSql . $groupBy . $orderSql . $limitSql, $types, $params);

jsonResponse($conn, [
    "data" => $data ?: [],
    "totalCnt" => $totalCnt,
    "filterCnt" => $totalCnt
]);
?>
