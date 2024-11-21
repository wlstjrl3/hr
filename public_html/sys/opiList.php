<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_OPINION A";
    //기본 쿼리
    $sql = "SELECT C.ORG_NM,B.PSNL_NM,A.*,
    CASE 
    WHEN OPI_TYPE=1 THEN '긍정' 
    WHEN OPI_TYPE=2 THEN '부정' 
    WHEN OPI_TYPE=3 THEN '포상' 
    WHEN OPI_TYPE=4 THEN '징계' END AS OPI_TYPE_KOR
     FROM BONDANG_HR.PSNL_OPINION A 
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
    LEFT OUTER JOIN ORG_INFO C ON B.ORG_CD = C.ORG_CD";
    //조건문 지정
    $whereSql = " WHERE 1=1"; //" WHERE PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    if(@$_REQUEST['PSNL_CD']){
        $whereSql=$whereSql." AND A.PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    }
    if(@$_REQUEST['OPI_DT_From']){
        $whereSql=$whereSql." AND OPI_DT >= '".$_REQUEST['OPI_DT_From']."'";
    }
    if(@$_REQUEST['OPI_DT_To']){
        $whereSql=$whereSql." AND OPI_DT <= '".$_REQUEST['OPI_DT_To']."'";
    }
    if(@$_REQUEST['OPI_PERSON']){
        $whereSql=$whereSql." AND OPI_PERSON LIKE '%".$_REQUEST['OPI_PERSON']."%'";
    }
    if(@$_REQUEST['OPI_DTL']){
        $whereSql=$whereSql." AND OPI_DTL LIKE '%".$_REQUEST['OPI_DTL']."%'";
    }
    if(@$_REQUEST['OPI_TYPE']){
        $whereSql=$whereSql." AND OPI_TYPE = '".$_REQUEST['OPI_TYPE']."'";
    }
    //정렬 기준 지정
    $orderSql = "";
    if(@$_REQUEST['ORDER']){
        $orderSql = $orderSql." ORDER BY ".$_REQUEST['ORDER'];
    }
    //리미트 지정
    $limitSql = "";
    if(@$_REQUEST['LIMIT']){
        $limitSql = $limitSql." LIMIT ".$_REQUEST['LIMIT'];
    }
    $totalCnt = mysqli_fetch_assoc(mysqli_query($conn,$rowCntSql));
    $filterCnt = mysqli_fetch_assoc(mysqli_query($conn,$rowCntSql.$whereSql));
    
    $result = mysqli_query($conn,$sql.$whereSql.$orderSql.$limitSql);
    mysqli_close($conn);

    while($row = mysqli_fetch_assoc($result)){
        $data[] = $row;
    }
    $datas = array(
       "data" => @$data
       //,"query" => $sql.$whereSql.$orderSql.$limitSql
       ,"totalCnt" => $totalCnt["ROW_CNT"]
       ,"filterCnt" => $filterCnt["ROW_CNT"]
    ); 

    echo json_encode($datas, JSON_UNESCAPED_UNICODE);

?>