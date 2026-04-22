<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

$type = $_REQUEST['type'] ?? '';
$org_nm = $_REQUEST['ORG_NM'] ?? '';
$fsc_year = $_REQUEST['FSC_YEAR'] ?? '';
$accounts = @$_REQUEST['ACCOUNTS'] ? explode(',', $_REQUEST['ACCOUNTS']) : [];

try {
    if ($type === 'emp') {
        // Get list of names for current active employees in the organization
        $sql = "
            SELECT A.PSNL_NM, A.PSNL_CD
            FROM BONDANG_HR.PSNL_INFO A
            INNER JOIN (
                SELECT PSNL_CD, ORG_CD, TRS_TYPE, TRS_DT
                FROM BONDANG_HR.PSNL_TRANSFER
                WHERE (PSNL_CD, TRS_DT) IN (
                    SELECT PSNL_CD, MAX(TRS_DT)
                    FROM BONDANG_HR.PSNL_TRANSFER
                    GROUP BY PSNL_CD
                )
            ) C ON A.PSNL_CD = C.PSNL_CD
            INNER JOIN BONDANG_HR.ORG_INFO B ON C.ORG_CD = B.ORG_CD
            WHERE B.ORG_NM = ? AND C.TRS_TYPE <> 2
            ORDER BY A.PSNL_NM ASC
        ";
        $data = executeQuery($conn, $sql, "s", [$org_nm]);
        jsonResponse($conn, ["data" => $data]);

    } else if ($type === 'history') {
        // Get the specific date of the believer count record
        $sql = "
            SELECT DATE_FORMAT(MAX(OH_DT), '%Y-%m-%d') AS OH_DT, MAX(PERSON_CNT) AS PERSON_CNT
            FROM BONDANG_HR.ORG_HISTORY H
            INNER JOIN BONDANG_HR.ORG_INFO O ON H.ORG_CD = O.ORG_CD
            WHERE O.ORG_NM = ? AND YEAR(H.OH_DT) = ?
        ";
        $data = executeQuery($conn, $sql, "ss", [$org_nm, $fsc_year]);
        jsonResponse($conn, ["data" => $data]);

    } else if ($type === 'budget') {
        // Get account-level breakdown
        if (empty($accounts)) {
            jsonResponse($conn, ["data" => []]);
        }
        
        $placeholders = implode(',', array_fill(0, count($accounts), '?'));
        $sql = "
            SELECT F.ACC_NM, F.AMOUNT
            FROM BONDANG_HR.ORG_BUDGET F
            INNER JOIN BONDANG_HR.ORG_INFO O ON F.ORG_CD = O.ORG_CD
            WHERE O.ORG_NM = ? AND F.FSC_YEAR = ? AND F.ACC_NM IN ($placeholders)
            ORDER BY F.AMOUNT DESC
        ";
        
        $params = array_merge([$org_nm, $fsc_year], $accounts);
        $types = "ss" . str_repeat("s", count($accounts));
        
        $data = executeQuery($conn, $sql, $types, $params);
        jsonResponse($conn, ["data" => $data]);

    } else {
        jsonResponse($conn, ["error" => true, "message" => "Invalid type"]);
    }
} catch (Exception $e) {
    jsonResponse($conn, ["error" => true, "message" => "Detail query failed: " . $e->getMessage()]);
}
?>
