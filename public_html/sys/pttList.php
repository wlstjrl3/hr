<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM BONDANG_HR.PSNL_PARTTIME A";
    //기본 쿼리
    $sql = "SELECT C.ORG_NM,B.PSNL_NM,D.POSITION,
    A.PTT_CD,A.PTT_YEAR,A.PTT_DAY,A.PTT_HOUR,A.PTT_ADDHOUR,A.PTT_ADJ,A.PTT_ADJPAY    
    /*
    21~24년까지 실제 지급된 급여가 최저임금에 미달하여 보정지급된 경우가 많아 기존 수식이 의미없음.. 
    24년 기준 급여계산 (주간근로시간+(주간근로시간/주간근무일수)*52+(주간근로시간/주간근무일수)/12 
    25년 기준 급여계산 ((주근무시간+주휴시간(=주근무시간*4/20))*(365/7/12)) * 최저시급(E.LEGAL_PAY) 
    ,FORMAT(CEIL((CEIL(((A.PTT_HOUR+(A.PTT_HOUR/A.PTT_DAY))*52+(A.PTT_HOUR/A.PTT_DAY))/12)*E.LEGAL_PAY+A.PTT_ADJPAY)/100)*100,0) AS PTT_TOTALPAY2023
    ,FORMAT(CEIL(((A.PTT_HOUR+(A.PTT_HOUR/A.PTT_DAY))*52+(A.PTT_HOUR/A.PTT_DAY))/12)*E.LEGAL_PAY+A.PTT_ADJPAY,0) AS PTT_TOTALPAY2024
    */
    ,FORMAT((CEIL((A.PTT_HOUR+(A.PTT_HOUR*4/20))*(365/7/12))*E.LEGAL_PAY+A.PTT_ADJPAY),0) AS PTT_TOTALPAY
    /*1주간 연장근로 시간 * 4.345 * 최저시급 * 1.5 = 연장수당합*/
    ,FORMAT(A.PTT_ADDHOUR*4.345*E.LEGAL_PAY*1.5,0) AS PTT_ADDPAY
    ,FORMAT(CEIL(A.PTT_ADDHOUR*4.345*E.LEGAL_PAY*1.5/10)*10+(CEIL((A.PTT_HOUR+(A.PTT_HOUR*4/20))*(365/7/12))*E.LEGAL_PAY+A.PTT_ADJPAY),0) AS PTT_TOTALADDPAY
    FROM BONDANG_HR.PSNL_PARTTIME A 
    LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
    LEFT OUTER JOIN PSNL_TRANSFER D ON A.PSNL_CD = D.PSNL_CD AND D.TRS_DT = (SELECT MAX(TRS_DT) FROM PSNL_TRANSFER WHERE PSNL_CD = A.PSNL_CD)
    LEFT OUTER JOIN ORG_INFO C ON D.ORG_CD = C.ORG_CD
    LEFT OUTER JOIN SALARY_TB E ON A.PTT_YEAR = E.SLR_YEAR AND E.SLR_TYPE = '최저시급'
    ";
    //조건문 지정
    $whereSql = " WHERE 1=1"; //" WHERE PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    if(@$_REQUEST['PSNL_CD']){
        $whereSql=$whereSql." AND A.PSNL_CD='".@$_REQUEST['PSNL_CD']."'";
    }
    if(@$_REQUEST['PTT_YEAR']){
        $whereSql=$whereSql." AND PTT_YEAR = '".$_REQUEST['PTT_YEAR']."'";
    }
    if(@$_REQUEST['PTT_DAY']){
        $whereSql=$whereSql." AND PTT_DAY = '".$_REQUEST['PTT_DAY']."'";
    }
    if(@$_REQUEST['PTT_HOUR']){
        $whereSql=$whereSql." AND PTT_HOUR = '".$_REQUEST['PTT_HOUR']."'";
    }
    if(@$_REQUEST['PTT_ADJPAY']){
        $whereSql=$whereSql." AND PTT_ADJPAY = '".$_REQUEST['PTT_ADJPAY']."'";
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