<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
    $psnlCd = @$_REQUEST['PSNL_CD'];
    $year = @$_REQUEST['MPAY_YEAR'];

    if (!$psnlCd || !$year) {
        jsonResponse($conn, ["data" => [], "error" => "Required parameters missing"]);
        exit;
    }

    $sqlParts = [];
    $params = [];
    $types = "";

    for ($i = 1; $i <= 12; $i++) {
        $loopMonth = substr(('0' . $i), -2);
        $monthStr = $year . "-" . $loopMonth;
        $sttDt = $monthStr . "-01";
        $endDt = $monthStr . "-31";
        $prevYearSttDt = ($year - 1) . "-" . $loopMonth . "-01";

        $sqlParts[] = "
        (SELECT 
            ? AS YEAR_MON
            ,A2.WORK_TYPE
            ,A.ADVANCE_DT
            ,A.GRD_GRADE
            ,A.GRD_PAY
            ,B.NORMAL_PAY
            ,B.LEGAL_PAY
            ,IFNULL((SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE 
                PSNL_CD = ? 
                AND ADJ_TYPE = '직책' 
                AND ADJ_STT_DT <= ? 
                AND (ADJ_END_DT >= ? OR ADJ_END_DT is null)),0) AS POSI_PAY

            ,IFNULL((SELECT SUM(FML_PAY) FROM PSNL_FAMILY WHERE PSNL_CD = ? AND FML_STT_DT <= ? AND (FML_END_DT >= ? OR FML_END_DT is null)),0) AS FML_PAY
            ,IFNULL((SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE PSNL_CD = ? AND ADJ_TYPE = '자격' AND ADJ_STT_DT <= ? AND (ADJ_END_DT >= ? OR ADJ_END_DT is null)),0) AS LCS_PAY
            ,IFNULL((SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE PSNL_CD = ? AND ADJ_TYPE = '장애' AND ADJ_STT_DT <= ? AND (ADJ_END_DT >= ? OR ADJ_END_DT is null)),0) AS DIS_PAY
            ,IFNULL((SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE PSNL_CD = ? AND ADJ_TYPE = '조정' AND ADJ_STT_DT <= ? AND (ADJ_END_DT >= ? OR ADJ_END_DT is null)),0) AS ADJ_PAY
            ,B.NORMAL_PAY+B.LEGAL_PAY+IFNULL((SELECT SUM(FML_PAY) FROM PSNL_FAMILY WHERE PSNL_CD = ? AND FML_STT_DT <= ? AND (FML_END_DT >= ? OR FML_END_DT is null)),0)+IFNULL((SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE PSNL_CD = ? AND ADJ_STT_DT <= ? AND (ADJ_END_DT >= ? OR ADJ_END_DT is null)),0) AS TOTAL_PAY
            FROM GRADE_HISTORY A
            LEFT OUTER JOIN PSNL_TRANSFER A2 ON TRS_CD = (SELECT TRS_CD FROM PSNL_TRANSFER WHERE PSNL_CD=? AND TRS_DT <= ? ORDER BY TRS_DT DESC LIMIT 1)
            LEFT OUTER JOIN SALARY_TB B ON GRD_GRADE = SLR_GRADE AND GRD_PAY = SLR_PAY AND SLR_YEAR = ? AND A2.WORK_TYPE LIKE CONCAT('%',SLR_TYPE)
        WHERE A.PSNL_CD = ?
            AND A.ADVANCE_DT > ? AND A.ADVANCE_DT <= ? LIMIT 1)";

        // 22 parameters per subquery
        array_push($params, $monthStr, $psnlCd, $endDt, $sttDt, $psnlCd, $endDt, $sttDt, $psnlCd, $endDt, $sttDt, $psnlCd, $endDt, $sttDt, $psnlCd, $endDt, $sttDt, $psnlCd, $endDt, $sttDt, $psnlCd, $endDt, $sttDt, $psnlCd, $endDt, $year, $psnlCd, $prevYearSttDt, $sttDt);
    }
    
    // Correction: Let's count properly.
    // 1:YEAR_MON, 2:PSNL_CD, 3:endDt, 4:sttDt (POSI)
    // 5:PSNL_CD, 6:endDt, 7:sttDt (FML)
    // 8:PSNL_CD, 9:endDt, 10:sttDt (LCS)
    // 11:PSNL_CD, 12:endDt, 13:sttDt (DIS)
    // 14:PSNL_CD, 15:endDt, 16:sttDt (ADJ)
    // 17:PSNL_CD, 18:endDt, 19:sttDt (SUB FML)
    // 20:PSNL_CD, 21:endDt, 22:sttDt (SUB ADJ)
    // 23:PSNL_CD, 24:endDt (A2)
    // 25:year (B)
    // 26:PSNL_CD, 27:prevYearSttDt, 28:sttDt (WHERE)
    // Total 28 markers.
    
    $types = str_repeat("s", count($params));
    $sql = implode(" UNION ", $sqlParts);

    $data = executeQuery($conn, $sql, $types, $params);
    jsonResponse($conn, ["data" => $data ?: null]);
?>