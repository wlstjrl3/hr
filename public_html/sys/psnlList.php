<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_INFO A 
    LEFT OUTER JOIN PSNL_TRANSFER C ON C.TRS_CD = (
        SELECT TRS_CD FROM PSNL_TRANSFER AS C2
            WHERE C2.PSNL_CD = A.PSNL_CD
            ORDER BY TRS_DT DESC, TRS_CD DESC
            LIMIT 1
    )
    INNER JOIN BONDANG_HR.ORG_INFO B ON C.ORG_CD = B.ORG_CD ";
    //기본 쿼리
    $sql = "SELECT B.ORG_NM, A.* FROM BONDANG_HR.PSNL_INFO A
    LEFT OUTER JOIN PSNL_TRANSFER C ON C.TRS_CD = (
        SELECT TRS_CD FROM PSNL_TRANSFER AS C2
            WHERE C2.PSNL_CD = A.PSNL_CD
            ORDER BY TRS_DT DESC, TRS_CD DESC
            LIMIT 1
    )
    LEFT OUTER JOIN BONDANG_HR.ORG_INFO B ON C.ORG_CD = B.ORG_CD 
    ";
    //조건문 지정
    $whereSql = " WHERE 1=1 ";
    $params = [];
    $types = "";

    if(@$_REQUEST['PSNL_CD']){
        $whereSql .= " AND A.PSNL_CD = ?";
        $params[] = $_REQUEST['PSNL_CD'];
        $types .= "s";
    }    
    if(@$_REQUEST['ORG_NM']){
        $whereSql .= " AND ORG_NM LIKE ?";
        $params[] = '%' . $_REQUEST['ORG_NM'] . '%';
        $types .= "s";
    }
    if(@$_REQUEST['PSNL_NM']){
        $whereSql .= " AND PSNL_NM LIKE ?";
        $params[] = '%'.$_REQUEST['PSNL_NM'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['BAPT_NM']){
        $whereSql .= " AND BAPT_NM LIKE ?";
        $params[] = '%'.$_REQUEST['BAPT_NM'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['PHONE_NUM']){
        $whereSql .= " AND PHONE_NUM LIKE ?";
        $params[] = '%'.$_REQUEST['PHONE_NUM'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['PSNL_BIRTH_From']){ //PSNL_NUM을 날짜형식으로 변경하여 비교해야함.
        $whereSql .= " AND PSNL_NUM >= ?";
        $params[] = $_REQUEST['PSNL_BIRTH'] . " 00:00:00";
        $types .= "s";
    }
    if(@$_REQUEST['PSNL_BIRTH_To']){ //PSNL_NUM을 날짜형식으로 변경하여 비교해야함.
        $whereSql .= " AND PSNL_NUM <= ?";
        $params[] = $_REQUEST['PSNL_BIRTH_To'] . " 23:59:59";
        $types .= "s";
    }
    if(@$_REQUEST['PSNL_NUM']){
        $whereSql .= " AND PSNL_NUM LIKE ?";
        $params[] = '%'.$_REQUEST['PSNL_NUM'].'%';
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