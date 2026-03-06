<?php
include "sql_safe_helper.php";
    //if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_INFO A
            LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
            SELECT TRS_CD FROM PSNL_TRANSFER AS P2
            WHERE P2.PSNL_CD = A.PSNL_CD
            ORDER BY P2.REG_DT DESC
            LIMIT 1
        )
        LEFT OUTER JOIN ORG_INFO B ON P.ORG_CD = B.ORG_CD
    ";
    //기본 쿼리
    $sql = "SELECT A.PSNL_CD,A.PSNL_NM,A.BAPT_NM,B.ORG_NM,P.POSITION FROM BONDANG_HR.PSNL_INFO A 
        LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
            SELECT TRS_CD FROM PSNL_TRANSFER AS P2
            WHERE P2.PSNL_CD = A.PSNL_CD
            ORDER BY P2.REG_DT DESC
            LIMIT 1
        )
        LEFT OUTER JOIN ORG_INFO B ON P.ORG_CD = B.ORG_CD        
    ";
    //조건문 지정
    $whereSql = " WHERE 1=1 ";
    $params = [];
    $types = "";
    
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
    if(@$_REQUEST['ORG_NM']){
        $whereSql .= " AND B.ORG_NM LIKE ?";
        $params[] = '%'.$_REQUEST['ORG_NM'].'%';
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