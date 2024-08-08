<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_INFO A INNER JOIN BONDANG_HR.ORG_INFO B ON A.ORG_CD = B.ORG_CD ";
    //기본 쿼리
    $sql = "SELECT B.ORG_NM, A.* FROM BONDANG_HR.PSNL_INFO A INNER JOIN BONDANG_HR.ORG_INFO B ON A.ORG_CD = B.ORG_CD ";
    //조건문 지정
    $whereSql = " WHERE 1=1 ";
    if(@$_REQUEST['ORG_NM']){
        $whereSql=$whereSql." AND ORG_NM LIKE '%".$_REQUEST['ORG_NM']."%'"; //조직 정보의 B테이블에서 가져온다.
    }
    if(@$_REQUEST['PSNL_NM']){
        $whereSql=$whereSql." AND PSNL_NM LIKE '%".$_REQUEST['PSNL_NM']."%'";
    }
    if(@$_REQUEST['BAPT_NM']){
        $whereSql=$whereSql." AND BAPT_NM LIKE '%".$_REQUEST['BAPT_NM']."%'";
    }
    if(@$_REQUEST['POSITION']){
        $whereSql=$whereSql." AND POSITION LIKE '%".$_REQUEST['POSITION']."%'";
    }
    if(@$_REQUEST['WORK_TYPE']){
        $whereSql=$whereSql." AND WORK_TYPE LIKE '%".$_REQUEST['WORK_TYPE']."%'";
    }
    if(@$_REQUEST['JOIN_DT_From']){
        $whereSql=$whereSql." AND JOIN_DT >= '".$_REQUEST['JOIN_DT_From']." 00:00:00'";
    }
    if(@$_REQUEST['JOIN_DT_To']){
        $whereSql=$whereSql." AND JOIN_DT <= '".$_REQUEST['JOIN_DT_To']." 23:59:59'";
    }
    if(@$_REQUEST['QUIT_DT_From']){
        $whereSql=$whereSql." AND QUIT_DT >= '".$_REQUEST['QUIT_DT_From']." 00:00:00'";
    }
    if(@$_REQUEST['QUIT_DT_To']){
        $whereSql=$whereSql." AND QUIT_DT <= '".$_REQUEST['QUIT_DT_To']." 23:59:59'";
    }
    if(@$_REQUEST['PHONE_NUM']){
        $whereSql=$whereSql." AND PHONE_NUM LIKE '%".$_REQUEST['PHONE_NUM']."%'";
    }
    if(@$_REQUEST['PSNL_EMAIL']){
        $whereSql=$whereSql." AND PSNL_EMAIL LIKE '%".$_REQUEST['PSNL_EMAIL']."%'";
    }
    if(@$_REQUEST['FEAST']){
        $whereSql=$whereSql." AND FEAST LIKE '%".$_REQUEST['FEAST']."%'";
    }
    if(@$_REQUEST['PSNL_BIRTH_From']){ //PSNL_NUM을 날짜형식으로 변경하여 비교해야함.
        $whereSql=$whereSql." AND PSNL_NUM >= '".$_REQUEST['PSNL_BIRTH']." 00:00:00'";
    }
    if(@$_REQUEST['PSNL_BIRTH_To']){ //PSNL_NUM을 날짜형식으로 변경하여 비교해야함.
        $whereSql=$whereSql." AND PSNL_NUM <= '".$_REQUEST['PSNL_BIRTH_To']." 23:59:59'";
    }
    if(@$_REQUEST['PSNL_NUM']){
        $whereSql=$whereSql." AND PSNL_NUM LIKE '%".$_REQUEST['PSNL_NUM']."%'";
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