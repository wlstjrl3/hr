<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../dbconn/dbconn.php';
include __DIR__ . '/sql_safe_helper.php';

// Prepare variables as if psnlTotal ran them
$baseYearEsc = '2024';
$trsCond = " AND LEFT(TRS_DT, 4) <= '2024' ";
$pttCond = " WHERE PTT_YEAR <= '2024' ";
$grdCond = " WHERE LEFT(ADVANCE_DT, 4) <= '2024' ";

$rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM PSNL_INFO A 
        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(TRS_CD ORDER BY TRS_DT DESC, TRS_CD DESC), ',', 1) AS MAX_TRS_CD
            FROM PSNL_TRANSFER
            WHERE TRS_TYPE IN (1,2) {$trsCond}
            GROUP BY PSNL_CD
        ) C_SUB ON C_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER C ON C.TRS_CD = C_SUB.MAX_TRS_CD
        
        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(TRS_CD ORDER BY TRS_DT DESC, TRS_CD DESC), ',', 1) AS MAX_TRS_CD
            FROM PSNL_TRANSFER
            WHERE 1=1 {$trsCond}
            GROUP BY PSNL_CD
        ) C2_SUB ON C2_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER C2 ON C2.TRS_CD = C2_SUB.MAX_TRS_CD
        LEFT OUTER JOIN ORG_INFO B ON C2.ORG_CD = B.ORG_CD   
        ";

// STAT_MODE injection
    $rowCntSql .= " LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(PTT_CD ORDER BY PTT_YEAR DESC, PTT_CD DESC), ',', 1) AS MAX_PTT_CD
            FROM PSNL_PARTTIME
            {$pttCond}
            GROUP BY PSNL_CD
        ) PTT_SUB ON PTT_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN PSNL_PARTTIME PTT ON PTT.PTT_CD = PTT_SUB.MAX_PTT_CD 
        
        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(GRD_CD ORDER BY ADVANCE_DT DESC, GRD_CD DESC), ',', 1) AS MAX_GRD_CD
            FROM GRADE_HISTORY
            {$grdCond}
            GROUP BY PSNL_CD
        ) D_SUB ON D_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN GRADE_HISTORY D ON D.GRD_CD = D_SUB.MAX_GRD_CD ";

$whereSql = " WHERE 1=1 AND C2.TRS_TYPE != '2' AND NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000') = ?";
$params = ['2024'];
$types = "s";

try {
    $res = executeQuery($conn, $rowCntSql . $whereSql, $types, $params);
    echo "ROW CNT 1 SUCCESS\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
