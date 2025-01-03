<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //기본 쿼리
    $sql = "";
    for($i=1;$i<=12;$i++){
        $loopMonth = substr(('0'.$i),-2);
        $sql = $sql."
SELECT 
	CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."') AS YEAR_MON
    ,A2.WORK_TYPE
    ,A.ADVANCE_DT
    ,A.GRD_GRADE
    ,A.GRD_PAY
    ,B.NORMAL_PAY
    ,B.LEGAL_PAY
    ,IFNULL((SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE 
        PSNL_CD = ".@$_REQUEST['PSNL_CD']." 
        AND ADJ_TYPE = '직책' 
        AND ADJ_STT_DT <= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-31') 
        AND (ADJ_END_DT >= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-01') OR ADJ_END_DT is null)),0) AS POSI_PAY

    ,IFNULL((SELECT SUM(FML_PAY) FROM PSNL_FAMILY WHERE PSNL_CD = ".@$_REQUEST['PSNL_CD']." AND FML_STT_DT <= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-31') AND (FML_END_DT >= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-01') OR FML_END_DT is null)),0) AS FML_PAY
    ,IFNULL((SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE PSNL_CD = ".@$_REQUEST['PSNL_CD']." AND ADJ_TYPE = '자격' AND ADJ_STT_DT <= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-31') AND (ADJ_END_DT >= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-01') OR ADJ_END_DT is null)),0) AS LCS_PAY
    ,IFNULL((SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE PSNL_CD = ".@$_REQUEST['PSNL_CD']." AND ADJ_TYPE = '장애' AND ADJ_STT_DT <= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-31') AND (ADJ_END_DT >= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-01') OR ADJ_END_DT is null)),0) AS DIS_PAY
    ,IFNULL((SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE PSNL_CD = ".@$_REQUEST['PSNL_CD']." AND ADJ_TYPE = '조정' AND ADJ_STT_DT <= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-31') AND (ADJ_END_DT >= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-01') OR ADJ_END_DT is null)),0) AS ADJ_PAY
	,B.NORMAL_PAY+B.LEGAL_PAY+IFNULL((SELECT SUM(FML_PAY) FROM PSNL_FAMILY WHERE PSNL_CD = ".@$_REQUEST['PSNL_CD']." AND FML_STT_DT <= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-31') AND (FML_END_DT >= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-01') OR FML_END_DT is null)),0)+IFNULL((SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE PSNL_CD = ".@$_REQUEST['PSNL_CD']." AND ADJ_STT_DT <= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-31') AND (ADJ_END_DT >= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-01') OR ADJ_END_DT is null)),0) AS TOTAL_PAY
    FROM GRADE_HISTORY A
    LEFT OUTER JOIN PSNL_TRANSFER A2 ON TRS_CD = (SELECT TRS_CD FROM PSNL_TRANSFER WHERE PSNL_CD=".@$_REQUEST['PSNL_CD']." AND TRS_DT <= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-','".$loopMonth."','-31') ORDER BY TRS_DT DESC LIMIT 1)
    LEFT OUTER JOIN SALARY_TB B ON GRD_GRADE = SLR_GRADE AND GRD_PAY = SLR_PAY AND SLR_YEAR = ".@$_REQUEST['MPAY_YEAR']." AND SLR_TYPE = A2.WORK_TYPE
WHERE A.PSNL_CD = ".@$_REQUEST['PSNL_CD']."
	AND A.ADVANCE_DT > CONCAT((".@$_REQUEST['MPAY_YEAR']."-1),'-".$loopMonth."-01') AND A.ADVANCE_DT <= CONCAT(".@$_REQUEST['MPAY_YEAR'].",'-".$loopMonth."-01')
        ";
        if($i<12){
            $sql = $sql."
                UNION
            ";
        }
    }
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    while($row = mysqli_fetch_assoc($result)){
        $data[] = $row;
    }
    $datas = array(
       "data" => @$data
    ); 

    echo json_encode($datas, JSON_UNESCAPED_UNICODE);

?>