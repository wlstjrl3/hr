<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.SALARY_TB";
    //기본 쿼리
    $sql = "SELECT * FROM BONDANG_HR.SALARY_TB";
    //조건문 지정
    $whereSql = " WHERE 1=1 ";
    $params = [];
    $types = "";
    if(@$_REQUEST['SLR_YEAR']){
        $whereSql .= " AND SLR_YEAR LIKE ?";
        $params[] = '%' . $_REQUEST['SLR_YEAR'] . '%';
        $types .= "s";
    }
    if(@$_REQUEST['SLR_TYPE']){
        $whereSql .= " AND SLR_TYPE LIKE ?";
        $params[] = '%'.$_REQUEST['SLR_TYPE'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['SLR_GRADE']){
        $whereSql .= " AND SLR_GRADE LIKE ?";
        $params[] = '%'.$_REQUEST['SLR_GRADE'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['SLR_PAY']){
        $whereSql .= " AND SLR_PAY LIKE ?";
        $params[] = '%'.$_REQUEST['SLR_PAY'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['NORMAL_PAY_From']){
        $whereSql .= " AND NORMAL_PAY >= ?";
        $params[] = $_REQUEST['NORMAL_PAY_From'];
        $types .= "d";
    }
    if(@$_REQUEST['NORMAL_PAY_To']){
        $whereSql .= " AND NORMAL_PAY <= ?";
        $params[] = $_REQUEST['NORMAL_PAY_To'];
        $types .= "d";
    }
    if(@$_REQUEST['LEGAL_PAY_From']){
        $whereSql .= " AND LEGAL_PAY >= ?";
        $params[] = $_REQUEST['LEGAL_PAY_From'];
        $types .= "d";
    }
    if(@$_REQUEST['LEGAL_PAY_To']){
        $whereSql .= " AND LEGAL_PAY <= ?";
        $params[] = $_REQUEST['LEGAL_PAY_To'];
        $types .= "d";
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