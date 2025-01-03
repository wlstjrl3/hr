<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.ORG_HISTORY A
    LEFT OUTER JOIN ORG_INFO B ON A.ORG_CD = B.ORG_CD
    ";
    //기본 쿼리
    $sql = "SELECT A.OH_CD,B.ORG_CD,B.ORG_NM,OH_DT,PERSON_CNT,ETC,A.REG_DT FROM BONDANG_HR.ORG_HISTORY A
    LEFT OUTER JOIN ORG_INFO B ON A.ORG_CD = B.ORG_CD
    ";
    //조건문 지정
    $whereSql = " WHERE 1=1"; //" WHERE PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    if(@$_REQUEST['PSNL_CD']){
        $whereSql=$whereSql." AND A.PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    }
    if(@$_REQUEST['OH_DT_From']){
        $whereSql=$whereSql." AND OH_DT >= '".$_REQUEST['OH_DT_From']."'";
    }
    if(@$_REQUEST['OH_DT_To']){
        $whereSql=$whereSql." AND OH_DT <= '".$_REQUEST['OH_DT_To']."'";
    }
    if(@$_REQUEST['PERSON_CNT_From']){
        $whereSql=$whereSql." AND PERSON_CNT >= '".$_REQUEST['PERSON_CNT_From']."'";
    }
    if(@$_REQUEST['PERSON_CNT_To']){
        $whereSql=$whereSql." AND PERSON_CNT <= '".$_REQUEST['PERSON_CNT_To']."'";
    }
    if(@$_REQUEST['ORG_NM']){
        $whereSql=$whereSql." AND ORG_NM LIKE '%".$_REQUEST['ORG_NM']."%'";
    }
    if(@$_REQUEST['ETC']){
        $whereSql=$whereSql." AND ETC LIKE '%".$_REQUEST['ETC']."%'";
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