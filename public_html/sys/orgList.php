<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.ORG_INFO A LEFT OUTER JOIN BONDANG_HR.ORG_INFO B ON A.UPPR_ORG_CD = B.ORG_CD";
    //기본 쿼리
    $sql = "SELECT B.ORG_NM AS UPR_ORG_NM
        ,A.ORG_CD
        ,A.ORG_NM
        ,CASE 
        WHEN A.ORG_TYPE='1' THEN '성지' 
        WHEN A.ORG_TYPE='11' THEN '본당' 
        WHEN A.ORG_TYPE='9' THEN '지구' END AS ORG_TYPE
        ,A.ORG_IN_TEL
        ,A.ORG_OUT_TEL
        ,A.REFRESH_DT
        FROM BONDANG_HR.ORG_INFO A LEFT OUTER JOIN BONDANG_HR.ORG_INFO B ON A.UPPR_ORG_CD = B.ORG_CD";
    //조건문 지정
    $whereSql = " WHERE 1=1 ";
    $params = [];
    $types = "";
    if(@$_REQUEST['UUPR_ORG']){
        $whereSql=$whereSql." AND (B.UPPR_ORG_CD = '".$_REQUEST['UUPR_ORG']."' OR A.UPPR_ORG_CD = '".$_REQUEST['UUPR_ORG']."')";
    }
    if(@$_REQUEST['UPR_ORG']){
        $whereSql .= " AND A.UPPR_ORG_CD = ?";
        $params[] = $_REQUEST['UPR_ORG'];
        $types .= "s";
    }
    if(@$_REQUEST['ORG_NM']){
        $whereSql .= " AND A.ORG_NM LIKE ?";
        $params[] = '%'.$_REQUEST['ORG_NM'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['ORG_TYPE']){
        $whereSql .= " AND A.ORG_TYPE = ?";
        $params[] = $_REQUEST['ORG_TYPE'];
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