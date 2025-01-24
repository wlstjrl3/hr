<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증

    $inputs = file_get_contents("php://input");
    $param = json_decode($inputs, true); // JSON을 PHP 배열로 변환
    //동일한 테이블에서 조회한 값을 다시 INSERT로 즉시 사용하는 것은 불가능함. 때문에 미리 SELECT처리를 한 뒤에 두번째 연결에서 INSERT 하는 방식으로 구현
    $values = [];
    if($_REQUEST['CRUD']=="C"){
        foreach ($param['psnlCd'] as $psnl_cd) {
            // 최신 GRD_GRADE와 GRD_PAY 조회
            $query = "SELECT GRD_GRADE, GRD_PAY FROM BONDANG_HR.GRADE_HISTORY 
                    WHERE PSNL_CD = '$psnl_cd' 
                    ORDER BY ADVANCE_DT DESC LIMIT 1";
            $result = mysqli_query($conn, $query);
            if ($result && $row = mysqli_fetch_assoc($result)) {
                $grd_grade = $row['GRD_GRADE'];
                $grd_pay = $row['GRD_PAY'] + 1;
                $advance_dt = @$param['date'];
                $values[] = "('$psnl_cd', '$advance_dt', '$grd_grade', '$grd_pay', '일괄 호봉 갱신처리', NOW())";
            }
        }

        if (!empty($values)) {
            $sql = "INSERT INTO BONDANG_HR.GRADE_HISTORY (PSNL_CD, ADVANCE_DT, GRD_GRADE, GRD_PAY, GRD_DTL, REG_DT) VALUES " . implode(',', $values);
            if (mysqli_query($conn, $sql)) {
                $affectedRows = mysqli_affected_rows($conn);
                echo $affectedRows . "건의 데이터 일괄처리 완료";
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        }
    }else if($_REQUEST['CRUD']=="D"){
        $sql = "DELETE FROM GRADE_HISTORY WHERE GRD_CD IN (" . implode(',', $param['grdCd']) .")";
        if(mysqli_query($conn, $sql)){
            $affectedRows = mysqli_affected_rows($conn);
            echo $affectedRows . "건의 데이터 일괄삭제 완료";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
    mysqli_close($conn);
?>