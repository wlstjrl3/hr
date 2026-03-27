<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.CERTIFICATE_HISTORY A LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD";
    //기본 쿼리
    $sql = "SELECT C.ORG_NM,B.PSNL_NM,D.POSITION,A.* FROM BONDANG_HR.CERTIFICATE_HISTORY A 
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
    LEFT OUTER JOIN PSNL_TRANSFER D ON D.TRS_CD = (SELECT TRS_CD FROM PSNL_TRANSFER WHERE PSNL_CD=A.PSNL_CD ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1)
    LEFT OUTER JOIN ORG_INFO C ON D.ORG_CD = C.ORG_CD
    ";
    //조건문 지정
    $whereSql = " WHERE 1=1";
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
    if(@$_REQUEST['CERT_TYPE']){
        $whereSql .= " AND A.CERT_TYPE LIKE ?";
        $params[] = '%'.$_REQUEST['CERT_TYPE'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['ISSUE_DT_From']){
        $whereSql .= " AND A.ISSUE_DT >= ?";
        $params[] = $_REQUEST['ISSUE_DT_From'];
        $types .= "s";
    }
    if(@$_REQUEST['ISSUE_DT_To']){
        $whereSql .= " AND A.ISSUE_DT <= ?";
        $params[] = $_REQUEST['ISSUE_DT_To'];
        $types .= "s";
    }
    if(@$_REQUEST['CERT_DTL']){
        $whereSql .= " AND A.CERT_DTL LIKE ?";
        $params[] = '%'.$_REQUEST['CERT_DTL'].'%';
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