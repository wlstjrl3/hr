<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    
    if($_REQUEST['CRUD']=='C'){
        if($_REQUEST['ADJ_CD']==""){ //신규 작성
            $sql = "INSERT INTO BONDANG_HR.PSNL_ADJUST(PSNL_CD, ADJ_TYPE, ADJ_NM, ADJ_LEVEL, ADJ_NUM, ADJ_DTL";
            if(@$_REQUEST['ADJ_PAY']){$sql=$sql.",ADJ_PAY";}
            if(@$_REQUEST['ADJ_GET_DT']){$sql=$sql.",ADJ_GET_DT";}
            if(@$_REQUEST['ADJ_STT_DT']){$sql=$sql.",ADJ_STT_DT";}
            if(@$_REQUEST['ADJ_END_DT']){$sql=$sql.",ADJ_END_DT";}
            $sql = $sql.",REG_DT) VALUES ('";
            $sql = $sql.$_REQUEST['PSNL_CD']."','".$_REQUEST['ADJ_TYPE']."','".$_REQUEST['ADJ_NM']."','".$_REQUEST['ADJ_LEVEL']."','".$_REQUEST['ADJ_NUM']."','".@$_REQUEST['ADJ_DTL'];
            if(@$_REQUEST['ADJ_PAY']){$sql=$sql."','".@$_REQUEST['ADJ_PAY'];}
            if(@$_REQUEST['ADJ_GET_DT']){$sql=$sql."','".@$_REQUEST['ADJ_GET_DT'];}
            if(@$_REQUEST['ADJ_STT_DT']){$sql=$sql."','".@$_REQUEST['ADJ_STT_DT'];}
            if(@$_REQUEST['ADJ_END_DT']){$sql=$sql."','".@$_REQUEST['ADJ_END_DT'];}
            $sql = $sql."','".date("Y-m-d h:m:s")."')";
            //echo $sql; //오류 점검용 쿼리
        }else{ //기존 데이터 UPDATE
            $sql = "UPDATE BONDANG_HR.PSNL_ADJUST SET 
                PSNL_CD='".$_REQUEST['PSNL_CD']."'
                ,ADJ_TYPE='".$_REQUEST['ADJ_TYPE']."'
                ,ADJ_NM='".$_REQUEST['ADJ_NM']."'
                ,ADJ_LEVEL='".$_REQUEST['ADJ_LEVEL']."'
                ,ADJ_NUM='".$_REQUEST['ADJ_NUM']."'
                ,ADJ_DTL='".@$_REQUEST['ADJ_DTL']."'";
                if(@$_REQUEST['ADJ_PAY']){$sql=$sql.",ADJ_PAY='".@$_REQUEST['ADJ_PAY']."'";}
                if(@$_REQUEST['ADJ_GET_DT']){$sql=$sql.",ADJ_GET_DT='".@$_REQUEST['ADJ_GET_DT']."'";}else{$sql=$sql.",ADJ_GET_DT=NULL";}
                if(@$_REQUEST['ADJ_STT_DT']){$sql=$sql.",ADJ_STT_DT='".@$_REQUEST['ADJ_STT_DT']."'";}else{$sql=$sql.",ADJ_STT_DT=NULL";}
                if(@$_REQUEST['ADJ_END_DT']){$sql=$sql.",ADJ_END_DT='".@$_REQUEST['ADJ_END_DT']."'";}else{$sql=$sql.",ADJ_END_DT=NULL";}
            $sql = $sql."
                ,REG_DT='".date("Y-m-d h:m:s")."'
                WHERE ADJ_CD = '".$_REQUEST['ADJ_CD']."'";
        }
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else if($_REQUEST['CRUD']=='R'){
        //기본 쿼리
        $sql = "SELECT A.*,B.PSNL_NM,C.ORG_NM,POSITION FROM BONDANG_HR.PSNL_ADJUST A
            LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
            LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
                SELECT TRS_CD FROM PSNL_TRANSFER AS P2
                WHERE P2.PSNL_CD = A.PSNL_CD
                ORDER BY P2.REG_DT DESC
                LIMIT 1
            )            
            LEFT OUTER JOIN ORG_INFO C ON P.ORG_CD = C.ORG_CD
            ";
        //조건문 지정
        $whereSql = " WHERE 1=1 ";
        if(@$_REQUEST['ADJ_CD']){
            $whereSql=$whereSql." AND ADJ_CD = '".$_REQUEST['ADJ_CD']."'";
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
        $sql = "DELETE FROM BONDANG_HR.PSNL_ADJUST WHERE ADJ_CD = '".$_REQUEST['ADJ_CD']."'";
        //echo $sql; //오류 점검용 쿼리
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else{
        echo 'ADJConfig 잘못된 접근방식입니다.';
    }

?>