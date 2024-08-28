<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    //if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_INFO A LEFT OUTER JOIN ORG_INFO B ON A.ORG_CD = B.ORG_CD";
    //기본 쿼리
    $sql = "SELECT PSNL_CD,PSNL_NM,BAPT_NM,ORG_NM,POSITION FROM BONDANG_HR.PSNL_INFO A LEFT OUTER JOIN ORG_INFO B ON A.ORG_CD = B.ORG_CD";
    //조건문 지정
    $whereSql = " WHERE 1=1 ";
    
    if(@$_REQUEST['PSNL_NM']){
        $whereSql=$whereSql." AND PSNL_NM LIKE '%".$_REQUEST['PSNL_NM']."%'";
    }
    if(@$_REQUEST['BAPT_NM']){
        $whereSql=$whereSql." AND BAPT_NM LIKE '%".$_REQUEST['BAPT_NM']."%'";
    }    
    if(@$_REQUEST['ORG_NM']){
        $whereSql=$whereSql." AND ORG_NM LIKE '%".$_REQUEST['ORG_NM']."%'";
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
       ,"date" => "2021-99-99"
       ,"totalCnt" => $totalCnt["ROW_CNT"]
       ,"filterCnt" => $filterCnt["ROW_CNT"]
    ); 

    echo json_encode($datas, JSON_UNESCAPED_UNICODE);

?>