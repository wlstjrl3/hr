<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.ORG_HISTORY A
    LEFT OUTER JOIN ORG_INFO B ON A.ORG_CD = B.ORG_CD
    ";
    //기본 쿼리
    $sql = "SELECT A.OH_CD,B.ORG_CD,B.ORG_NM,OH_DT,PERSON_CNT,ETC,A.REG_DT FROM BONDANG_HR.ORG_HISTORY A
    LEFT OUTER JOIN ORG_INFO B ON A.ORG_CD = B.ORG_CD
    ";
    //조건문 지정
    $whereSql = " WHERE 1=1"; //" WHERE PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    $params = [];
    $types = "";
    if(@$_REQUEST['PSNL_CD']){
        $whereSql .= " AND A.PSNL_CD = ?";
        $params[] = @$_REQUEST['PSNL_CD'];
        $types .= "s";
    }
    if(@$_REQUEST['OH_DT_From']){
        $whereSql .= " AND OH_DT >= ?";
        $params[] = $_REQUEST['OH_DT_From'];
        $types .= "s";
    }
    if(@$_REQUEST['OH_DT_To']){
        $whereSql .= " AND OH_DT <= ?";
        $params[] = $_REQUEST['OH_DT_To'];
        $types .= "s";
    }
    if(@$_REQUEST['PERSON_CNT_From']){
        $whereSql .= " AND PERSON_CNT >= ?";
        $params[] = $_REQUEST['PERSON_CNT_From'];
        $types .= "s";
    }
    if(@$_REQUEST['PERSON_CNT_To']){
        $whereSql .= " AND PERSON_CNT <= ?";
        $params[] = $_REQUEST['PERSON_CNT_To'];
        $types .= "s";
    }
    if(@$_REQUEST['ORG_NM']){
        $whereSql .= " AND ORG_NM LIKE ?";
        $params[] = '%'.$_REQUEST['ORG_NM'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['ETC']){
        $whereSql .= " AND ETC LIKE ?";
        $params[] = '%'.$_REQUEST['ETC'].'%';
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