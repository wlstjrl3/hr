<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    
    if($_REQUEST['CRUD']=='C'){
        if($_REQUEST['TRS_CD']==""){ //신규 작성
            $sql = "INSERT INTO BONDANG_HR.PSNL_TRANSFER(PSNL_CD,ORG_CD,WORK_TYPE,POSITION,TRS_TYPE,TRS_DTL,TRS_DT,REG_DT) VALUES ('";
            $sql = $sql.$_REQUEST['PSNL_CD']."','".$_REQUEST['ORG_CD']."','".$_REQUEST['WORK_TYPE']."','".$_REQUEST['POSITION']."','".$_REQUEST['TRS_TYPE']."','".$_REQUEST['TRS_DTL']."','".$_REQUEST['TRS_DT'];
            $sql = $sql."','".date("Y-m-d h:m:s")."')";
            echo $sql; //오류 점검용 쿼리
        }else{ //기존 데이터 UPDATE
            $sql = "UPDATE BONDANG_HR.PSNL_TRANSFER SET 
                PSNL_CD='".$_REQUEST['PSNL_CD']."'
                ,ORG_CD='".@$_REQUEST['ORG_CD']."'
                ,WORK_TYPE='".@$_REQUEST['WORK_TYPE']."'
                ,POSITION='".@$_REQUEST['POSITION']."'
                ,TRS_TYPE='".@$_REQUEST['TRS_TYPE']."'
                ,TRS_DTL='".@$_REQUEST['TRS_DTL']."'
                ,TRS_DT='".@$_REQUEST['TRS_DT']."'";
            $sql = $sql."
                ,REG_DT='".date("Y-m-d h:m:s")."'
                WHERE TRS_CD = '".$_REQUEST['TRS_CD']."'";
        }
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else if($_REQUEST['CRUD']=='R'){
        //기본 쿼리
        $sql = "SELECT A.*,B.PSNL_NM,C.ORG_NM,D.ORG_NM AS OLD_ORG_NM FROM BONDANG_HR.PSNL_TRANSFER A
            LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
            LEFT OUTER JOIN ORG_INFO C ON B.ORG_CD = C.ORG_CD
            LEFT OUTER JOIN ORG_INFO D ON A.ORG_CD = D.ORG_CD";
        //조건문 지정
        $whereSql = " WHERE 1=1 ";
        if(@$_REQUEST['TRS_CD']){
            $whereSql=$whereSql." AND TRS_CD = '".$_REQUEST['TRS_CD']."'";
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
        $sql = "DELETE FROM BONDANG_HR.PSNL_TRANSFER WHERE TRS_CD = '".$_REQUEST['TRS_CD']."'";
        echo $sql; //오류 점검용 쿼리
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else{
        echo 'trsConfig 잘못된 접근방식입니다.';
    }

?>