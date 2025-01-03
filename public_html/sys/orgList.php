<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.ORG_INFO A LEFT OUTER JOIN BONDANG_HR.ORG_INFO B ON A.UPPR_ORG_CD = B.ORG_CD";
    //기본 쿼리
    $sql = "SELECT B.ORG_NM AS UPR_ORG_NM
        ,A.ORG_CD
        ,A.ORG_NM
        ,CASE 
        WHEN A.ORG_TYPE='1' THEN '성지' 
        WHEN A.ORG_TYPE='11' THEN '본당' 
        WHEN A.ORG_TYPE='9' THEN '지구' END AS ORG_TYPE
        ,A.REFRESH_DT
        FROM BONDANG_HR.ORG_INFO A LEFT OUTER JOIN BONDANG_HR.ORG_INFO B ON A.UPPR_ORG_CD = B.ORG_CD";
    //조건문 지정
    $whereSql = " WHERE 1=1 ";
    if(@$_REQUEST['UUPR_ORG']){
        $whereSql=$whereSql." AND (B.UPPR_ORG_CD = '".$_REQUEST['UUPR_ORG']."' OR A.UPPR_ORG_CD = '".$_REQUEST['UUPR_ORG']."')";
    }
    if(@$_REQUEST['UPR_ORG']){
        $whereSql=$whereSql." AND A.UPPR_ORG_CD = '".$_REQUEST['UPR_ORG']."'";
    }
    if(@$_REQUEST['ORG_NM']){
        $whereSql=$whereSql." AND A.ORG_NM LIKE '%".$_REQUEST['ORG_NM']."%'";
    }
    if(@$_REQUEST['ORG_TYPE']){
        $whereSql=$whereSql." AND A.ORG_TYPE = '".$_REQUEST['ORG_TYPE']."'";
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