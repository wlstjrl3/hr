<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    
    if($_REQUEST['CRUD']=='C'){
        if($_REQUEST['OPI_CD']==""){ //신규 작성
            $sql = "INSERT INTO BONDANG_HR.PSNL_OPINION(PSNL_CD,OPI_DT,OPI_PERSON,OPI_DTL,OPI_TYPE,REG_DT) VALUES ('";
            $sql = $sql.$_REQUEST['PSNL_CD']."','".$_REQUEST['OPI_DT']."','".$_REQUEST['OPI_PERSON']."','".$_REQUEST['OPI_DTL']."','".$_REQUEST['OPI_TYPE'];
            $sql = $sql."','".date("Y-m-d h:m:s")."')";
            echo $sql; //오류 점검용 쿼리
        }else{ //기존 데이터 UPDATE
            $sql = "UPDATE BONDANG_HR.PSNL_OPINION SET 
                PSNL_CD='".$_REQUEST['PSNL_CD']."'
                ,OPI_DT='".@$_REQUEST['OPI_DT']."'
                ,OPI_PERSON='".@$_REQUEST['OPI_PERSON']."'
                ,OPI_DTL='".@$_REQUEST['OPI_DTL']."'
                ,OPI_TYPE='".@$_REQUEST['OPI_TYPE']."'";
            $sql = $sql."
                ,REG_DT='".date("Y-m-d h:m:s")."'
                WHERE OPI_CD = '".$_REQUEST['OPI_CD']."'";
        }
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else if($_REQUEST['CRUD']=='R'){
        //기본 쿼리
        $sql = "SELECT A.*,B.PSNL_NM,C.ORG_NM,POSITION FROM BONDANG_HR.PSNL_OPINION A
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
        if(@$_REQUEST['OPI_CD']){
            $whereSql=$whereSql." AND OPI_CD = '".$_REQUEST['OPI_CD']."'";
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
        $sql = "DELETE FROM BONDANG_HR.PSNL_OPINION WHERE OPI_CD = '".$_REQUEST['OPI_CD']."'";
        echo $sql; //오류 점검용 쿼리
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else{
        echo 'fmlConfig 잘못된 접근방식입니다.';
    }

?>