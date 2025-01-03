<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증

    $req = file_get_contents("php://input");
    $param = json_decode(json_encode(json_decode($req)),true); //json 디코드로 껍질을 풀어도 속에 Object형이 남아있어서 다시 인코드 후 디코드를 하여 array형태로 변환

    $sql = "INSERT INTO BONDANG_HR.ORG_HISTORY(OH_DT, ORG_CD, PERSON_CNT, ETC, REG_DT) VALUES ";
    foreach($param as $key => $row){
        if(strlen($row['OH_DT'])!=10){
            $sql=$sql."('".@excelDateToPHPDate($row['OH_DT']);
        }else{
            $sql=$sql."('".@$row['OH_DT'];
        }
        $sql=$sql."','".@$row['ORG_NM']."','".@$row['PERSON_CNT']."','".@$row['ETC']."','".date("Y-m-d h:m:s")."'),";
    }
    $sql = substr($sql, 0, -1); //마지막 자리 , 삭제
    //echo $sql;
    //echo json_encode($row, JSON_UNESCAPED_UNICODE);
    mysqli_query($conn,$sql);
    $sql2 = "UPDATE BONDANG_HR.ORG_HISTORY A LEFT OUTER JOIN ORG_INFO B ON A.ORG_CD = B.ORG_NM SET A.ORG_CD = B.ORG_CD WHERE A.ORG_CD NOT LIKE '1311%'";
    mysqli_query($conn,$sql2);

    mysqli_close($conn);

    function excelDateToPHPDate($excelDate) { /* 엑셀에서 넘어온 자료의 날짜가 숫자로 변환된 경우 재보정 용코드 */
        // 엑셀 기준 날짜: 1900-01-01
        $baseDate = new DateTime('1900-01-01');
        // 엑셀 날짜는 1부터 시작, 1900년 2월 29일 오류 보정을 위해 -2일
        $interval = new DateInterval('P' . ($excelDate - 2) . 'D');
        $baseDate->add($interval);
        return $baseDate->format('Y-m-d');
    }    
?>