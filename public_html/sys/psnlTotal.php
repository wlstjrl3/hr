<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

$baseDate = @$_REQUEST['STAT_BASE_DATE'];
$trsCond = "";
$pttCond = "";
$grdCond = "";
if ($baseDate) {
    $baseDateEsc = mysqli_real_escape_string($conn, $baseDate);
    $baseDateStr = str_replace('-', '', $baseDateEsc);
    $trsCond = " AND REPLACE(TRS_DT, '-', '') <= '{$baseDateStr}' ";
    $pttCond = " WHERE PTT_YEAR <= LEFT('{$baseDateStr}', 4) ";
    $grdCond = " WHERE REPLACE(ADVANCE_DT, '-', '') <= '{$baseDateStr}' ";
}

//갯수 카운트 쿼리
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

        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(GRD_CD ORDER BY ADVANCE_DT DESC, GRD_CD DESC), ',', 1) AS MAX_GRD_CD
            FROM GRADE_HISTORY
            {$grdCond}
            GROUP BY PSNL_CD
        ) D_SUB ON D_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN GRADE_HISTORY D ON D.GRD_CD = D_SUB.MAX_GRD_CD

        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(PTT_CD ORDER BY PTT_YEAR DESC, PTT_CD DESC), ',', 1) AS MAX_PTT_CD
            FROM PSNL_PARTTIME
            {$pttCond}
            GROUP BY PSNL_CD
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
        ,C2.TRS_DT, C.APP_DT, B.ORG_NM, B.ORG_CD
        ,IFNULL((
            SELECT PERSON_CNT FROM ORG_HISTORY WHERE B.ORG_CD = ORG_HISTORY.ORG_CD
            ORDER BY OH_DT DESC LIMIT 1
        ),0) AS PERSON_CNT
        ,A.PSNL_CD,A.PSNL_NM,A.BAPT_NM
        ,(YEAR(CURDATE()) - (CASE WHEN SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('1', '2', '5', '6') THEN 1900 ELSE 2000 END + LEFT(A.PSNL_NUM, 2))) AS AGE
        ,A.PHONE_NUM,LEFT(A.PSNL_NUM,14) AS PSNL_NUM
        ,TRUNCATE(DATEDIFF(CURDATE(), C.TRS_DT)/365,1) AS TRS_ELAPSE 
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
        ,IFNULL((
            SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE ADJ_TYPE='직책' AND PSNL_CD = A.PSNL_CD
            AND LEFT(ADJ_STT_DT,4) <= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000')
            AND (LEFT(ADJ_END_DT,4) >= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000') OR ADJ_END_DT is null)
        ),0) AS ADJUST_PAY1
        ,IFNULL((
            SELECT SUM(FML_PAY) FROM PSNL_FAMILY WHERE PSNL_CD = A.PSNL_CD
            AND LEFT(FML_STT_DT,4) <= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000')
            AND (LEFT(FML_END_DT,4) >= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000') OR FML_END_DT is null)
        ),0) AS FAMILY_PAY        
        ,IFNULL((
            SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE ADJ_TYPE='자격' AND PSNL_CD = A.PSNL_CD
            AND LEFT(ADJ_STT_DT,4) <= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000')
            AND (LEFT(ADJ_END_DT,4) >= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000') OR ADJ_END_DT is null)
        ),0) AS ADJUST_PAY2
        ,IFNULL((
            SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE ADJ_TYPE='장애' AND PSNL_CD = A.PSNL_CD
            AND LEFT(ADJ_STT_DT,4) <= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000')
            AND (LEFT(ADJ_END_DT,4) >= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000') OR ADJ_END_DT is null)
        ),0) AS ADJUST_PAY3
        ,IFNULL((
            SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE ADJ_TYPE='조정' AND PSNL_CD = A.PSNL_CD
            AND LEFT(ADJ_STT_DT,4) <= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000')
            AND (LEFT(ADJ_END_DT,4) >= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000') OR ADJ_END_DT is null)
        ),0) AS ADJUST_PAY4
        ,IF(PTT.PTT_CD IS NOT NULL AND (D.ADVANCE_DT IS NULL OR PTT.PTT_YEAR > LEFT(D.ADVANCE_DT, 4)),
            CONCAT(
                FORMAT(CEIL(PTT.PTT_ADDHOUR*4.345*E_MIN.LEGAL_PAY*1.5/10)*10+(CEIL((PTT.PTT_HOUR+(PTT.PTT_HOUR*4/20))*(365/7/12))*E_MIN.LEGAL_PAY+PTT.PTT_ADJPAY),0),
                '<br><span class=\"fs7 cl3\">(', PTT.PTT_DAY, '일*', PTT.PTT_HOUR, '시', IF(IFNULL(PTT.PTT_ADJPAY,0) > 0, CONCAT('+', IF(PTT.PTT_ADJPAY % 10000 = 0, CAST(FLOOR(PTT.PTT_ADJPAY/10000) AS CHAR), CAST(ROUND(PTT.PTT_ADJPAY/10000, 1) AS CHAR)), '만'), ''), ')</span>'
            ),
            FORMAT(
                IFNULL((
                    SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE PSNL_CD = A.PSNL_CD
                    AND LEFT(ADJ_STT_DT,4) <= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000')
                    AND (LEFT(ADJ_END_DT,4) >= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000') OR ADJ_END_DT is null)
                ),0) + 
                IFNULL((
                    SELECT SUM(FML_PAY) FROM PSNL_FAMILY WHERE PSNL_CD = A.PSNL_CD
                    AND LEFT(FML_STT_DT,4) <= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000')
                    AND (LEFT(FML_END_DT,4) >= NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000') OR FML_END_DT is null)
                ),0) +
                E.NORMAL_PAY+E.LEGAL_PAY+0,0)
        ) AS EXPECT_PAY
        ,B.ORG_IN_TEL, B.ORG_OUT_TEL
        FROM PSNL_INFO A
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
        
        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(GRD_CD ORDER BY ADVANCE_DT DESC, GRD_CD DESC), ',', 1) AS MAX_GRD_CD
            FROM GRADE_HISTORY
            {$grdCond}
            GROUP BY PSNL_CD
        ) D_SUB ON D_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN GRADE_HISTORY D ON D.GRD_CD = D_SUB.MAX_GRD_CD

        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(PTT_CD ORDER BY PTT_YEAR DESC, PTT_CD DESC), ',', 1) AS MAX_PTT_CD
            FROM PSNL_PARTTIME
            {$pttCond}
            GROUP BY PSNL_CD
        ) PTT_SUB ON PTT_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN PSNL_PARTTIME PTT ON PTT.PTT_CD = PTT_SUB.MAX_PTT_CD

        /* C2의 최근고용형태 참조 & 계약직+무기 계약직 동시 조건에 들어가도록 join 조건에 concat을 이용한 like 조건을 적용함.*/
        LEFT OUTER JOIN SALARY_TB E ON C2.WORK_TYPE LIKE CONCAT('%',E.SLR_TYPE) AND  D.GRD_GRADE = E.SLR_GRADE AND D.GRD_PAY = E.SLR_PAY AND SLR_YEAR = NULLIF(GREATEST(IFNULL(LEFT(D.ADVANCE_DT, 4), '0000'), IFNULL(PTT.PTT_YEAR, '0000')), '0000')

        LEFT OUTER JOIN SALARY_TB E_MIN ON PTT.PTT_YEAR = E_MIN.SLR_YEAR AND E_MIN.SLR_TYPE = '최저시급'
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
    $whereSql = $whereSql . " AND ORG_NM LIKE '%" . $_REQUEST['ORG_NM'] . "%'"; //조직 정보의 B테이블에서 가져온다.
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
        $whereSql = $whereSql . " AND C.TRS_TYPE IN ('1','3')";
    }
    else {
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
// PSNL_NUM에서 완전한 YYYY-MM-DD 형식의 생년월일을 동적으로 생성
// 주민등록번호의 7번째 자리(성별/세기 구분)를 사용하여 연도 세기를 결정
$derivedBirthDateSql = "CONCAT(
        CASE 
            WHEN SUBSTR(A.PSNL_NUM, 7, 1) IN ('1', '2', '5', '6') THEN '19'
            WHEN SUBSTR(A.PSNL_NUM, 7, 1) IN ('3', '4', '7', '8') THEN '20'
            ELSE '19' -- 기본값 또는 오류 처리 (필요에 따라 조정)
        END,
        SUBSTR(A.PSNL_NUM, 1, 2), '-',
        SUBSTR(A.PSNL_NUM, 3, 2), '-',
        SUBSTR(A.PSNL_NUM, 5, 2)
    )";

if (@$_REQUEST['PSNL_BIRTH_From']) {
    $whereSql .= " AND " . $derivedBirthDateSql . " >= '" . $_REQUEST['PSNL_BIRTH_From'] . "'";
}
if (@$_REQUEST['PSNL_BIRTH_To']) {
    $whereSql .= " AND " . $derivedBirthDateSql . " <= '" . $_REQUEST['PSNL_BIRTH_To'] . "'";
}
if (@$_REQUEST['TRS_DT_From']) {
    $whereSql .= " AND C.TRS_DT >= ?";
    $params[] = $_REQUEST['TRS_DT_From'] . " 00:00:00";
    $types .= "s";
}
if (@$_REQUEST['TRS_DT_To']) {
    $whereSql .= " AND C.TRS_DT <= ?";
    $params[] = $_REQUEST['TRS_DT_To'] . " 23:59:59";
    $types .= "s";
}
// PSNL_NUM 자체를 검색하는 필터는 기존 위치 유지 (생년월일 필터와 독립적으로 작동)
if (@$_REQUEST['PSNL_NUM']) {
    $whereSql .= " AND A.PSNL_NUM LIKE ?";
    $params[] = '%' . $_REQUEST['PSNL_NUM'] . '%';
    $types .= "s";
}
if (@$_REQUEST['TRS_DT_From']) {
    $whereSql .= " AND C.TRS_DT >= ?";
    $params[] = $_REQUEST['TRS_DT_From'] . " 00:00:00";
    $types .= "s";
}
if (@$_REQUEST['TRS_DT_To']) {
    $whereSql .= " AND C.TRS_DT <= ?";
    $params[] = $_REQUEST['TRS_DT_To'] . " 23:59:59";
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
    }
    else if ($cat == 'REG_FEMALE') {
        $whereSql .= " AND (C2.WORK_TYPE = '정규직' OR C2.WORK_TYPE = '기능직') AND (PTT.PTT_HOUR >= 40 OR PTT.PTT_HOUR IS NULL) AND SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('2', '4', '6', '8', '0')";
    }
    else if ($cat == 'CONT_MALE') {
        $whereSql .= " AND (C2.WORK_TYPE LIKE '%계약직%' OR C2.WORK_TYPE = '무기계약직') AND (PTT.PTT_HOUR >= 40 OR PTT.PTT_HOUR IS NULL) AND SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('1', '3', '5', '7', '9')";
    }
    else if ($cat == 'CONT_FEMALE') {
        $whereSql .= " AND (C2.WORK_TYPE LIKE '%계약직%' OR C2.WORK_TYPE = '무기계약직') AND (PTT.PTT_HOUR >= 40 OR PTT.PTT_HOUR IS NULL) AND SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('2', '4', '6', '8', '0')";
    }
    else if ($cat == 'SHORT_MALE') {
        $whereSql .= " AND PTT.PTT_HOUR < 40 AND SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('1', '3', '5', '7', '9')";
    }
    else if ($cat == 'SHORT_FEMALE') {
        $whereSql .= " AND PTT.PTT_HOUR < 40 AND SUBSTR(REPLACE(A.PSNL_NUM, '-', ''), 7, 1) IN ('2', '4', '6', '8', '0')";
    }
    else if ($cat == 'TOTAL_CNT') {
        // 총계의 경우 별도의 성별/근무형태 필터링 없음
    }

    if ($target == 'ALL') {
        $whereSql .= " AND (B.UPPR_ORG_CD = '13061001' OR B.UPPR_ORG_CD IN (SELECT ORG_CD FROM ORG_INFO WHERE UPPR_ORG_CD = '13061001') OR B.ORG_CD = '13061001')";
    }
    else if ($target == 'DISTRICT') {
        $whereSql .= " AND (B.UPPR_ORG_CD = ? OR B.ORG_CD = ?)";
        $params[] = $org_cd;
        $params[] = $org_cd;
        $types .= "ss";
    }
    else if ($target == 'HOLY' || $target == 'PARISH') {
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
