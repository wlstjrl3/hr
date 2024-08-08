<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.USER_TB";
    //기본 쿼리
    $sql = "SELECT * FROM BONDANG_HR.USER_TB";
    //조건문 지정
    $whereSql = " WHERE 1=1 ";
    if(@$_REQUEST['USER_ID']){
        $whereSql=$whereSql." AND USER_ID LIKE '%".$_REQUEST['USER_ID']."%'";
    }
    if(@$_REQUEST['USER_NM']){
        $whereSql=$whereSql." AND USER_NM LIKE '%".$_REQUEST['USER_NM']."%'";
    }
    if(@$_REQUEST['USER_PASS']){
        $whereSql=$whereSql." AND USER_PASS LIKE '%".$_REQUEST['USER_PASS']."%'";
    }
    if(@$_REQUEST['EMAIL']){
        $whereSql=$whereSql." AND EMAIL LIKE '%".$_REQUEST['EMAIL']."%'";
    }
    if(@$_REQUEST['MEMO']){
        $whereSql=$whereSql." AND MEMO LIKE '%".$_REQUEST['MEMO']."%'";
    }
    if(@$_REQUEST['REG_DT_From']){
        $whereSql=$whereSql." AND REG_DT >= '".$_REQUEST['REG_DT_From']." 00:00:00'";
    }
    if(@$_REQUEST['REG_DT_To']){
        $whereSql=$whereSql." AND REG_DT <= '".$_REQUEST['REG_DT_To']." 23:59:59'";
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