<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증

    $inputs = file_get_contents("php://input");
    $param = json_decode($inputs, true); // JSON을 PHP 배열로 변환
    //$param = json_decode(json_encode(json_decode($inputs)),true); //json 디코드로 껍질을 풀어도 속에 Object형이 남아있어서 다시 인코드 후 디코드를 하여 array형태로 변환

    $sql = "INSERT INTO BONDANG_HR.GRADE_HISTORY(PSNL_CD, ADVANCE_DT, GRD_GRADE, GRD_PAY, GRD_DTL, REG_DT) VALUES ";
    
    foreach($param['psnlCd'] as $key => $row){
        $sql=$sql."('".@$row."','".@$param['date']."',
        (SELECT GRD_GRADE FROM BONDANG_HR.GRADE_HISTORY WHERE PSNL_CD='".@$row."' ORDER BY ADVANCE_DT DESC LIMIT 1),
        (SELECT GRD_PAY+1 FROM BONDANG_HR.GRADE_HISTORY WHERE PSNL_CD='".@$row."' ORDER BY ADVANCE_DT DESC LIMIT 1),
        '일괄 호봉 갱신처리','".date("Y-m-d h:m:s")."'),";
    }
    $sql = substr($sql, 0, -1); //마지막 자리 , 삭제
    echo $sql;
    //echo json_encode($row, JSON_UNESCAPED_UNICODE);
    mysqli_query($conn,$sql);
    
    mysqli_close($conn);
?>