<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    
    if($_REQUEST['CRUD']=='C'){
        if($_REQUEST['OH_CD']==""){ //신규 작성
            $sql = "INSERT INTO BONDANG_HR.ORG_HISTORY(OH_DT, ORG_CD, PERSON_CNT, ETC, REG_DT) VALUES ('";
            $sql = $sql.$_REQUEST['OH_DT']."','".$_REQUEST['ORG_CD']."','".$_REQUEST['PERSON_CNT']."','".$_REQUEST['ETC'];
            $sql = $sql."','".date("Y-m-d h:m:s")."')";
            //echo $sql; //오류 점검용 쿼리
        }else{ //기존 데이터 UPDATE
            $sql = "UPDATE BONDANG_HR.ORG_HISTORY SET 
                OH_DT='".$_REQUEST['OH_DT']."'
                ,ORG_CD='".$_REQUEST['ORG_CD']."'
                ,PERSON_CNT='".$_REQUEST['PERSON_CNT']."'
                ,ETC='".$_REQUEST['ETC']."'
                ,REG_DT='".date("Y-m-d h:m:s")."'
                WHERE OH_CD = '".$_REQUEST['OH_CD']."'";
        }
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else if($_REQUEST['CRUD']=='R'){
        //기본 쿼리
        $sql = "SELECT B.ORG_NM,A.* FROM BONDANG_HR.ORG_HISTORY A LEFT OUTER JOIN ORG_INFO B ON A.ORG_CD = B.ORG_CD";
        //조건문 지정
        $whereSql = " WHERE 1=1 ";
        if(@$_REQUEST['OH_CD']){
            $whereSql=$whereSql." AND OH_CD = '".$_REQUEST['OH_CD']."'";
        }
        //리미트 지정
        $limitSql = " LIMIT 1";
        $result = mysqli_query($conn,$sql.$whereSql.$limitSql);
        mysqli_close($conn);

        while($row = mysqli_fetch_assoc($result)){
            $data[] = $row;
        }
        $datas = array(
        "data" => @$data,
        "date" => "2021-99-99"
        );
        //echo $sql.$whereSql.$limitSql; //오류 점검용 쿼리
        echo json_encode($datas, JSON_UNESCAPED_UNICODE);
    }else if($_REQUEST['CRUD']=='D'){
        //기본 쿼리
        $sql = "DELETE FROM BONDANG_HR.ORG_HISTORY WHERE OH_CD = '".$_REQUEST['OH_CD']."'";
        //echo $sql; //오류 점검용 쿼리
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else if($_REQUEST['CRUD']=='BD' && strlen($_REQUEST['REG_DT']) == 10){ //일괄 제거 코드를 추가한다.
        //기본 쿼리
        $sql = "DELETE FROM BONDANG_HR.ORG_HISTORY WHERE REG_DT LIKE '".$_REQUEST['REG_DT']." %'";
        //echo $sql; //오류 점검용 쿼리
        $result = mysqli_query($conn,$sql);
        if ($result) {
            echo "영향을 받은 행의 수: " .mysqli_affected_rows($conn);
        } else {
            echo "쿼리 실행 실패: " .mysqli_error($conn);
        }
        mysqli_close($conn);
    }else{
        echo 'userConfig 잘못된 접근방식입니다.';
    }

?>