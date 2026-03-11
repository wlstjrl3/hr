<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
//갯수 카운트 쿼리
$rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM PSNL_INFO A 
        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(TRS_CD ORDER BY TRS_DT DESC, TRS_CD DESC), ',', 1) AS MAX_TRS_CD
            FROM PSNL_TRANSFER
            WHERE TRS_TYPE IN (1,2)
            GROUP BY PSNL_CD
        ) C_SUB ON C_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER C ON C.TRS_CD = C_SUB.MAX_TRS_CD
        
        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(TRS_CD ORDER BY TRS_DT DESC, TRS_CD DESC), ',', 1) AS MAX_TRS_CD
            FROM PSNL_TRANSFER
            GROUP BY PSNL_CD
        ) C2_SUB ON C2_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER C2 ON C2.TRS_CD = C2_SUB.MAX_TRS_CD
        LEFT OUTER JOIN ORG_INFO B ON C2.ORG_CD = B.ORG_CD   
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
            AND LEFT(OH_DT,4) = (LEFT(D.ADVANCE_DT,4)-1)
        ),0) AS PERSON_CNT
        ,A.PSNL_CD,A.PSNL_NM,A.BAPT_NM,A.PHONE_NUM,LEFT(A.PSNL_NUM,14) AS PSNL_NUM
        ,TRUNCATE(DATEDIFF(CURDATE(), C.TRS_DT)/365,1) AS TRS_ELAPSE 
        ,D.ADVANCE_DT
        ,CASE 
        WHEN SUBSTR(D.ADVANCE_DT,6,2) = '01' THEN '상반기'
        WHEN SUBSTR(D.ADVANCE_DT,6,2) = '07' THEN '하반기'
        
        END AS ADVANCE_RNG
        ,D.GRD_GRADE,D.GRD_PAY
        ,FORMAT(E.NORMAL_PAY,0) AS NORMAL_PAY,FORMAT(E.LEGAL_PAY,0) AS LEGAL_PAY
        ,IFNULL((
            SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE ADJ_TYPE='직책' AND PSNL_CD = A.PSNL_CD
            AND LEFT(ADJ_STT_DT,4) <= LEFT(D.ADVANCE_DT,4)
            AND (LEFT(ADJ_END_DT,4) >= LEFT(D.ADVANCE_DT,4) OR ADJ_END_DT is null)
        ),0) AS ADJUST_PAY1
        ,IFNULL((
            SELECT SUM(FML_PAY) FROM PSNL_FAMILY WHERE PSNL_CD = A.PSNL_CD
            AND LEFT(FML_STT_DT,4) <= LEFT(D.ADVANCE_DT,4)
            AND (LEFT(FML_END_DT,4) >= LEFT(D.ADVANCE_DT,4) OR FML_END_DT is null)
        ),0) AS FAMILY_PAY        
        ,IFNULL((
            SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE ADJ_TYPE='자격' AND PSNL_CD = A.PSNL_CD
            AND LEFT(ADJ_STT_DT,4) <= LEFT(D.ADVANCE_DT,4)
            AND (LEFT(ADJ_END_DT,4) >= LEFT(D.ADVANCE_DT,4) OR ADJ_END_DT is null)
        ),0) AS ADJUST_PAY2
        ,IFNULL((
            SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE ADJ_TYPE='장애' AND PSNL_CD = A.PSNL_CD
            AND LEFT(ADJ_STT_DT,4) <= LEFT(D.ADVANCE_DT,4)
            AND (LEFT(ADJ_END_DT,4) >= LEFT(D.ADVANCE_DT,4) OR ADJ_END_DT is null)
        ),0) AS ADJUST_PAY3
        ,IFNULL((
            SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE ADJ_TYPE='조정' AND PSNL_CD = A.PSNL_CD
            AND LEFT(ADJ_STT_DT,4) <= LEFT(D.ADVANCE_DT,4)
            AND (LEFT(ADJ_END_DT,4) >= LEFT(D.ADVANCE_DT,4) OR ADJ_END_DT is null)
        ),0) AS ADJUST_PAY4
        ,FORMAT(
        IFNULL((
            SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE PSNL_CD = A.PSNL_CD
            AND LEFT(ADJ_STT_DT,4) <= LEFT(D.ADVANCE_DT,4)
            AND (LEFT(ADJ_END_DT,4) >= LEFT(D.ADVANCE_DT,4) OR ADJ_END_DT is null)
        ),0) + 
        IFNULL((
            SELECT SUM(FML_PAY) FROM PSNL_FAMILY WHERE PSNL_CD = A.PSNL_CD
            AND LEFT(FML_STT_DT,4) <= LEFT(D.ADVANCE_DT,4)
            AND (LEFT(FML_END_DT,4) >= LEFT(D.ADVANCE_DT,4) OR FML_END_DT is null)
        ),0) +
        E.NORMAL_PAY+E.LEGAL_PAY+0,0) AS EXPECT_PAY
        ,B.ORG_IN_TEL, B.ORG_OUT_TEL
        FROM PSNL_INFO A
        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(TRS_CD ORDER BY TRS_DT DESC, TRS_CD DESC), ',', 1) AS MAX_TRS_CD
            FROM PSNL_TRANSFER
            WHERE TRS_TYPE IN (1,2)
            GROUP BY PSNL_CD
        ) C_SUB ON C_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER C ON C.TRS_CD = C_SUB.MAX_TRS_CD

        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(TRS_CD ORDER BY TRS_DT DESC, TRS_CD DESC), ',', 1) AS MAX_TRS_CD
            FROM PSNL_TRANSFER
            GROUP BY PSNL_CD
        ) C2_SUB ON C2_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER C2 ON C2.TRS_CD = C2_SUB.MAX_TRS_CD

        LEFT OUTER JOIN ORG_INFO B ON C2.ORG_CD = B.ORG_CD            
        
        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(GRD_CD ORDER BY ADVANCE_DT DESC, GRD_CD DESC), ',', 1) AS MAX_GRD_CD
            FROM GRADE_HISTORY
            GROUP BY PSNL_CD
        ) D_SUB ON D_SUB.PSNL_CD = A.PSNL_CD
        LEFT OUTER JOIN GRADE_HISTORY D ON D.GRD_CD = D_SUB.MAX_GRD_CD

        /* C2의 최근고용형태 참조 & 계약직+무기 계약직 동시 조건에 들어가도록 join 조건에 concat을 이용한 like 조건을 적용함.*/
        LEFT OUTER JOIN SALARY_TB E ON C2.WORK_TYPE LIKE CONCAT('%',E.SLR_TYPE) AND  D.GRD_GRADE = E.SLR_GRADE AND D.GRD_PAY = E.SLR_PAY AND SLR_YEAR = LEFT(D.ADVANCE_DT,4)
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
    $whereSql .= " AND C.POSITION LIKE ?";
    $params[] = '%' . $_REQUEST['POSITION'] . '%';
    $types .= "s";
}
if (@$_REQUEST['WORK_TYPE']) {
    $whereSql .= " AND C.WORK_TYPE LIKE ?";
    $params[] = '%' . $_REQUEST['WORK_TYPE'] . '%';
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
