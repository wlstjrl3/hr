<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
    if($_REQUEST['CRUD']=='C'){
        if($_REQUEST['SLR_CD']==""){ //신규 작성
            $sql = "INSERT INTO BONDANG_HR.SALARY_TB(SLR_YEAR, SLR_TYPE, SLR_GRADE, SLR_PAY, NORMAL_PAY, LEGAL_PAY, REG_DT) VALUES ('";
            $sql = $sql.$_REQUEST['SLR_YEAR']."','".$_REQUEST['SLR_TYPE']."','".$_REQUEST['SLR_GRADE']."','".$_REQUEST['SLR_PAY']."','".$_REQUEST['NORMAL_PAY']."','".$_REQUEST['LEGAL_PAY'];
            $sql = $sql."','".date("Y-m-d h:m:s")."')";
            //echo $sql; //오류 점검용 쿼리
        }else{ //기존 데이터 UPDATE
            $sql = "UPDATE BONDANG_HR.SALARY_TB SET 
                SLR_YEAR='".$_REQUEST['SLR_YEAR']."'
                ,SLR_TYPE='".$_REQUEST['SLR_TYPE']."'
                ,SLR_GRADE='".$_REQUEST['SLR_GRADE']."'
                ,SLR_PAY='".$_REQUEST['SLR_PAY']."'
                ,NORMAL_PAY='".$_REQUEST['NORMAL_PAY']."'
                ,LEGAL_PAY='".$_REQUEST['LEGAL_PAY']."'
                ,REG_DT='".date("Y-m-d h:m:s")."'
                WHERE SLR_CD = '".$_REQUEST['SLR_CD']."'";
        }
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else if($_REQUEST['CRUD']=='R'){
        //기본 쿼리
        $sql = "SELECT * FROM BONDANG_HR.SALARY_TB";
        //조건문 지정
        $whereSql = " WHERE 1=1 ";
        if(@$_REQUEST['SLR_CD']){
            $whereSql=$whereSql." AND SLR_CD = '".$_REQUEST['SLR_CD']."'";
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
        $sql = "DELETE FROM BONDANG_HR.SALARY_TB WHERE SLR_CD = '".$_REQUEST['SLR_CD']."'";
        //echo $sql; //오류 점검용 쿼리
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else if($_REQUEST['CRUD']=='BD' && strlen($_REQUEST['REG_DT']) == 10){ //일괄 제거 코드를 추가한다.
        //기본 쿼리
        $sql = "DELETE FROM BONDANG_HR.SALARY_TB WHERE REG_DT LIKE '".$_REQUEST['REG_DT']." %'";
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