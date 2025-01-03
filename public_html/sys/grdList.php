<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.GRADE_HISTORY A";
    //기본 쿼리
    $sql = "SELECT C.ORG_NM,B.PSNL_NM,D.POSITION,A.* FROM BONDANG_HR.GRADE_HISTORY A 
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
    LEFT OUTER JOIN PSNL_TRANSFER D ON D.TRS_CD = (SELECT MAX(TRS_CD) TRS_CD FROM PSNL_TRANSFER WHERE PSNL_CD=A.PSNL_CD)
    LEFT OUTER JOIN ORG_INFO C ON D.ORG_CD = C.ORG_CD
    ";
    //조건문 지정
    $whereSql = " WHERE 1=1"; //" WHERE PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    if(@$_REQUEST['PSNL_CD']){
        $whereSql=$whereSql." AND A.PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    }
    if(@$_REQUEST['ADVANCE_DT_From']){
        $whereSql=$whereSql." AND ADVANCE_DT >= '".$_REQUEST['ADVANCE_DT_From']."'";
    }
    if(@$_REQUEST['ADVANCE_DT_To']){
        $whereSql=$whereSql." AND ADVANCE_DT <= '".$_REQUEST['ADVANCE_DT_To']."'";
    }
    if(@$_REQUEST['GRD_GRADE']){
        $whereSql=$whereSql." AND GRD_GRADE LIKE '%".$_REQUEST['GRD_GRADE']."%'";
    }
    if(@$_REQUEST['GRD_PAY']){
        $whereSql=$whereSql." AND GRD_PAY LIKE '%".$_REQUEST['GRD_PAY']."%'";
    }
    if(@$_REQUEST['GRD_DTL']){
        $whereSql=$whereSql." AND GRD_DTL = '".$_REQUEST['GRD_DTL']."'";
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