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
        ,C.TRS_DT, B.ORG_NM, B.PERSON_CNT, A.*, 
        TRUNCATE(DATEDIFF(CURDATE(), TRS_DT)/365,1) AS TRS_ELAPSE 
        ,D.ADVANCE_DT
        ,CASE 
        WHEN SUBSTR(D.ADVANCE_DT,6,2) = '01' THEN '상반기'
        WHEN SUBSTR(D.ADVANCE_DT,6,2) = '07' THEN '하반기'
        
        END AS ADVANCE_RNG
        ,D.GRD_GRADE,D.GRD_PAY
        ,FORMAT(E.NORMAL_PAY,0) AS NORMAL_PAY,FORMAT(E.LEGAL_PAY,0) AS LEGAL_PAY
        ,IFNULL((
            SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE PSNL_CD = A.PSNL_CD
            AND LEFT(ADJ_STT_DT,4) <= LEFT(D.ADVANCE_DT,4)
            AND (LEFT(ADJ_END_DT,4) >= LEFT(D.ADVANCE_DT,4) OR ADJ_END_DT is null)
        ),0) AS ADJUST_PAY
        ,IFNULL((
            SELECT SUM(FML_PAY) FROM PSNL_FAMILY WHERE PSNL_CD = A.PSNL_CD
            AND LEFT(FML_STT_DT,4) <= LEFT(D.ADVANCE_DT,4)
            AND (LEFT(FML_END_DT,4) >= LEFT(D.ADVANCE_DT,4) OR FML_END_DT is null)
        ),0) AS FAMILY_PAY
        ,FORMAT(
        IFNULL((
            SELECT SUM(ADJ_PAY) FROM PSNL_ADJUST WHERE PSNL_CD = A.PSNL_CD
            AND LEFT(ADJ_STT_DT,4) <= LEFT(D.ADVANCE_DT,4)
            AND (LEFT(ADJ_END_DT,4) >= LEFT(D.ADVANCE_DT,4) OR ADJ_END_DT is null)
        ),0) + 
        IFNULL((
            SELECT SUM(FML_PAY) FROM PSNL_FAMILY WHERE PSNL_CD = A.PSNL_CD
            AND LEFT(FML_STT_DT,4) <= LEFT(D.ADVANCE_DT,4)
            AND (LEFT(FML_END_DT,4) >= LEFT(D.ADVANCE_DT,4) OR FML_END_DT is null)
        ),0) +
        E.NORMAL_PAY+E.LEGAL_PAY+0,0) AS EXPECT_PAY
        FROM PSNL_INFO A 
        INNER JOIN ORG_INFO B ON A.ORG_CD = B.ORG_CD 
        LEFT OUTER JOIN PSNL_TRANSFER C ON C.TRS_CD = (
            SELECT TRS_CD FROM PSNL_TRANSFER AS C2
                WHERE TRS_TYPE IN (1,2) AND C2.PSNL_CD = A.PSNL_CD /*전체 정보 표기시 전보는 제외하고 입 퇴사만 표기한다.*/
                ORDER BY TRS_DT DESC
                LIMIT 1
        )
        LEFT OUTER JOIN GRADE_HISTORY D ON D.GRD_CD = (
            SELECT GRD_CD FROM GRADE_HISTORY AS D2
                WHERE D2.PSNL_CD = A.PSNL_CD
                ORDER BY ADVANCE_DT DESC
                LIMIT 1
        )
        LEFT OUTER JOIN SALARY_TB E ON C.WORK_TYPE = E.SLR_TYPE AND  D.GRD_GRADE = E.SLR_GRADE AND D.GRD_PAY = E.SLR_PAY AND SLR_YEAR = LEFT(D.ADVANCE_DT,4)
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
        $whereSql=$whereSql." AND LEFT(PSNL_NUM,6) >= RIGHT(REPLACE('".$_REQUEST['PSNL_BIRTH_From']."', '-', ''),6)";
    }
    if(@$_REQUEST['PSNL_BIRTH_To']){ //PSNL_NUM을 날짜형식으로 변경하여 비교해야함.
        $whereSql=$whereSql." AND LEFT(PSNL_NUM,6) <= RIGHT(REPLACE('".$_REQUEST['PSNL_BIRTH_To']."', '-', ''),6)";
    }
    if(@$_REQUEST['PSNL_NUM']){
        $whereSql=$whereSql." AND PSNL_NUM LIKE '%".$_REQUEST['PSNL_NUM']."%'";
    }
    if(@$_REQUEST['TRS_DT_From']){ //PSNL_NUM을 날짜형식으로 변경하여 비교해야함.
        $whereSql=$whereSql." AND C.TRS_DT >= '".$_REQUEST['TRS_DT_From']." 00:00:00'";
    }
    if(@$_REQUEST['TRS_DT_To']){ //PSNL_NUM을 날짜형식으로 변경하여 비교해야함.
        $whereSql=$whereSql." AND C.TRS_DT <= '".$_REQUEST['TRS_DT_To']." 23:59:59'";
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
       ,"query" => $sql.$whereSql.$orderSql.$limitSql
    ); 

    echo json_encode($datas, JSON_UNESCAPED_UNICODE);

?>