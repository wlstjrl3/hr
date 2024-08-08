<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    
    if($_REQUEST['CRUD']=='C'){
        if($_REQUEST['USER_CD']==""){ //신규 작성
            $sql = "INSERT INTO BONDANG_HR.USER_TB(USER_ID,USER_NM,USER_PASS,USER_AUTH,EMAIL,POSITION,ORG_NM,REG_DT,MEMO) VALUES ('";
            $sql = $sql.$_REQUEST['USER_ID']."','".$_REQUEST['USER_NM']."','".MD5($_REQUEST['USER_PASS'])."','".$_REQUEST['USER_AUTH'];
            $sql = $sql."','".$_REQUEST['EMAIL']."','".$_REQUEST['POSITION']."','".$_REQUEST['ORG_NM']."','".date("Y-m-d h:m:s")."','".$_REQUEST['MEMO'];
            $sql = $sql."')";
            echo $sql; //오류 점검용 쿼리
        }else{ //기존 데이터 UPDATE
            $sql = "UPDATE BONDANG_HR.USER_TB SET 
                USER_ID='".$_REQUEST['USER_ID']."'
                ,USER_NM='".$_REQUEST['USER_NM']."'";
            if(strlen(@$_REQUEST['USER_PASS'])>1){ //변경된 패스워드가 전달되었다면
                $sql = $sql.",USER_PASS='".MD5($_REQUEST['USER_PASS'])."'";
            }
            $sql = $sql."
                ,USER_AUTH='".$_REQUEST['USER_AUTH']."'
                ,EMAIL='".$_REQUEST['EMAIL']."'
                ,POSITION='".$_REQUEST['POSITION']."'
                ,ORG_NM='".$_REQUEST['ORG_NM']."'
                ,MEMO='".$_REQUEST['MEMO']."'
                WHERE USER_CD = '".$_REQUEST['USER_CD']."'";
            echo $_REQUEST['USER_ID'];
        }
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else if($_REQUEST['CRUD']=='R'){
        //기본 쿼리
        $sql = "SELECT * FROM BONDANG_HR.USER_TB";
        //조건문 지정
        $whereSql = " WHERE 1=1 ";
        if(@$_REQUEST['USER_CD']){
            $whereSql=$whereSql." AND USER_CD = '".$_REQUEST['USER_CD']."'";
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
        echo json_encode($datas, JSON_UNESCAPED_UNICODE);
    }else if($_REQUEST['CRUD']=='D'){
        //기본 쿼리
        $sql = "DELETE FROM BONDANG_HR.USER_TB WHERE USER_CD = '".$_REQUEST['USER_CD']."'";
        echo $sql; //오류 점검용 쿼리
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }else{
        echo 'userConfig 잘못된 접근방식입니다.';
    }

?>