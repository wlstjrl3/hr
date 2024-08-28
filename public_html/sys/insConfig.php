<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    
    if($_REQUEST['CRUD']=='C'){
        if($_REQUEST['INS_CD']==""){ //신규 작성
            $sql = "INSERT INTO BONDANG_HR.PSNL_INSURANCE(PSNL_CD,INS_AMOUNT,INS_STT_DT,INS_END_DT,INS_DTL,REG_DT) VALUES ('";
            $sql = $sql.$_REQUEST['PSNL_CD']."','".$_REQUEST['INS_AMOUNT']."','".$_REQUEST['INS_STT_DT']."','".$_REQUEST['INS_END_DT']."','".$_REQUEST['INS_DTL'];
            $sql = $sql."','".date("Y-m-d h:m:s")."')";
            echo $sql; //오류 점검용 쿼리
        }else{ //기존 데이터 UPDATE
            $sql = "UPDATE BONDANG_HR.PSNL_INSURANCE SET 
                PSNL_CD='".$_REQUEST['PSNL_CD']."'
                ,INS_AMOUNT='".@$_REQUEST['INS_AMOUNT']."'
                ,INS_STT_DT='".@$_REQUEST['INS_STT_DT']."'
                ,INS_END_DT='".@$_REQUEST['INS_END_DT']."'
                ,INS_DTL='".@$_REQUEST['INS_DTL']."'";
            $sql = $sql."
                ,REG_DT='".date("Y-m-d h:m:s")."'
                WHERE INS_CD = '".$_REQUEST['INS_CD']."'";
        }
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else if($_REQUEST['CRUD']=='R'){
        //기본 쿼리
        $sql = "SELECT A.*,B.PSNL_NM,B.POSITION,C.ORG_NM FROM BONDANG_HR.PSNL_INSURANCE A
            LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
            LEFT OUTER JOIN ORG_INFO C ON B.ORG_CD = C.ORG_CD";
        //조건문 지정
        $whereSql = " WHERE 1=1 ";
        if(@$_REQUEST['INS_CD']){
            $whereSql=$whereSql." AND INS_CD = '".$_REQUEST['INS_CD']."'";
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
        $sql = "DELETE FROM BONDANG_HR.PSNL_INSURANCE WHERE INS_CD = '".$_REQUEST['INS_CD']."'";
        echo $sql; //오류 점검용 쿼리
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else{
        echo 'fmlConfig 잘못된 접근방식입니다.';
    }

?>