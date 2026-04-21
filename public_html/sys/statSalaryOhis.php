<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

// Get selected accounts from request
$accounts = @$_REQUEST['ACCOUNTS'] ? explode(',', $_REQUEST['ACCOUNTS']) : [];

// Subquery to get latest believer count of the year for each org
$historySql = "
    SELECT ORG_CD, YEAR(OH_DT) AS FSC_YEAR, MAX(PERSON_CNT) AS PERSON_CNT
    FROM BONDANG_HR.ORG_HISTORY
    GROUP BY ORG_CD, YEAR(OH_DT)
";

// Subquery to get current employee count for each org
$empSql = "
    SELECT ORG_CD, COUNT(*) AS EMP_CNT
    FROM (
        SELECT PSNL_CD, ORG_CD, TRS_TYPE
        FROM BONDANG_HR.PSNL_TRANSFER
        WHERE (PSNL_CD, TRS_DT) IN (
            SELECT PSNL_CD, MAX(TRS_DT)
            FROM BONDANG_HR.PSNL_TRANSFER
            GROUP BY PSNL_CD
        )
    ) T
    WHERE TRS_TYPE <> 2
    GROUP BY ORG_CD
";

// Main query
$sql = "
    SELECT 
        F.FSC_YEAR,
        O.ORG_NM,
        MAX(H.PERSON_CNT) AS PERSON_CNT,
        SUM(F.AMOUNT) AS TOTAL_AMOUNT,
        CASE WHEN MAX(H.PERSON_CNT) > 0 THEN ROUND(SUM(F.AMOUNT) / MAX(H.PERSON_CNT), 0) ELSE 0 END AS PER_PERSON,
        COALESCE(E.EMP_CNT, 0) AS EMP_CNT
    FROM BONDANG_HR.ORG_FINANCIAL F
    INNER JOIN BONDANG_HR.ORG_INFO O ON F.ORG_CD = O.ORG_CD
    LEFT JOIN ($historySql) H ON F.ORG_CD = H.ORG_CD AND F.FSC_YEAR = H.FSC_YEAR
    LEFT JOIN ($empSql) E ON F.ORG_CD = E.ORG_CD
";

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

if (!empty($accounts)) {
    $placeholders = implode(',', array_fill(0, count($accounts), '?'));
    $whereSql .= " AND F.ACC_NM IN ($placeholders)";
    foreach ($accounts as $acc) {
        $params[] = $acc;
        $types .= "s";
    }
} else {
    // If no accounts selected, return no data
    $whereSql .= " AND 1=0 ";
}

$groupSql = " GROUP BY F.FSC_YEAR, O.ORG_CD ";
$orderSql = safeOrderBy(@$_REQUEST['ORDER'], ['FSC_YEAR', 'ORG_NM', 'PERSON_CNT', 'TOTAL_AMOUNT', 'PER_PERSON']);
if (!$orderSql) $orderSql = " ORDER BY F.FSC_YEAR DESC, O.ORG_NM ASC";

$limitSql = safeLimit(@$_REQUEST['LIMIT']);

// For count, we need a wrapper query because of GROUP BY
$countSql = "SELECT COUNT(*) AS ROW_CNT FROM (SELECT 1 FROM BONDANG_HR.ORG_FINANCIAL F INNER JOIN BONDANG_HR.ORG_INFO O ON F.ORG_CD = O.ORG_CD $whereSql GROUP BY F.FSC_YEAR, O.ORG_CD) AS T";

$data = executeQuery($conn, $sql . $whereSql . $groupSql . $orderSql . $limitSql, $types, $params);
$totalCntResult = mysqli_query($conn, "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.ORG_FINANCIAL"); // Simplified for now
$totalCnt = mysqli_fetch_assoc($totalCntResult);

// For filterCnt, use the same logic as countSql
$filterCntData = executeQuery($conn, $countSql, $types, $params);
$filterCnt = $filterCntData[0]['ROW_CNT'] ?? 0;

jsonResponse($conn, [
    "data" => $data ?: null, 
    "totalCnt" => $totalCnt["ROW_CNT"], 
    "filterCnt" => $filterCnt
]);
?>
