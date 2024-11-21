<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.SALARY_TB";
    //기본 쿼리
    $sql = "SELECT * FROM BONDANG_HR.SALARY_TB";
    //조건문 지정
    $whereSql = " WHERE 1=1 ";
    if(@$_REQUEST['SLR_YEAR']){
        $whereSql=$whereSql." AND SLR_YEAR LIKE '%".$_REQUEST['SLR_YEAR']."%'"; //조직 정보의 B테이블에서 가져온다.
    }
    if(@$_REQUEST['SLR_GRADE']){
        $whereSql=$whereSql." AND SLR_GRADE LIKE '%".$_REQUEST['SLR_GRADE']."%'";
    }
    if(@$_REQUEST['SLR_PAY']){
        $whereSql=$whereSql." AND SLR_PAY LIKE '%".$_REQUEST['SLR_PAY']."%'";
    }
    if(@$_REQUEST['NORMAL_PAY_From']){
        $whereSql=$whereSql." AND (NORMAL_PAY >= ".$_REQUEST['NORMAL_PAY_From'].")";
    }
    if(@$_REQUEST['NORMAL_PAY_To']){
        $whereSql=$whereSql." AND (NORMAL_PAY <= ".$_REQUEST['NORMAL_PAY_To'].")";
    }
    if(@$_REQUEST['LEGAL_PAY_From']){
        $whereSql=$whereSql." AND (LEGAL_PAY >= ".$_REQUEST['LEGAL_PAY_From'].")";
    }
    if(@$_REQUEST['LEGAL_PAY_To']){
        $whereSql=$whereSql." AND (LEGAL_PAY <= ".$_REQUEST['LEGAL_PAY_To'].")";
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