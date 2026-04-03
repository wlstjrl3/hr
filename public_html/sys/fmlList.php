<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_FAMILY A
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD";
    //기본 쿼리
    $sql = "SELECT C.ORG_NM,B.PSNL_NM,A.*,D.POSITION FROM BONDANG_HR.PSNL_FAMILY A
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
    LEFT OUTER JOIN PSNL_TRANSFER D ON D.TRS_CD = (
        SELECT TRS_CD FROM PSNL_TRANSFER AS D2
            WHERE D2.PSNL_CD = A.PSNL_CD
            ORDER BY TRS_DT DESC, TRS_CD DESC
            LIMIT 1
    )
    LEFT OUTER JOIN ORG_INFO C ON D.ORG_CD = C.ORG_CD
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
    if(@$_REQUEST['PSNL_NM']){
        $whereSql .= " AND B.PSNL_NM LIKE ?";
        $params[] = '%'.@$_REQUEST['PSNL_NM'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['FML_NM']){
        $whereSql .= " AND FML_NM LIKE ?";
        $params[] = '%'.$_REQUEST['FML_NM'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['FML_RELATION']){
        $whereSql .= " AND FML_RELATION LIKE ?";
        $params[] = '%'.$_REQUEST['FML_RELATION'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['FML_BIRTH']){
        $whereSql .= " AND FML_BIRTH LIKE ?";
        $params[] = '%'.$_REQUEST['FML_BIRTH'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['FML_DTL']){
        $whereSql .= " AND FML_DTL LIKE ?";
        $params[] = '%'.$_REQUEST['FML_DTL'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['FML_PAY_From']){
        $whereSql .= " AND FML_PAY >= ?";
        $params[] = $_REQUEST['FML_PAY_From'];
        $types .= "s";
    }
    if(@$_REQUEST['FML_PAY_To']){
        $whereSql .= " AND FML_PAY <= ?";
        $params[] = $_REQUEST['FML_PAY_To'];
        $types .= "s";
    }
    if(@$_REQUEST['FML_STT_DT_From']){
        $whereSql .= " AND FML_STT_DT >= ?";
        $params[] = $_REQUEST['FML_STT_DT_From'];
        $types .= "s";
    }
    if(@$_REQUEST['FML_STT_DT_To']){
        $whereSql .= " AND FML_STT_DT <= ?";
        $params[] = $_REQUEST['FML_STT_DT_To'];
        $types .= "s";
    }
    if(@$_REQUEST['FML_END_DT_From']){
        $whereSql .= " AND FML_END_DT >= ?";
        $params[] = $_REQUEST['FML_END_DT_From'];
        $types .= "s";
    }
    if(@$_REQUEST['FML_END_DT_To']){
        $whereSql .= " AND FML_END_DT <= ?";
        $params[] = $_REQUEST['FML_END_DT_To'];
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