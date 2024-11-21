<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_INSURANCE A";
    //기본 쿼리
    $sql = "SELECT C.ORG_NM,B.PSNL_NM,A.*,P.POSITION FROM BONDANG_HR.PSNL_INSURANCE A 
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
    LEFT OUTER JOIN ORG_INFO C ON B.ORG_CD = C.ORG_CD
    LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
        SELECT TRS_CD FROM PSNL_TRANSFER AS P2
        WHERE P2.PSNL_CD = A.PSNL_CD
        ORDER BY P2.REG_DT DESC
        LIMIT 1
    )
    ";
    /*LEFT OUTER JOIN (SELECT PSNL_) P ON A.PSNL_CD = P.PSNL_CD*/

    //조건문 지정
    $whereSql = " WHERE 1=1"; //" WHERE PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    if(@$_REQUEST['PSNL_CD']){
        $whereSql=$whereSql." AND A.PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    }
    if(@$_REQUEST['INS_AMOUNT_From']){
        $whereSql=$whereSql." AND INS_AMOUNT >= '".$_REQUEST['INS_AMOUNT_From']."'";
    }
    if(@$_REQUEST['INS_AMOUNT_To']){
        $whereSql=$whereSql." AND INS_AMOUNT <= '".$_REQUEST['INS_AMOUNT_To']."'";
    }
    if(@$_REQUEST['INS_STT_DT_From']){
        $whereSql=$whereSql." AND INS_STT_DT >= '".$_REQUEST['INS_STT_DT_From']."'";
    }
    if(@$_REQUEST['INS_STT_DT_To']){
        $whereSql=$whereSql." AND INS_STT_DT <= '".$_REQUEST['INS_STT_DT_To']."'";
    }
    if(@$_REQUEST['INS_END_DT_From']){
        $whereSql=$whereSql." AND INS_END_DT >= '".$_REQUEST['INS_END_DT_From']."'";
    }
    if(@$_REQUEST['INS_END_DT_To']){
        $whereSql=$whereSql." AND INS_END_DT <= '".$_REQUEST['INS_END_DT_To']."'";
    }
    if(@$_REQUEST['INS_DTL']){
        $whereSql=$whereSql." AND INS_DTL LIKE '%".$_REQUEST['INS_DTL']."%'";
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