<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_ADJUST A";
    //기본 쿼리
    $sql = "SELECT C.ORG_NM,B.PSNL_NM,A.* FROM BONDANG_HR.PSNL_ADJUST A
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
    LEFT OUTER JOIN PSNL_TRANSFER D ON D.TRS_CD = (SELECT TRS_CD FROM PSNL_TRANSFER WHERE PSNL_CD = A.PSNL_CD ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1)
    LEFT OUTER JOIN ORG_INFO C ON D.ORG_CD = C.ORG_CD";
    //조건문 지정
    $whereSql = " WHERE 1=1"; //" WHERE PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    $params = [];
    $types = "";
    if(@$_REQUEST['PSNL_CD']){
        $whereSql .= " AND A.PSNL_CD = ?";
        $params[] = @$_REQUEST['PSNL_CD'];
        $types .= "s";
    }
    if(@$_REQUEST['ADJ_TYPE']){
        $whereSql .= " AND ADJ_TYPE LIKE ?";
        $params[] = '%'.$_REQUEST['ADJ_TYPE'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['ADJ_NM']){
        $whereSql .= " AND ADJ_NM LIKE ?";
        $params[] = '%'.$_REQUEST['ADJ_NM'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['ADJ_NUM']){
        $whereSql .= " AND ADJ_NUM LIKE ?";
        $params[] = '%'.$_REQUEST['ADJ_NUM'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['ADJ_LEVEL']){
        $whereSql .= " AND ADJ_LEVEL LIKE ?";
        $params[] = '%'.$_REQUEST['ADJ_LEVEL'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['ADJ_GET_DT']){
        $whereSql .= " AND ADJ_GET_DT LIKE ?";
        $params[] = '%'.$_REQUEST['ADJ_GET_DT'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['ADJ_DTL']){
        $whereSql .= " AND ADJ_DTL LIKE ?";
        $params[] = '%'.$_REQUEST['ADJ_DTL'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['ADJ_PAY_From']){
        $whereSql .= " AND ADJ_PAY >= ?";
        $params[] = $_REQUEST['ADJ_PAY_From'];
        $types .= "s";
    }
    if(@$_REQUEST['ADJ_PAY_To']){
        $whereSql .= " AND ADJ_PAY <= ?";
        $params[] = $_REQUEST['ADJ_PAY_To'];
        $types .= "s";
    }
    if(@$_REQUEST['ADJ_STT_DT_From']){
        $whereSql .= " AND ADJ_STT_DT >= ?";
        $params[] = $_REQUEST['ADJ_STT_DT_From'];
        $types .= "s";
    }
    if(@$_REQUEST['ADJ_STT_DT_To']){
        $whereSql .= " AND ADJ_STT_DT <= ?";
        $params[] = $_REQUEST['ADJ_STT_DT_To'];
        $types .= "s";
    }
    if(@$_REQUEST['ADJ_END_DT_From']){
        $whereSql .= " AND ADJ_END_DT >= ?";
        $params[] = $_REQUEST['ADJ_END_DT_From'];
        $types .= "s";
    }
    if(@$_REQUEST['ADJ_END_DT_To']){
        $whereSql .= " AND ADJ_END_DT <= ?";
        $params[] = $_REQUEST['ADJ_END_DT_To'];
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