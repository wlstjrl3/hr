<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    include "../dbconn/dbconn.php";
    if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM BONDANG_HR.USER_TB WHERE USER_PASS = '".@$_REQUEST['key']."' LIMIT 1"))<1){die;} //보안 검증
    //갯수 카운트 쿼리
    $rowCntSql = "
WITH TEMP_TBL AS (
	SELECT C.ORG_NM,B.PSNL_NM,D.POSITION,D.TRS_TYPE,C.ORG_CD,A.PSNL_CD,A.GRD_GRADE,SUM(1) AS CNTT FROM GRADE_HISTORY A
		LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
		LEFT OUTER JOIN ORG_INFO C ON B.ORG_CD = C.ORG_CD
		LEFT OUTER JOIN (
			SELECT PSNL_CD,POSITION,TRS_TYPE FROM PSNL_TRANSFER
				WHERE (PSNL_CD,TRS_DT) IN (SELECT PSNL_CD,MAX(TRS_DT) AS TRS_DT FROM BONDANG_HR.PSNL_TRANSFER GROUP BY PSNL_CD)
			GROUP BY PSNL_CD
		) D ON A.PSNL_CD = D.PSNL_CD
		WHERE (A.PSNL_CD,GRD_GRADE) IN
		(SELECT PSNL_CD,MIN(GRD_GRADE) AS GRD_GRADE FROM BONDANG_HR.GRADE_HISTORY GROUP BY PSNL_CD)
		GROUP BY A.PSNL_CD
	ORDER BY CNTT DESC, GRD_GRADE ASC
)
SELECT COUNT(PSNL_CD) AS ROW_CNT FROM TEMP_TBL    
    ";
    //기본 쿼리
    $sql = "
WITH TEMP_TBL AS (
	SELECT C.ORG_NM,B.PSNL_NM,D.POSITION,D.TRS_TYPE,C.ORG_CD,A.PSNL_CD,A.GRD_GRADE,SUM(1) AS CNTT FROM GRADE_HISTORY A
		LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
		LEFT OUTER JOIN ORG_INFO C ON B.ORG_CD = C.ORG_CD
		LEFT OUTER JOIN (
			SELECT PSNL_CD,POSITION,TRS_TYPE FROM PSNL_TRANSFER
				WHERE (PSNL_CD,TRS_DT) IN (SELECT PSNL_CD,MAX(TRS_DT) AS TRS_DT FROM BONDANG_HR.PSNL_TRANSFER GROUP BY PSNL_CD)
			GROUP BY PSNL_CD
		) D ON A.PSNL_CD = D.PSNL_CD
		WHERE (A.PSNL_CD,GRD_GRADE) IN
		(SELECT PSNL_CD,MIN(GRD_GRADE) AS GRD_GRADE FROM BONDANG_HR.GRADE_HISTORY GROUP BY PSNL_CD)
		GROUP BY A.PSNL_CD
	ORDER BY CNTT DESC, GRD_GRADE ASC
)
SELECT * FROM TEMP_TBL
    ";
    //조건문 지정
    $whereSql = " WHERE TRS_TYPE <> 2 AND ((GRD_GRADE>=6 AND CNTT >= 4) OR (GRD_GRADE=5 AND CNTT >= 5))"; //
    if(@$_REQUEST['ORG_NM']){
        $whereSql=$whereSql." AND ORG_NM LIKE '%".$_REQUEST['ORG_NM']."%'";
    }
    if(@$_REQUEST['PSNL_NM']){
        $whereSql=$whereSql." AND PSNL_NM LIKE '%".$_REQUEST['PSNL_NM']."%'";
    }
    if(@$_REQUEST['POSITION']){
        $whereSql=$whereSql." AND POSITION LIKE '%".$_REQUEST['POSITION']."%'";
    }
    if(@$_REQUEST['GRD_GRADE']){
        $whereSql=$whereSql." AND GRD_GRADE = '".$_REQUEST['GRD_GRADE']."'";
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
    //echo $sql.$whereSql.$orderSql.$limitSql;
    //die;    
    $totalCnt = mysqli_fetch_assoc(mysqli_query($conn,$rowCntSql));
    $filterCnt = mysqli_fetch_assoc(mysqli_query($conn,$rowCntSql.$whereSql));
    $result = mysqli_query($conn,$sql.$whereSql.$orderSql.$limitSql);
    mysqli_close($conn);

    while($row = mysqli_fetch_assoc($result)){
        $data[] = $row;
    }
    $datas = array(
       "data" => @$data
       //,"query" => $sql.$whereSql.$orderSql.$limitSql
       ,"totalCnt" => $totalCnt["ROW_CNT"]
       ,"filterCnt" => $filterCnt["ROW_CNT"]
    ); 

    echo json_encode($datas, JSON_UNESCAPED_UNICODE);

?>