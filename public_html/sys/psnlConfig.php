<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    
    if($_REQUEST['CRUD']=='C'){
        if($_REQUEST['PSNL_NUM']==""){}else{ //주민번호가 기입되어 있다면 중복데이터 존재여부를 체크한다.
            if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.PSNL_INFO WHERE PSNL_CD != '".@$_REQUEST['PSNL_CD']."' AND PSNL_NUM = '".@$_REQUEST['PSNL_NUM']."' LIMIT 1"))>0){echo "중복되는 주민번호가 존재합니다.";die;} //보안 검증
        }
        if($_REQUEST['PSNL_CD']==""){ //신규 작성
            $sql = "INSERT INTO BONDANG_HR.PSNL_INFO(PSNL_NM, BAPT_NM,PHONE_NUM,PSNL_NUM, REG_DT, REG_PER) VALUES ('";
            $sql = $sql.$_REQUEST['PSNL_NM']."','".$_REQUEST['BAPT_NM'];
            $sql = $sql."','".$_REQUEST['PHONE_NUM']."','".$_REQUEST['PSNL_NUM'];
            $sql = $sql."','".date("Y-m-d h:m:s")."','regPer')";
        }else{ //기존 데이터 UPDATE
            $sql = "UPDATE BONDANG_HR.PSNL_INFO SET 
                PSNL_NM='".$_REQUEST['PSNL_NM']."'
                ,BAPT_NM='".$_REQUEST['BAPT_NM']."'
                ,PHONE_NUM='".$_REQUEST['PHONE_NUM']."'
                ,PSNL_NUM='".$_REQUEST['PSNL_NUM']."'
                ,REG_DT='".date("Y-m-d h:m:s")."'
                ,REG_PER='edtPer'
                WHERE PSNL_CD = '".$_REQUEST['PSNL_CD']."'";
        }
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else if($_REQUEST['CRUD']=='R'){
        //기본 쿼리
        $sql = "SELECT B.ORG_NM,A.* FROM BONDANG_HR.PSNL_INFO A
                LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
                    SELECT TRS_CD FROM PSNL_TRANSFER AS P2
                    WHERE P2.PSNL_CD = A.PSNL_CD
                    ORDER BY P2.REG_DT DESC
                    LIMIT 1
                )
         LEFT OUTER JOIN BONDANG_HR.ORG_INFO B ON P.ORG_CD = B.ORG_CD";
        //조건문 지정
        $whereSql = " WHERE 1=1 ";
        if(@$_REQUEST['PSNL_CD']){
            $whereSql=$whereSql." AND A.PSNL_CD = '".$_REQUEST['PSNL_CD']."'";
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
        $sql = "DELETE FROM BONDANG_HR.PSNL_INFO WHERE PSNL_CD = '".$_REQUEST['PSNL_CD']."'";
        echo $sql; //오류 점검용 쿼리
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else{
        echo 'userConfig 잘못된 접근방식입니다.';
    }

?>