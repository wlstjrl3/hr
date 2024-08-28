<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    
    if($_REQUEST['CRUD']=='C'){
        if($_REQUEST['LCS_CD']==""){ //신규 작성
            $sql = "INSERT INTO BONDANG_HR.PSNL_LICENSE(PSNL_CD, LCS_NM, LCS_LEVEL, LCS_NUM, LCS_GET_DT, LCS_DTL";
            if(@$_REQUEST['LCS_PAY']){$sql=$sql.",LCS_PAY";}
            if(@$_REQUEST['LCS_STT_DT']){$sql=$sql.",LCS_STT_DT";}
            if(@$_REQUEST['LCS_END_DT']){$sql=$sql.",LCS_END_DT";}
            $sql = $sql.",REG_DT) VALUES ('";
            $sql = $sql.$_REQUEST['PSNL_CD']."','".$_REQUEST['LCS_NM']."','".$_REQUEST['LCS_LEVEL']."','".$_REQUEST['LCS_NUM']."','".$_REQUEST['LCS_GET_DT']."','".@$_REQUEST['LCS_DTL'];
            if(@$_REQUEST['LCS_PAY']){$sql=$sql."','".@$_REQUEST['LCS_PAY'];}
            if(@$_REQUEST['LCS_STT_DT']){$sql=$sql."','".@$_REQUEST['LCS_STT_DT'];}
            if(@$_REQUEST['LCS_END_DT']){$sql=$sql."','".@$_REQUEST['LCS_END_DT'];}
            $sql = $sql."','".date("Y-m-d h:m:s")."')";
            echo $sql; //오류 점검용 쿼리
        }else{ //기존 데이터 UPDATE
            $sql = "UPDATE BONDANG_HR.PSNL_LICENSE SET 
                PSNL_CD='".$_REQUEST['PSNL_CD']."'
                ,LCS_NM='".$_REQUEST['LCS_NM']."'
                ,LCS_LEVEL='".$_REQUEST['LCS_LEVEL']."'
                ,LCS_NUM='".$_REQUEST['LCS_NUM']."'
                ,LCS_GET_DT='".$_REQUEST['LCS_GET_DT']."'
                ,LCS_DTL='".@$_REQUEST['LCS_DTL']."'";
                if(@$_REQUEST['LCS_PAY']){$sql=$sql.",LCS_PAY='".@$_REQUEST['LCS_PAY']."'";}
                if(@$_REQUEST['LCS_STT_DT']){$sql=$sql.",LCS_STT_DT='".@$_REQUEST['LCS_STT_DT']."'";}
                if(@$_REQUEST['LCS_END_DT']){$sql=$sql.",LCS_END_DT='".@$_REQUEST['LCS_END_DT']."'";}
            $sql = $sql."
                ,REG_DT='".date("Y-m-d h:m:s")."'
                WHERE LCS_CD = '".$_REQUEST['LCS_CD']."'";
        }
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else if($_REQUEST['CRUD']=='R'){
        //기본 쿼리
        $sql = "SELECT A.*,B.PSNL_NM,B.POSITION,C.ORG_NM FROM BONDANG_HR.PSNL_LICENSE A
            LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
            LEFT OUTER JOIN ORG_INFO C ON B.ORG_CD = C.ORG_CD";
        //조건문 지정
        $whereSql = " WHERE 1=1 ";
        if(@$_REQUEST['LCS_CD']){
            $whereSql=$whereSql." AND LCS_CD = '".$_REQUEST['LCS_CD']."'";
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
        $sql = "DELETE FROM BONDANG_HR.PSNL_LICENSE WHERE LCS_CD = '".$_REQUEST['LCS_CD']."'";
        echo $sql; //오류 점검용 쿼리
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else{
        echo 'LCSConfig 잘못된 접근방식입니다.';
    }

?>