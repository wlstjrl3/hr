<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_LICENSE A";
    //기본 쿼리
    $sql = "SELECT C.ORG_NM,B.PSNL_NM,A.* FROM BONDANG_HR.PSNL_LICENSE A
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
    LEFT OUTER JOIN ORG_INFO C ON B.ORG_CD = C.ORG_CD";
    //조건문 지정
    $whereSql = " WHERE 1=1"; //" WHERE PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    if(@$_REQUEST['PSNL_CD']){
        $whereSql=$whereSql." AND A.PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    }
    if(@$_REQUEST['LCS_NUM']){
        $whereSql=$whereSql." AND LCS_NUM LIKE '%".$_REQUEST['LCS_NUM']."%'";
    }
    if(@$_REQUEST['LCS_NM']){
        $whereSql=$whereSql." AND LCS_NM LIKE '%".$_REQUEST['LCS_NM']."%'";
    }
    if(@$_REQUEST['LCS_LEVEL']){
        $whereSql=$whereSql." AND LCS_LEVEL LIKE '%".$_REQUEST['LCS_LEVEL']."%'";
    }
    if(@$_REQUEST['LCS_GET_DT']){
        $whereSql=$whereSql." AND LCS_GET_DT LIKE '%".$_REQUEST['LCS_GET_DT']."%'";
    }
    if(@$_REQUEST['LCS_DTL']){
        $whereSql=$whereSql." AND LCS_DTL LIKE '%".$_REQUEST['LCS_DTL']."%'";
    }
    if(@$_REQUEST['LCS_PAY_From']){
        $whereSql=$whereSql." AND LCS_PAY >= '".$_REQUEST['LCS_PAY_From']."'";
    }
    if(@$_REQUEST['LCS_PAY_To']){
        $whereSql=$whereSql." AND LCS_PAY <= '".$_REQUEST['LCS_PAY_To']."'";
    }
    if(@$_REQUEST['LCS_STT_DT_From']){
        $whereSql=$whereSql." AND LCS_STT_DT >= '".$_REQUEST['LCS_STT_DT_From']."'";
    }
    if(@$_REQUEST['LCS_STT_DT_To']){
        $whereSql=$whereSql." AND LCS_STT_DT <= '".$_REQUEST['LCS_STT_DT_To']."'";
    }
    if(@$_REQUEST['LCS_END_DT_From']){
        $whereSql=$whereSql." AND LCS_END_DT >= '".$_REQUEST['LCS_END_DT_From']."'";
    }
    if(@$_REQUEST['LCS_END_DT_To']){
        $whereSql=$whereSql." AND LCS_END_DT <= '".$_REQUEST['LCS_END_DT_To']."'";
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
       ,"query" => $sql.$whereSql.$orderSql.$limitSql
       ,"totalCnt" => $totalCnt["ROW_CNT"]
       ,"filterCnt" => $filterCnt["ROW_CNT"]
    ); 

    echo json_encode($datas, JSON_UNESCAPED_UNICODE);

?>