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

        // [N+1 최적화 v2] 인라인 뷰 내 미리 집계 → Cartesian product(데이터 뻥튀기) 방지
        $sqlParts[] = "
        (SELECT 
            ? AS YEAR_MON
            ,A2.WORK_TYPE
            ,A.ADVANCE_DT
            ,A.GRD_GRADE
            ,A.GRD_PAY
            ,B.NORMAL_PAY
            ,B.LEGAL_PAY
            ,IFNULL(ADJ_M.POSI_PAY, 0) AS POSI_PAY
            ,IFNULL(FML_M.FML_PAY, 0) AS FML_PAY
            ,IFNULL(ADJ_M.LCS_PAY, 0) AS LCS_PAY
            ,IFNULL(ADJ_M.DIS_PAY, 0) AS DIS_PAY
            ,IFNULL(ADJ_M.ADJ_PAY, 0) AS ADJ_PAY
            ,B.NORMAL_PAY + B.LEGAL_PAY + IFNULL(FML_M.FML_PAY, 0) + IFNULL(ADJ_M.TOTAL_ADJ, 0) AS TOTAL_PAY
            FROM GRADE_HISTORY A
            LEFT OUTER JOIN PSNL_TRANSFER A2 ON TRS_CD = (SELECT TRS_CD FROM PSNL_TRANSFER WHERE PSNL_CD=? AND TRS_DT <= ? ORDER BY TRS_DT DESC LIMIT 1)
            LEFT OUTER JOIN SALARY_TB B ON GRD_GRADE = SLR_GRADE AND GRD_PAY = SLR_PAY AND SLR_YEAR = ? AND A2.WORK_TYPE LIKE CONCAT('%',SLR_TYPE)
            LEFT OUTER JOIN (
                SELECT PSNL_CD
                      ,SUM(CASE WHEN ADJ_TYPE='직책' THEN ADJ_PAY ELSE 0 END) AS POSI_PAY
                      ,SUM(CASE WHEN ADJ_TYPE='자격' THEN ADJ_PAY ELSE 0 END) AS LCS_PAY
                      ,SUM(CASE WHEN ADJ_TYPE='장애' THEN ADJ_PAY ELSE 0 END) AS DIS_PAY
                      ,SUM(CASE WHEN ADJ_TYPE='조정' THEN ADJ_PAY ELSE 0 END) AS ADJ_PAY
                      ,SUM(ADJ_PAY) AS TOTAL_ADJ
                FROM PSNL_ADJUST
                WHERE PSNL_CD = ? AND ADJ_STT_DT <= ? AND (ADJ_END_DT >= ? OR ADJ_END_DT IS NULL)
                GROUP BY PSNL_CD
            ) ADJ_M ON ADJ_M.PSNL_CD = A.PSNL_CD
            LEFT OUTER JOIN (
                SELECT PSNL_CD
                      ,SUM(FML_PAY) AS FML_PAY
                FROM PSNL_FAMILY
                WHERE PSNL_CD = ? AND FML_STT_DT <= ? AND (FML_END_DT >= ? OR FML_END_DT IS NULL)
                GROUP BY PSNL_CD
            ) FML_M ON FML_M.PSNL_CD = A.PSNL_CD
        WHERE A.PSNL_CD = ?
            AND A.ADVANCE_DT > ? AND A.ADVANCE_DT <= ?
        LIMIT 1)";

        // 파라미터: 13개/월 (인라인뷰 내 WHERE 포함)
        array_push($params,
            $monthStr,                        // 1  (YEAR_MON)
            $psnlCd, $endDt,                  // 2, 3  (TRANS: PSNL_CD=?, TRS_DT<=?)
            $year,                            // 4  (SLR_YEAR=?)
            $psnlCd, $endDt, $sttDt,          // 5, 6, 7  (ADJ_M 인라인뷰 WHERE)
            $psnlCd, $endDt, $sttDt,          // 8, 9, 10 (FML_M 인라인뷰 WHERE)
            $psnlCd, $prevYearSttDt, $sttDt   // 11,12,13 (메인 WHERE)
        );
    }
    
    $types = str_repeat("s", count($params));
    $sql = implode(" UNION ", $sqlParts);

    $data = executeQuery($conn, $sql, $types, $params);
    jsonResponse($conn, ["data" => $data ?: null]);
?>