<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

$baseFrom = " FROM BONDANG_HR.PSNL_FAMILY A
        LEFT OUTER JOIN BONDANG_HR.PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(TRS_CD ORDER BY TRS_DT DESC, TRS_CD DESC), ',', 1) AS MAX_TRS_CD
            FROM BONDANG_HR.PSNL_TRANSFER
            GROUP BY PSNL_CD
        ) P_SUB ON P_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN BONDANG_HR.PSNL_TRANSFER C ON C.TRS_CD = P_SUB.MAX_TRS_CD
        LEFT OUTER JOIN BONDANG_HR.ORG_INFO D ON C.ORG_CD = D.ORG_CD
        LEFT OUTER JOIN (
            SELECT FML_CD, 
                   COUNT(ISSUE_CD) AS SUPPORT_CNT, 
                   SUM(ISSUE_AMT) AS TOTAL_AMT, 
                   MIN(SCHOOL_GRADE) AS START_GRADE,
                   CONCAT('[', GROUP_CONCAT(
                        JSON_OBJECT(
                            'ISSUE_CD', ISSUE_CD,
                            'ISSUE_NO', IFNULL(ISSUE_NO, ''),
                            'ISSUE_DT', ISSUE_DT,
                            'ISSUE_AMT', ISSUE_AMT,
                            'SCHOOL_GRADE', IFNULL(SCHOOL_GRADE, ''),
                            'MEMO', IFNULL(MEMO, '')
                        ) ORDER BY ISSUE_DT ASC
                   ), ']') AS ISSUE_DETAILS
            FROM BONDANG_HR.TB_TUITION_ISSUE
            GROUP BY FML_CD
        ) T ON T.FML_CD = A.FML_CD ";

$rowCntSql = "SELECT COUNT(*) AS ROW_CNT " . $baseFrom;

$sql = "SELECT 
            A.FML_CD, A.PSNL_CD, A.FML_NM, A.FML_BIRTH, 
            B.PSNL_NM, C.APP_DT, C.TRS_DT, D.ORG_NM,
            IFNULL(T.SUPPORT_CNT, 0) AS SUPPORT_CNT,
            (8 - IFNULL(T.SUPPORT_CNT, 0)) AS REMAIN_CNT,
            IFNULL(T.TOTAL_AMT, 0) AS TOTAL_AMT,
            T.START_GRADE,
            T.ISSUE_DETAILS " . $baseFrom;

$whereSql = " WHERE A.FML_RELATION LIKE '%자녀%' ";
$params = [];
$types = "";
$hasFilter = false;

if (@$_REQUEST['FML_CD']) {
    $whereSql .= " AND A.FML_CD = ?";
    $params[] = $_REQUEST['FML_CD'];
    $types .= "i";
    $hasFilter = true;
} else {
    if (@$_REQUEST['PSNL_NM']) {
        $whereSql .= " AND B.PSNL_NM LIKE ?";
        $params[] = '%' . $_REQUEST['PSNL_NM'] . '%';
        $types .= "s";
        $hasFilter = true;
    }
    if (@$_REQUEST['PSNL_CD']) {
        $whereSql .= " AND A.PSNL_CD = ?";
        $params[] = $_REQUEST['PSNL_CD'];
        $types .= "s";
        $hasFilter = true;
    }
    if (@$_REQUEST['ORG_NM']) {
        $whereSql .= " AND D.ORG_NM LIKE ?";
        $params[] = '%' . $_REQUEST['ORG_NM'] . '%';
        $types .= "s";
        $hasFilter = true;
    }
    if (@$_REQUEST['FML_NM']) {
        $whereSql .= " AND A.FML_NM LIKE ?";
        $params[] = '%' . $_REQUEST['FML_NM'] . '%';
        $types .= "s";
        $hasFilter = true;
    }
    
    // 필터 조건이 없으면 지급 이력이 있는 대상만 노출
    if (!$hasFilter) {
        $whereSql .= " AND T.SUPPORT_CNT > 0 ";
    }
}

$orderSql = safeOrderBy(@$_REQUEST['ORDER'], []);
$limitSql = safeLimit(@$_REQUEST['LIMIT']);

$totalCntResult = mysqli_query($conn, "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_FAMILY WHERE FML_RELATION LIKE '%자녀%'");
$totalCnt = mysqli_fetch_assoc($totalCntResult);

$filterResult = executeQuery($conn, $rowCntSql . $whereSql, $types, $params);
$filterCnt = $filterResult[0];

$data = executeQuery($conn, $sql . $whereSql . $orderSql . $limitSql, $types, $params);

// Parse JSON details
if ($data) {
    foreach ($data as &$row) {
        if ($row['ISSUE_DETAILS']) {
            $row['ISSUE_DETAILS'] = json_decode($row['ISSUE_DETAILS'], true);
        } else {
            $row['ISSUE_DETAILS'] = [];
        }
    }
}

jsonResponse($conn, ["data" => $data ?: null, "totalCnt" => $totalCnt["ROW_CNT"], "filterCnt" => $filterCnt["ROW_CNT"]]);
?>
