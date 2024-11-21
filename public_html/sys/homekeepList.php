<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_HOUSEKEEP A";
    //기본 쿼리
    /* ((주근무시간+주휴시간(=주근무시간*4/20))*(365/7/12)) * 최저시급(10030) */
    $sql = "SELECT C.ORG_NM,B.PSNL_NM,D.POSITION,
    A.HKP_CD,A.HKP_DAY,A.HKP_HOUR,A.HKP_PERSON,
    FORMAT((CEIL((A.HKP_HOUR+(A.HKP_HOUR*4/20))*(365/7/12))*10030+((A.HKP_PERSON-1)*200000)),0) AS HKP_PAY
    FROM BONDANG_HR.PSNL_HOUSEKEEP A 
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
    LEFT OUTER JOIN ORG_INFO C ON B.ORG_CD = C.ORG_CD
    LEFT OUTER JOIN PSNL_TRANSFER D ON A.PSNL_CD = D.PSNL_CD AND D.TRS_DT = (SELECT MAX(TRS_DT) FROM PSNL_TRANSFER WHERE PSNL_CD = A.PSNL_CD)";
    //조건문 지정
    $whereSql = " WHERE 1=1"; //" WHERE PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    if(@$_REQUEST['PSNL_CD']){
        $whereSql=$whereSql." AND A.PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    }
    if(@$_REQUEST['HKP_DAY']){
        $whereSql=$whereSql." AND HKP_DAY = '".$_REQUEST['HKP_DAY']."'";
    }
    if(@$_REQUEST['HKP_HOUR']){
        $whereSql=$whereSql." AND HKP_HOUR = '".$_REQUEST['HKP_HOUR']."'";
    }
    if(@$_REQUEST['HKP_PERSON']){
        $whereSql=$whereSql." AND HKP_PERSON = '".$_REQUEST['HKP_PERSON']."'";
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