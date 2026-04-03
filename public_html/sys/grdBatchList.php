<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.GRADE_HISTORY A 
        LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER D ON D.TRS_CD = (SELECT TRS_CD FROM PSNL_TRANSFER WHERE PSNL_CD=A.PSNL_CD ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1)
        JOIN (SELECT PSNL_CD,MAX(ADVANCE_DT) AS MAX_ADVANCE_DT FROM GRADE_HISTORY GROUP BY PSNL_CD) LST ON A.PSNL_CD = LST.PSNL_CD AND A.ADVANCE_DT = LST.MAX_ADVANCE_DT
    ";
    //기본 쿼리
    $sql = "SELECT C.ORG_NM,B.PSNL_NM,D.POSITION,A.* FROM BONDANG_HR.GRADE_HISTORY A 
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
    LEFT OUTER JOIN PSNL_TRANSFER D ON D.TRS_CD = (SELECT TRS_CD FROM PSNL_TRANSFER WHERE PSNL_CD=A.PSNL_CD ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1)
    LEFT OUTER JOIN ORG_INFO C ON D.ORG_CD = C.ORG_CD
    JOIN (SELECT PSNL_CD,MAX(ADVANCE_DT) AS MAX_ADVANCE_DT FROM GRADE_HISTORY GROUP BY PSNL_CD) LST ON A.PSNL_CD = LST.PSNL_CD AND A.ADVANCE_DT = LST.MAX_ADVANCE_DT
    ";
    //조건문 지정
    $whereSql = " WHERE 1=1 AND D.TRS_TYPE IN (1,3) AND A.GRD_PAY > 0"; //재직상태가 재직이나 전보인 경우만 조회(퇴직은 TRS_TYPE 값이 2 임) ---- 호봉  GRD_PAY값이 0보다 클것
    $params = [];
    $types = "";
    if(@$_REQUEST['PSNL_CD']){
        $whereSql .= " AND A.PSNL_CD = ?";
        $params[] = @$_REQUEST['PSNL_CD'];
        $types .= "s";
    }
    if(@$_REQUEST['PSNL_NM']){
        $whereSql .= " AND B.PSNL_NM LIKE ?";
        $params[] = '%'.@$_REQUEST['PSNL_NM'].'%';
        $types .= "s";
    }    
    if(@$_REQUEST['ORG_NM']){
        $whereSql .= " AND C.ORG_NM LIKE ?";
        $params[] = '%'.@$_REQUEST['ORG_NM'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['ADVANCE_DT_From']){
        $whereSql .= " AND ADVANCE_DT >= ?";
        $params[] = $_REQUEST['ADVANCE_DT_From'];
        $types .= "s";
    }
    if(@$_REQUEST['ADVANCE_DT_To']){
        $whereSql .= " AND ADVANCE_DT <= ?";
        $params[] = $_REQUEST['ADVANCE_DT_To'];
        $types .= "s";
    }
    if(@$_REQUEST['GRD_GRADE']){
        $whereSql .= " AND GRD_GRADE LIKE ?";
        $params[] = '%'.$_REQUEST['GRD_GRADE'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['GRD_PAY']){
        $whereSql .= " AND GRD_PAY LIKE ?";
        $params[] = '%'.$_REQUEST['GRD_PAY'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['GRD_DTL']){
        $whereSql .= " AND GRD_DTL = ?";
        $params[] = $_REQUEST['GRD_DTL'];
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