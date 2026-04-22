<?php
include "sql_safe_helper.php";
include "budget_groups.php";
verifyApiKey($conn, @$_REQUEST['key']);

// Get selected accounts from request for the interactive sum
$selectedAccounts = @$_REQUEST['ACCOUNTS'] ? explode(',', $_REQUEST['ACCOUNTS']) : [];
$accType = @$_REQUEST['ACC_TYPE'] ?: '지출';

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

// Subquery to get total budget for the year/org/type
$totalBudgetSql = "
    SELECT ORG_CD, FSC_YEAR, SUM(AMOUNT) AS TOTAL_BUDGET
    FROM BONDANG_HR.ORG_BUDGET
    WHERE ACC_TYPE = ?
    GROUP BY ORG_CD, FSC_YEAR
";

// Dynamic SQL generation for each group
$activeGroups = $BUDGET_GROUPS[$accType] ?? [];
$groupSelects = "";
$allKnownAccounts = [];

foreach ($activeGroups as $groupName => $groupAccounts) {
    if ($groupName === '기타') continue;
    if (empty($groupAccounts)) {
        $groupSelects .= ", 0 AS `G_$groupName` ";
        continue;
    }
    $allKnownAccounts = array_merge($allKnownAccounts, $groupAccounts);
    $escaped = array_map(function($a) use ($conn) { return "'" . mysqli_real_escape_string($conn, $a) . "'"; }, $groupAccounts);
    $list = implode(',', $escaped);
    $groupSelects .= ", SUM(CASE WHEN F.ACC_NM IN ($list) THEN F.AMOUNT ELSE 0 END) AS `G_$groupName` ";
}

// Handling '기타' group
if (isset($activeGroups['기타'])) {
    if (empty($allKnownAccounts)) {
        $groupSelects .= ", SUM(F.AMOUNT) AS `G_기타` ";
    } else {
        $escaped = array_map(function($a) use ($conn) { return "'" . mysqli_real_escape_string($conn, $a) . "'"; }, $allKnownAccounts);
        $list = implode(',', $escaped);
        $groupSelects .= ", SUM(CASE WHEN F.ACC_NM NOT IN ($list) THEN F.AMOUNT ELSE 0 END) AS `G_기타` ";
    }
}

// Interactive selection sum
if (!empty($selectedAccounts)) {
    $escaped = array_map(function($a) use ($conn) { return "'" . mysqli_real_escape_string($conn, $a) . "'"; }, $selectedAccounts);
    $list = implode(',', $escaped);
    $groupSelects .= ", SUM(CASE WHEN F.ACC_NM IN ($list) THEN F.AMOUNT ELSE 0 END) AS `SELECTED_AMOUNT` ";
} else {
    $groupSelects .= ", 0 AS `SELECTED_AMOUNT` ";
}

// Join condition for believer count history
$historyYear = @$_REQUEST['HISTORY_YEAR'];
$historyJoin = "F.FSC_YEAR = H.FSC_YEAR";
$historyParams = [];
$historyTypes = "";

if ($historyYear) {
    $historyJoin = "H.FSC_YEAR = ?";
    $historyParams[] = $historyYear;
    $historyTypes = "s";
}

// Main query
$sql = "
    SELECT 
        F.FSC_YEAR,
        O.ORG_NM,
        MAX(H.PERSON_CNT) AS PERSON_CNT,
        MAX(B.TOTAL_BUDGET) AS TOTAL_BUDGET,
        COALESCE(E.EMP_CNT, 0) AS EMP_CNT
        $groupSelects
    FROM BONDANG_HR.ORG_BUDGET F
    INNER JOIN BONDANG_HR.ORG_INFO O ON F.ORG_CD = O.ORG_CD
    LEFT JOIN ($historySql) H ON F.ORG_CD = H.ORG_CD AND $historyJoin
    LEFT JOIN ($empSql) E ON F.ORG_CD = E.ORG_CD
    LEFT JOIN ($totalBudgetSql) B ON F.ORG_CD = B.ORG_CD AND F.FSC_YEAR = B.FSC_YEAR
";

$whereSql = " WHERE F.ACC_TYPE = ? ";
$params = [$accType];
$types = "s";

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

$groupSql = " GROUP BY F.FSC_YEAR, O.ORG_CD ";
$orderSql = safeOrderBy(@$_REQUEST['ORDER'], ['FSC_YEAR', 'ORG_NM', 'PERSON_CNT', 'TOTAL_BUDGET']);
if (!$orderSql) $orderSql = " ORDER BY F.FSC_YEAR DESC, O.ORG_NM ASC";

$limitSql = safeLimit(@$_REQUEST['LIMIT']);

// Merge join params with where params
$allParams = array_merge([$accType], $historyParams, $params);
$allTypes = "s" . $historyTypes . $types;

// For count, we need a wrapper query because of GROUP BY
$countSql = "SELECT COUNT(*) AS ROW_CNT FROM (SELECT 1 FROM BONDANG_HR.ORG_BUDGET F INNER JOIN BONDANG_HR.ORG_INFO O ON F.ORG_CD = O.ORG_CD LEFT JOIN ($totalBudgetSql) B ON F.ORG_CD = B.ORG_CD AND F.FSC_YEAR = B.FSC_YEAR $whereSql GROUP BY F.FSC_YEAR, O.ORG_CD) AS T";

$data = executeQuery($conn, $sql . $whereSql . $groupSql . $orderSql . $limitSql, $allTypes, $allParams);

$totalSize = executeQuery($conn, "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.ORG_BUDGET");
$totalCnt = $totalSize[0]['ROW_CNT'] ?? 0;

$filterCntData = executeQuery($conn, $countSql, $allTypes, $allParams);
$filterCnt = $filterCntData[0]['ROW_CNT'] ?? 0;

jsonResponse($conn, [
    "data" => $data ?: null, 
    "totalCnt" => (int)$totalCnt, 
    "filterCnt" => (int)$filterCnt
]);
?>
