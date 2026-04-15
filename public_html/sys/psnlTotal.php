<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

$baseDateRequested = @$_REQUEST['BASE_DATE'] ?? @$_REQUEST['STAT_BASE_DATE'] ?? '';
$baseDate = safeDateParam($baseDateRequested);
$baseDateStr = str_replace('-', '', $baseDate);
$targetYear = substr($baseDate, 0, 4);

// 기준일 파라미터가 명시적으로 없는 경우 '당해년도'로 처리 (safeDateParam이 오늘날짜를 반환하므로 동일하지만 의미 명확화)
if (empty($baseDateRequested)) {
    $targetYear = date('Y');
}

$trsCond = "";
$pttCond = "";
$grdCond = "";

if ($baseDate) {
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $baseDate)) {
        // Condition strings for subqueries
        $trsCond = " AND REPLACE(TRS_DT, '-', '') <= '" . $baseDateStr . "' ";
        $pttCond = " WHERE PTT_YEAR <= '" . $targetYear . "' ";
        $grdCond = " WHERE REPLACE(ADVANCE_DT, '-', '') <= '" . $baseDateStr . "' ";
    }
}

$useKoreanAge = @$_REQUEST['USE_KOREAN_AGE'] == 'Y';

$derivedBirthDateSql = "CONCAT(
        CASE 
            WHEN SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('1', '2', '5', '6') THEN '19'
            WHEN SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('3', '4', '7', '8') THEN '20'
            ELSE '19'
        END,
        SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 1, 2), '-',
        SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 3, 2), '-',
        SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 5, 2)
    )";

$baseDateAgeRef1 = $baseDate ? "'{$baseDate}'" : "CURDATE()";
$baseDateAgeRef2 = $baseDate ? "DATE_FORMAT('{$baseDate}', '%m%d')" : "DATE_FORMAT(CURDATE(), '%m%d')";

if ($useKoreanAge) {
    $ageSql = "(YEAR({$baseDateAgeRef1}) - CAST(SUBSTR({$derivedBirthDateSql}, 1, 4) AS UNSIGNED) + 1)";
} else {
    $ageSql = "(YEAR({$baseDateAgeRef1}) - CAST(SUBSTR({$derivedBirthDateSql}, 1, 4) AS UNSIGNED) - IF({$baseDateAgeRef2} < SUBSTR(REPLACE({$derivedBirthDateSql}, '-', ''), 5, 4), 1, 0))";
}

//갯수 카운트 쿼리
$rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM PSNL_INFO A 
        LEFT OUTER JOIN (
            SELECT PSNL_CD, TRS_CD AS MAX_TRS_CD
            FROM (
                SELECT PSNL_CD, TRS_CD, ROW_NUMBER() OVER (PARTITION BY PSNL_CD ORDER BY TRS_DT DESC, TRS_CD DESC) as rn
                FROM PSNL_TRANSFER
                WHERE 1=1 {$trsCond}
            ) t WHERE rn = 1
        ) C2_SUB ON C2_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER C ON C.TRS_CD = C2_SUB.MAX_TRS_CD
        LEFT OUTER JOIN PSNL_TRANSFER C2 ON C2.TRS_CD = C2_SUB.MAX_TRS_CD
        LEFT OUTER JOIN ORG_INFO B ON C2.ORG_CD = B.ORG_CD

        LEFT OUTER JOIN (
            SELECT PSNL_CD, GRD_CD AS MAX_GRD_CD
            FROM (
                SELECT PSNL_CD, GRD_CD, ROW_NUMBER() OVER (PARTITION BY PSNL_CD ORDER BY ADVANCE_DT DESC, GRD_CD DESC) as rn
                FROM GRADE_HISTORY
                {$grdCond}
            ) t WHERE rn = 1
        ) D_SUB ON D_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN GRADE_HISTORY D ON D.GRD_CD = D_SUB.MAX_GRD_CD

        LEFT OUTER JOIN (
            SELECT PSNL_CD, PTT_CD AS MAX_PTT_CD
            FROM (
                SELECT PSNL_CD, PTT_CD, ROW_NUMBER() OVER (PARTITION BY PSNL_CD ORDER BY PTT_YEAR DESC, PTT_CD DESC) as rn
                FROM PSNL_PARTTIME
                {$pttCond}
            ) t WHERE rn = 1
        ) PTT_SUB ON PTT_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN PSNL_PARTTIME PTT ON PTT.PTT_CD = PTT_SUB.MAX_PTT_CD
        ";
//기본 쿼리 (전보로 사무장 전환 등의 정보를 반영하기 위하여 기존 C.TRS_TYPE / C.POSITION등의 데이터를 모두 C2로 교체함 20251128 양진석)
$sql = "SELECT 
        CASE 
        WHEN C2.TRS_TYPE = 1 THEN '재직'
        WHEN C2.TRS_TYPE = 2 THEN '퇴사'
        WHEN C2.TRS_TYPE = 3 THEN '전보'
        END AS TRS_TYPE
        ,C2.POSITION,C2.WORK_TYPE
        ,C2.TRS_DT, HIRE_TRS.BNF_DT, C.APP_DT, B.ORG_NM, B.ORG_CD
        ,IFNULL(OH_V.PERSON_CNT, 0) AS PERSON_CNT
        ,A.PSNL_CD,A.PSNL_NM,A.BAPT_NM
        ,{$ageSql} AS AGE
        ,A.PHONE_NUM,LEFT(A.PSNL_NUM,14) AS PSNL_NUM
        ,TRUNCATE(DATEDIFF('{$baseDate}', IF(C2.TRS_TYPE = 3, CM.HIRE_DT, C.TRS_DT))/365,1) AS TRS_ELAPSE 
        ,CASE 
            WHEN D.ADVANCE_DT IS NOT NULL AND PTT.PTT_YEAR IS NOT NULL THEN 
                IF(LEFT(D.ADVANCE_DT, 4) >= PTT.PTT_YEAR, D.ADVANCE_DT, CONCAT(PTT.PTT_YEAR, '(최저)'))
            ELSE IFNULL(D.ADVANCE_DT, CONCAT(PTT.PTT_YEAR, '(최저)'))
        END AS ADVANCE_DT
        ,CASE 
        WHEN SUBSTR(D.ADVANCE_DT,6,2) = '01' THEN '상반기'
        WHEN SUBSTR(D.ADVANCE_DT,6,2) = '07' THEN '하반기'
        
        END AS ADVANCE_RNG
        ,D.GRD_GRADE,D.GRD_PAY
        ,FORMAT(E.NORMAL_PAY,0) AS NORMAL_PAY,FORMAT(E.LEGAL_PAY,0) AS LEGAL_PAY
        ,IFNULL(ADJ_V.SUM_ADJ1, 0) AS ADJUST_PAY1
        ,IFNULL(FML_V.SUM_FML, 0) AS FAMILY_PAY
        ,IFNULL(ADJ_V.SUM_ADJ2, 0) AS ADJUST_PAY2
        ,IFNULL(ADJ_V.SUM_ADJ3, 0) AS ADJUST_PAY3
        ,IFNULL(ADJ_V.SUM_ADJ4, 0) AS ADJUST_PAY4
        ,IF(PTT.PTT_CD IS NOT NULL AND (D.ADVANCE_DT IS NULL OR PTT.PTT_YEAR > LEFT(D.ADVANCE_DT, 4)),
            CONCAT(
                FORMAT(CEIL(PTT.PTT_ADDHOUR*4.345*E_MIN.LEGAL_PAY*1.5/10)*10+(CEIL((PTT.PTT_HOUR+(PTT.PTT_HOUR*4/20))*(365/7/12))*E_MIN.LEGAL_PAY+PTT.PTT_ADJPAY),0),
                '<br><span class=\"fs7 cl3\">(', PTT.PTT_DAY, '일*', PTT.PTT_HOUR, '시', IF(IFNULL(PTT.PTT_ADJPAY,0) > 0, CONCAT('+', IF(PTT.PTT_ADJPAY % 10000 = 0, CAST(FLOOR(PTT.PTT_ADJPAY/10000) AS CHAR), CAST(ROUND(PTT.PTT_ADJPAY/10000, 1) AS CHAR)), '만'), ''), ')</span>'
            ),
            FORMAT(
                IFNULL(ADJ_V.SUM_ADJ_ALL, 0) +
                IFNULL(FML_V.SUM_FML, 0) +
                E.NORMAL_PAY+E.LEGAL_PAY+0, 0)
        ) AS EXPECT_PAY
        ,B.ORG_IN_TEL, B.ORG_OUT_TEL
        FROM PSNL_INFO A
        LEFT OUTER JOIN (
            SELECT PSNL_CD, TRS_CD AS MAX_TRS_CD
            FROM (
                SELECT PSNL_CD, TRS_CD, ROW_NUMBER() OVER (PARTITION BY PSNL_CD ORDER BY TRS_DT DESC, TRS_CD DESC) as rn
                FROM PSNL_TRANSFER
                WHERE 1=1 {$trsCond}
            ) t WHERE rn = 1
        ) C2_SUB ON C2_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER C ON C.TRS_CD = C2_SUB.MAX_TRS_CD
        LEFT OUTER JOIN PSNL_TRANSFER C2 ON C2.TRS_CD = C2_SUB.MAX_TRS_CD

        LEFT OUTER JOIN ORG_INFO B ON C2.ORG_CD = B.ORG_CD            
        
        /* [추가] 입사(최초 발령)일 조회를 위한 집계 */
        LEFT OUTER JOIN (
            SELECT PSNL_CD, MIN(TRS_DT) AS HIRE_DT
            FROM PSNL_TRANSFER
            GROUP BY PSNL_CD
        ) CM ON CM.PSNL_CD = A.PSNL_CD

        /* [추가] 발령구분 '입사'(TRS_TYPE=1) 레코드의 BNF_DT 조회 (1건 보장) */
        LEFT OUTER JOIN (
            SELECT PSNL_CD, BNF_DT
            FROM (
                SELECT PSNL_CD, BNF_DT,
                       ROW_NUMBER() OVER (PARTITION BY PSNL_CD ORDER BY TRS_DT ASC, TRS_CD ASC) AS rn
                FROM PSNL_TRANSFER
                WHERE TRS_TYPE = '1'
            ) t WHERE rn = 1
        ) HIRE_TRS ON HIRE_TRS.PSNL_CD = A.PSNL_CD
        
        LEFT OUTER JOIN (
            SELECT PSNL_CD, GRD_CD AS MAX_GRD_CD
            FROM (
                SELECT PSNL_CD, GRD_CD, ROW_NUMBER() OVER (PARTITION BY PSNL_CD ORDER BY ADVANCE_DT DESC, GRD_CD DESC) as rn
                FROM GRADE_HISTORY
                {$grdCond}
            ) t WHERE rn = 1
        ) D_SUB ON D_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN GRADE_HISTORY D ON D.GRD_CD = D_SUB.MAX_GRD_CD

        LEFT OUTER JOIN (
            SELECT PSNL_CD, PTT_CD AS MAX_PTT_CD
            FROM (
                SELECT PSNL_CD, PTT_CD, ROW_NUMBER() OVER (PARTITION BY PSNL_CD ORDER BY PTT_YEAR DESC, PTT_CD DESC) as rn
                FROM PSNL_PARTTIME
                {$pttCond}
            ) t WHERE rn = 1
        ) PTT_SUB ON PTT_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN PSNL_PARTTIME PTT ON PTT.PTT_CD = PTT_SUB.MAX_PTT_CD

        /* [최적화] 조직별 최근 인원수 뷰 미리 집계 JOIN */
        LEFT OUTER JOIN (
            SELECT ORG_CD, PERSON_CNT
            FROM (
                SELECT ORG_CD, PERSON_CNT, ROW_NUMBER() OVER (PARTITION BY ORG_CD ORDER BY OH_DT DESC) as rn
                FROM ORG_HISTORY
            ) t WHERE rn = 1
        ) OH_V ON B.ORG_CD = OH_V.ORG_CD

        /* C2의 최근고용형태 참조 & 계약직+무기 계약직 동시 조건에 들어가도록 join 조건에 concat을 이용한 like 조건을 적용함.*/
        LEFT OUTER JOIN SALARY_TB E 
            ON E.SLR_YEAR = '{$targetYear}'
            AND D.GRD_GRADE = E.SLR_GRADE 
            AND D.GRD_PAY = E.SLR_PAY 
            AND C2.WORK_TYPE LIKE CONCAT('%', E.SLR_TYPE)

        LEFT OUTER JOIN SALARY_TB E_MIN 
            ON E_MIN.SLR_YEAR = '{$targetYear}'
            AND E_MIN.SLR_TYPE = '최저시급'

        /* [N+1 최적화 - 카테시안 곱 방지 완벽 해결] 당해년도(targetYear) 기준 집계 뷰 */
        LEFT OUTER JOIN (
            SELECT ADJ.PSNL_CD,
                   SUM(CASE WHEN ADJ.ADJ_TYPE='직책' THEN ADJ.ADJ_PAY ELSE 0 END) AS SUM_ADJ1,
                   SUM(CASE WHEN ADJ.ADJ_TYPE='자격' THEN ADJ.ADJ_PAY ELSE 0 END) AS SUM_ADJ2,
                   SUM(CASE WHEN ADJ.ADJ_TYPE='장애' THEN ADJ.ADJ_PAY ELSE 0 END) AS SUM_ADJ3,
                   SUM(CASE WHEN ADJ.ADJ_TYPE='조정' THEN ADJ.ADJ_PAY ELSE 0 END) AS SUM_ADJ4,
                   SUM(ADJ.ADJ_PAY) AS SUM_ADJ_ALL
            FROM PSNL_ADJUST ADJ
            WHERE ADJ.ADJ_STT_DT <= '{$targetYear}-12-31'
              AND (ADJ.ADJ_END_DT >= '{$targetYear}-01-01' OR ADJ.ADJ_END_DT IS NULL)
            GROUP BY ADJ.PSNL_CD
        ) ADJ_V ON ADJ_V.PSNL_CD = A.PSNL_CD

        /* [N+1 최적화 - 가족수당 집계 인라 인 뷰] */
        LEFT OUTER JOIN (
            SELECT FML.PSNL_CD,
                   SUM(FML.FML_PAY) AS SUM_FML
            FROM PSNL_FAMILY FML
            WHERE FML.FML_STT_DT <= '{$targetYear}-12-31'
              AND (FML.FML_END_DT >= '{$targetYear}-01-01' OR FML.FML_END_DT IS NULL)
            GROUP BY FML.PSNL_CD
        ) FML_V ON FML_V.PSNL_CD = A.PSNL_CD
        ";
//조건문 지정
$whereSql = " WHERE 1=1 ";
$params = [];
$types = "";
if (@$_REQUEST['PSNL_CD']) {
    $whereSql .= " AND A.PSNL_CD = ?";
    $params[] = $_REQUEST['PSNL_CD'];
    $types .= "s";
}
if (@$_REQUEST['ORG_NM']) {
    $whereSql .= " AND ORG_NM LIKE ?";
    $params[] = '%' . $_REQUEST['ORG_NM'] . '%';
    $types .= "s";
}
if (@$_REQUEST['PSNL_NM']) {
    $whereSql .= " AND PSNL_NM LIKE ?";
    $params[] = '%' . $_REQUEST['PSNL_NM'] . '%';
    $types .= "s";
}

if (@$_REQUEST['BAPT_NM']) {
    $whereSql .= " AND BAPT_NM LIKE ?";
    $params[] = '%' . $_REQUEST['BAPT_NM'] . '%';
    $types .= "s";
}
if (@$_REQUEST['POSITION']) {
    $whereSql .= " AND C2.POSITION LIKE ?";
    $params[] = '%' . $_REQUEST['POSITION'] . '%';
    $types .= "s";
}
if (@$_REQUEST['WORK_TYPE']) {
    $workTypes = array_filter(array_map('trim', explode(',', $_REQUEST['WORK_TYPE'])));
    if (!empty($workTypes)) {
        $wtConds = [];
        foreach ($workTypes as $wt) {
            $wtConds[] = "C2.WORK_TYPE LIKE ?";
            $params[] = '%' . $wt . '%';
            $types .= "s";
        }
        $whereSql .= " AND (" . implode(" OR ", $wtConds) . ")";
    }
}
if (@$_REQUEST['EXCLUDE_POS']) {
    $whereSql .= " AND C2.POSITION NOT LIKE ?";
    $params[] = '%' . $_REQUEST['EXCLUDE_POS'] . '%';
    $types .= "s";
}
if (@$_REQUEST['TRS_TYPE']) {
    if ($_REQUEST['TRS_TYPE'] == 1) {
        $whereSql = $whereSql . " AND C.TRS_TYPE != '2' AND C.TRS_TYPE IS NOT NULL";
    } else {
        $whereSql .= " AND C.TRS_TYPE = ?";
        $params[] = $_REQUEST['TRS_TYPE'];
        $types .= "s";
    }
}
if (@$_REQUEST['PHONE_NUM']) {
    $whereSql .= " AND PHONE_NUM LIKE ?";
    $params[] = '%' . $_REQUEST['PHONE_NUM'] . '%';
    $types .= "s";
}
if (@$_REQUEST['GENDER']) {
    if ($_REQUEST['GENDER'] == 'M') {
        $whereSql .= " AND SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('1', '3', '5', '7', '9')";
    } else if ($_REQUEST['GENDER'] == 'F') {
        $whereSql .= " AND SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('2', '4', '6', '8', '0')";
    }
}
if (@$_REQUEST['GRD_GRADE']) {
    $whereSql .= " AND D.GRD_GRADE = ?";
    $params[] = $_REQUEST['GRD_GRADE'];
    $types .= "s";
}
if (@$_REQUEST['GRD_GRADE_From']) {
    $whereSql .= " AND CAST(D.GRD_GRADE AS UNSIGNED) >= ?";
    $params[] = $_REQUEST['GRD_GRADE_From'];
    $types .= "i";
}
if (@$_REQUEST['GRD_GRADE_To']) {
    $whereSql .= " AND CAST(D.GRD_GRADE AS UNSIGNED) <= ?";
    $params[] = $_REQUEST['GRD_GRADE_To'];
    $types .= "i";
}
if (@$_REQUEST['GRD_PAY']) {
    if ($_REQUEST['GRD_PAY'] == 'EMPTY') {
        $whereSql .= " AND (D.GRD_PAY IS NULL OR D.GRD_PAY = '' OR D.GRD_PAY = '0')";
    } else {
        $whereSql .= " AND D.GRD_PAY = ?";
        $params[] = $_REQUEST['GRD_PAY'];
        $types .= "s";
    }
}
if (@$_REQUEST['GRD_PAY_From']) {
    $whereSql .= " AND CAST(D.GRD_PAY AS UNSIGNED) >= ?";
    $params[] = $_REQUEST['GRD_PAY_From'];
    $types .= "i";
}
if (@$_REQUEST['GRD_PAY_To']) {
    $whereSql .= " AND CAST(D.GRD_PAY AS UNSIGNED) <= ?";
    $params[] = $_REQUEST['GRD_PAY_To'];
    $types .= "i";
}
if (@$_REQUEST['HAS_PAY'] == 'Y') {
    $whereSql .= " AND (D.GRD_PAY IS NOT NULL AND D.GRD_PAY != '' AND D.GRD_PAY != '0')";
}
if (@$_REQUEST['PSNL_BIRTH_From']) {
    $whereSql .= " AND " . $derivedBirthDateSql . " >= ?";
    $params[] = $_REQUEST['PSNL_BIRTH_From'];
    $types .= "s";
}
if (@$_REQUEST['PSNL_BIRTH_To']) {
    $whereSql .= " AND " . $derivedBirthDateSql . " <= ?";
    $params[] = $_REQUEST['PSNL_BIRTH_To'];
    $types .= "s";
}
if (@$_REQUEST['AGE_MIN']) {
    $whereSql .= " AND {$ageSql} >= ?";
    $params[] = (int) $_REQUEST['AGE_MIN'];
    $types .= "i";
}
if (@$_REQUEST['AGE_MAX']) {
    $whereSql .= " AND {$ageSql} <= ?";
    $params[] = (int) $_REQUEST['AGE_MAX'];
    $types .= "i";
}
$trsDtCol = (@$_REQUEST['USE_FIRST_TRS'] == 'Y') ? "(SELECT REPLACE(MIN(REPLACE(TRS_DT, '-', '')), '-', '') FROM PSNL_TRANSFER T_MIN WHERE T_MIN.PSNL_CD = A.PSNL_CD)" : "REPLACE(C.TRS_DT, '-', '')";

if (@$_REQUEST['TRS_DT_From']) {
    $whereSql .= " AND " . $trsDtCol . " >= ?";
    $params[] = str_replace('-', '', $_REQUEST['TRS_DT_From']);
    $types .= "s";
}
if (@$_REQUEST['TRS_DT_To']) {
    $whereSql .= " AND " . $trsDtCol . " <= ?";
    $params[] = str_replace('-', '', $_REQUEST['TRS_DT_To']);
    $types .= "s";
}
// PSNL_NUM 자체를 검색하는 필터는 기존 위치 유지 (생년월일 필터와 독립적으로 작동)
if (@$_REQUEST['PSNL_NUM']) {
    $whereSql .= " AND A.PSNL_NUM LIKE ?";
    $params[] = '%' . $_REQUEST['PSNL_NUM'] . '%';
    $types .= "s";
}

// statWorkType 통계 연결 모드 필터
if (@$_REQUEST['STAT_MODE'] == '1') {
    $cat = @$_REQUEST['STAT_CAT'];
    $target = @$_REQUEST['STAT_TARGET'];
    $org_cd = @$_REQUEST['STAT_ORG_CD'];

    // statWorkType 조건 일치 (퇴사제외)
    $whereSql .= " AND C2.TRS_TYPE != '2'";

    if ($cat == 'REG_MALE') {
        $whereSql .= " AND (C2.WORK_TYPE = '정규직' OR C2.WORK_TYPE = '기능직') AND (PTT.PTT_HOUR >= 40 OR PTT.PTT_HOUR IS NULL) AND SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('1', '3', '5', '7', '9')";
    } else if ($cat == 'REG_FEMALE') {
        $whereSql .= " AND (C2.WORK_TYPE = '정규직' OR C2.WORK_TYPE = '기능직') AND (PTT.PTT_HOUR >= 40 OR PTT.PTT_HOUR IS NULL) AND SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('2', '4', '6', '8', '0')";
    } else if ($cat == 'CONT_MALE') {
        $whereSql .= " AND (C2.WORK_TYPE LIKE '%계약직%' OR C2.WORK_TYPE = '무기계약직') AND (PTT.PTT_HOUR >= 40 OR PTT.PTT_HOUR IS NULL) AND SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('1', '3', '5', '7', '9')";
    } else if ($cat == 'CONT_FEMALE') {
        $whereSql .= " AND (C2.WORK_TYPE LIKE '%계약직%' OR C2.WORK_TYPE = '무기계약직') AND (PTT.PTT_HOUR >= 40 OR PTT.PTT_HOUR IS NULL) AND SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('2', '4', '6', '8', '0')";
    } else if ($cat == 'SHORT_MALE') {
        $whereSql .= " AND PTT.PTT_HOUR < 40 AND SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('1', '3', '5', '7', '9')";
    } else if ($cat == 'SHORT_FEMALE') {
        $whereSql .= " AND PTT.PTT_HOUR < 40 AND SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('2', '4', '6', '8', '0')";
    } else if ($cat == 'TOTAL_CNT') {
        // 총계의 경우 별도의 성별/근무형태 필터링 없음
    }

    if ($target == 'ALL') {
        $whereSql .= " AND (B.UPPR_ORG_CD = '13061001' OR B.UPPR_ORG_CD IN (SELECT ORG_CD FROM ORG_INFO WHERE UPPR_ORG_CD = '13061001') OR B.ORG_CD = '13061001')";
    } else if ($target == 'DISTRICT') {
        $whereSql .= " AND (B.UPPR_ORG_CD = ? OR B.ORG_CD = ?)";
        $params[] = $org_cd;
        $params[] = $org_cd;
        $types .= "ss";
    } else if ($target == 'HOLY' || $target == 'PARISH') {
        $whereSql .= " AND (B.ORG_CD = ?)";
        $params[] = $org_cd;
        $types .= "s";
    }
}


//정렬 기준 지정
$orderSql = safeOrderBy(@$_REQUEST['ORDER'], []);
//리미트 지정
$limitSql = safeLimit(@$_REQUEST['LIMIT']);
$totalCnt = mysqli_fetch_assoc(mysqli_query($conn, $rowCntSql));
$filterResult = executeQuery($conn, $rowCntSql . $whereSql, $types, $params);
$filterCnt = $filterResult[0];
$data = executeQuery($conn, $sql . $whereSql . $orderSql . $limitSql, $types, $params);
jsonResponse($conn, ["data" => $data ?: null, "totalCnt" => $totalCnt["ROW_CNT"], "filterCnt" => $filterCnt["ROW_CNT"]]);

?>