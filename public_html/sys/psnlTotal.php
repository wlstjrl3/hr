<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "SELECT COUNT(*) AS ROW_CNT FROM PSNL_INFO A 
        INNER JOIN ORG_INFO B ON A.ORG_CD = B.ORG_CD 
        LEFT OUTER JOIN PSNL_TRANSFER C ON C.TRS_CD = (
            SELECT TRS_CD FROM PSNL_TRANSFER AS C2
                WHERE C2.PSNL_CD = A.PSNL_CD
                ORDER BY TRS_DT DESC
                LIMIT 1
        )";
    //기본 쿼리
    $sql = "SELECT 
        CASE 
        WHEN C.TRS_TYPE = 1 THEN '재직'
        WHEN C.TRS_TYPE = 2 THEN '퇴사'
        WHEN C.TRS_TYPE = 3 THEN '전보'
        END AS TRS_TYPE
        ,C.POSITION,C.WORK_TYPE
        ,C.TRS_DT, B.ORG_NM, A.*, 
        TRUNCATE(DATEDIFF(CURDATE(), TRS_DT)/365,1) AS TRS_ELAPSE 
        ,D.ADVANCE_DT
        ,CASE 
        WHEN SUBSTR(D.ADVANCE_DT,6,2) = '01' THEN '상반기'
        WHEN SUBSTR(D.ADVANCE_DT,6,2) = '07' THEN '하반기'
        
        END AS ADVANCE_RNG
        ,D.GRD_GRADE,D.GRD_PAY
        ,FORMAT(E.NORMAL_PAY,0) AS NORMAL_PAY,FORMAT(E.LEGAL_PAY,0) AS LEGAL_PAY
        ,0 AS ADJUST_PAY,FORMAT(E.NORMAL_PAY+E.LEGAL_PAY,0) AS EXPECT_PAY
        FROM PSNL_INFO A 
        INNER JOIN ORG_INFO B ON A.ORG_CD = B.ORG_CD 
        LEFT OUTER JOIN PSNL_TRANSFER C ON C.TRS_CD = (
            SELECT TRS_CD FROM PSNL_TRANSFER AS C2
                WHERE C2.PSNL_CD = A.PSNL_CD
                ORDER BY TRS_DT DESC
                LIMIT 1
        )
        LEFT OUTER JOIN GRADE_HISTORY D ON D.GRD_CD = (
            SELECT GRD_CD FROM GRADE_HISTORY AS D2
                WHERE D2.PSNL_CD = A.PSNL_CD
                ORDER BY ADVANCE_DT DESC
                LIMIT 1
        )
        LEFT OUTER JOIN SALARY_TB E ON D.GRD_GRADE = E.SLR_GRADE AND D.GRD_PAY = E.SLR_PAY AND SLR_YEAR = LEFT(CURDATE(),4)
        ";
    //조건문 지정
    $whereSql = " WHERE 1=1 ";
    if(@$_REQUEST['PSNL_CD']){
        $whereSql=$whereSql." AND A.PSNL_CD = '".$_REQUEST['PSNL_CD']."'";
    }    
    if(@$_REQUEST['ORG_NM']){
        $whereSql=$whereSql." AND ORG_NM LIKE '%".$_REQUEST['ORG_NM']."%'"; //조직 정보의 B테이블에서 가져온다.
    }
    if(@$_REQUEST['PSNL_NM']){
        $whereSql=$whereSql." AND PSNL_NM LIKE '%".$_REQUEST['PSNL_NM']."%'";
    }
    if(@$_REQUEST['BAPT_NM']){
        $whereSql=$whereSql." AND BAPT_NM LIKE '%".$_REQUEST['BAPT_NM']."%'";
    }
    if(@$_REQUEST['POSITION']){
        $whereSql=$whereSql." AND POSITION LIKE '%".$_REQUEST['POSITION']."%'";
    }
    if(@$_REQUEST['WORK_TYPE']){
        $whereSql=$whereSql." AND WORK_TYPE LIKE '%".$_REQUEST['WORK_TYPE']."%'";
    }
    if(@$_REQUEST['TRS_TYPE']){
        if($_REQUEST['TRS_TYPE']==1){
            $whereSql=$whereSql." AND TRS_TYPE IN ('1','3')";
        }else{
            $whereSql=$whereSql." AND TRS_TYPE = '".$_REQUEST['TRS_TYPE']."'";
        }
    }
    if(@$_REQUEST['PHONE_NUM']){
        $whereSql=$whereSql." AND PHONE_NUM LIKE '%".$_REQUEST['PHONE_NUM']."%'";
    }
    if(@$_REQUEST['PSNL_BIRTH_From']){ //PSNL_NUM을 날짜형식으로 변경하여 비교해야함.
        $whereSql=$whereSql." AND PSNL_NUM >= '".$_REQUEST['PSNL_BIRTH']." 00:00:00'";
    }
    if(@$_REQUEST['PSNL_BIRTH_To']){ //PSNL_NUM을 날짜형식으로 변경하여 비교해야함.
        $whereSql=$whereSql." AND PSNL_NUM <= '".$_REQUEST['PSNL_BIRTH_To']." 23:59:59'";
    }
    if(@$_REQUEST['PSNL_NUM']){
        $whereSql=$whereSql." AND PSNL_NUM LIKE '%".$_REQUEST['PSNL_NUM']."%'";
    }


    //정렬 기준 지정
    $orderSql = "";
    if(@$_REQUEST['ORDER']){
        $orderSql = $orderSql." ORDER BY ".$_REQUEST['ORDER'];
    }
    //리미트 지정
    $limitSql = "";
    if(@$_REQUEST['LIMIT']){
        $limitSql = $limitSql." LIMIT ".$_REQUEST['LIMIT'];
    }
    
    $totalCnt = mysqli_fetch_assoc(mysqli_query($conn,$rowCntSql));
    $filterCnt = mysqli_fetch_assoc(mysqli_query($conn,$rowCntSql.$whereSql));

    $result = mysqli_query($conn,$sql.$whereSql.$orderSql.$limitSql);
    mysqli_close($conn);

    while($row = mysqli_fetch_assoc($result)){
        $data[] = $row;
    }
    $datas = array(
       "data" => @$data
       ,"date" => "2021-99-99"
       ,"totalCnt" => $totalCnt["ROW_CNT"]
       ,"filterCnt" => $filterCnt["ROW_CNT"]
    ); 

    echo json_encode($datas, JSON_UNESCAPED_UNICODE);

?>