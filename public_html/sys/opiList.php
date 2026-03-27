<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_OPINION A";
    //기본 쿼리
    $sql = "SELECT C.ORG_NM,B.PSNL_NM,A.*,D.POSITION,
    CASE 
    WHEN OPI_TYPE=1 THEN '긍정' 
    WHEN OPI_TYPE=2 THEN '부정' 
    WHEN OPI_TYPE=3 THEN '포상' 
    WHEN OPI_TYPE=4 THEN '징계' END AS OPI_TYPE_KOR
     FROM BONDANG_HR.PSNL_OPINION A 
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
    LEFT OUTER JOIN PSNL_TRANSFER D ON D.TRS_CD = (SELECT TRS_CD FROM PSNL_TRANSFER WHERE PSNL_CD=A.PSNL_CD ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1)
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
    if(@$_REQUEST['OPI_DT_From']){
        $whereSql .= " AND OPI_DT >= ?";
        $params[] = $_REQUEST['OPI_DT_From'];
        $types .= "s";
    }
    if(@$_REQUEST['OPI_DT_To']){
        $whereSql .= " AND OPI_DT <= ?";
        $params[] = $_REQUEST['OPI_DT_To'];
        $types .= "s";
    }
    if(@$_REQUEST['OPI_PERSON']){
        $whereSql .= " AND OPI_PERSON LIKE ?";
        $params[] = '%'.$_REQUEST['OPI_PERSON'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['OPI_DTL']){
        $whereSql .= " AND OPI_DTL LIKE ?";
        $params[] = '%'.$_REQUEST['OPI_DTL'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['OPI_TYPE']){
        $whereSql .= " AND OPI_TYPE = ?";
        $params[] = $_REQUEST['OPI_TYPE'];
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