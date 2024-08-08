<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_ID = '".@$_REQUEST['key']."' LIMIT 1"))<1){echo "인증에러"; die;} //보안 검증

    $req = file_get_contents("php://input");
    $param = json_decode(json_encode(json_decode($req)),true); //json 디코드로 껍질을 풀어도 속에 Object형이 남아있어서 다시 인코드 후 디코드를 하여 array형태로 변환

    $sql = "INSERT INTO BONDANG_HR.USER_TB (USER_ID,USER_NM,USER_PASS,EMAIL,MEMO,REG_DT) VALUES ";
    foreach($param as $key => $row){
        //idx	사용자ID	사용자성명	비밀번호	이메일	메모
        $sql=$sql."('".$row['사용자ID']."','".$row['사용자성명']."','".$row['비밀번호']."','".$row['이메일']."','".$row['메모']."','".date("Y-m-d H:i:s")."'),";
    }
    $sql = substr($sql, 0, -1); //마지막 자리 , 삭제
    
    //echo $sql;
    //echo json_encode($row, JSON_UNESCAPED_UNICODE);
    mysqli_query($conn,$sql);
    mysqli_close($conn);
?>