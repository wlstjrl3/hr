<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
    $psnlCd = @$_REQUEST['PSNL_CD'];
    $year = (int)(@$_REQUEST['MPAY_YEAR'] ?? date('Y'));

    if (!$psnlCd || !$year) {
        jsonResponse($conn, ["data" => [], "error" => "Required parameters missing"]);
        exit;
    }

    $sqlParts = [];
    $params = [];
    $types = "";

    for ($i = 1; $i <= 12; $i++) {
        $loopMonth = sprintf('%02d', $i);
        $monthStr = $year . "-" . $loopMonth;
        $sttDt = $monthStr . "-01";
        // 해당 월의 마지막 날짜 계산
        $lastDay = date("t", strtotime($sttDt));
        $endDt = $monthStr . "-" . $lastDay;
        
        // 작년 동일 시점 (비교용 또는 검색 범위 제한용)
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

        // Total 28 markers per subquery
        array_push($params, 
            $monthStr, // 1
            $psnlCd, $endDt, $sttDt, // 2, 3, 4 (POSI)
            $psnlCd, $endDt, $sttDt, // 5, 6, 7 (FML)
            $psnlCd, $endDt, $sttDt, // 8, 9, 10 (LCS)
            $psnlCd, $endDt, $sttDt, // 11, 12, 13 (DIS)
            $psnlCd, $endDt, $sttDt, // 14, 15, 16 (ADJ)
            $psnlCd, $endDt, $sttDt, // 17, 18, 19 (TOTAL FML)
            $psnlCd, $endDt, $sttDt, // 20, 21, 22 (TOTAL ADJ)
            $psnlCd, $endDt,         // 23, 24 (TRANS)
            $year,                   // 25 (SALARY YEAR)
            $psnlCd, $prevYearSttDt, $sttDt // 26, 27, 28 (WHERE)
        );
    }
    
    $types = str_repeat("s", count($params));
    $sql = implode(" UNION ", $sqlParts);

    $data = executeQuery($conn, $sql, $types, $params);
    jsonResponse($conn, ["data" => $data ?: null]);
?>