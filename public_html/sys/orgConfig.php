<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    
    if($_REQUEST['CRUD']=='C'){
        $sql = "INSERT INTO BONDANG_HR.ORG_INFO(ORG_CD, ORG_NM, UPPR_ORG_CD, PERSON_CNT, ORG_TYPE, REFRESH_DT, REG_DT) VALUES ('";
        $sql = $sql.$_REQUEST['ORG_CD']."','".$_REQUEST['ORG_NM']."','".$_REQUEST['UPPR_ORG_CD']."','".$_REQUEST['PERSON_CNT']."','".$_REQUEST['ORG_TYPE'];
        $sql = $sql."','".date("Y-m-d h:m:s")."','".date("Y-m-d h:m:s")."')
            ON DUPLICATE KEY UPDATE
                ORG_NM='".$_REQUEST['ORG_NM']."'
                ,UPPR_ORG_CD='".$_REQUEST['UPPR_ORG_CD']."'
                ,PERSON_CNT='".$_REQUEST['PERSON_CNT']."'
                ,ORG_TYPE='".$_REQUEST['ORG_TYPE']."'
                ,REFRESH_DT='".date("Y-m-d h:m:s")."'";
        //echo $sql;
        //die;
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else if($_REQUEST['CRUD']=='R'){
        //기본 쿼리
        $sql = "SELECT B.ORG_NM AS UPR_ORG_NM,A.* FROM BONDANG_HR.ORG_INFO A LEFT OUTER JOIN BONDANG_HR.ORG_INFO B ON A.UPPR_ORG_CD = B.ORG_CD";
        //조건문 지정
        $whereSql = " WHERE 1=1 ";
        if(@$_REQUEST['ORG_CD']){
            $whereSql=$whereSql." AND A.ORG_CD = '".$_REQUEST['ORG_CD']."'";
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
        $sql = "DELETE FROM BONDANG_HR.ORG_INFO WHERE ORG_CD = '".$_REQUEST['ORG_CD']."'";
        echo $sql; //오류 점검용 쿼리
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else{
        echo 'ongConfig 잘못된 접근방식입니다.';
    }

?>