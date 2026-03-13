<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
    //갯수 카운트 쿼리
    $rowCntSql = "
WITH TEMP_TBL AS (
	SELECT C.ORG_NM,B.PSNL_NM,D.POSITION,D.TRS_TYPE,C.ORG_CD,A.PSNL_CD,A.GRD_GRADE,SUM(1) AS CNTT FROM GRADE_HISTORY A
		LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
		LEFT OUTER JOIN (
			SELECT PSNL_CD,ORG_CD,POSITION,TRS_TYPE FROM PSNL_TRANSFER
            WHERE (PSNL_CD,TRS_DT) IN (SELECT PSNL_CD,MAX(TRS_DT) AS TRS_DT FROM BONDANG_HR.PSNL_TRANSFER GROUP BY PSNL_CD)
			GROUP BY PSNL_CD
            ) D ON A.PSNL_CD = D.PSNL_CD
        LEFT OUTER JOIN ORG_INFO C ON D.ORG_CD = C.ORG_CD
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
		LEFT OUTER JOIN (
			SELECT PSNL_CD,ORG_CD,POSITION,TRS_TYPE FROM PSNL_TRANSFER
            WHERE (PSNL_CD,TRS_DT) IN (SELECT PSNL_CD,MAX(TRS_DT) AS TRS_DT FROM BONDANG_HR.PSNL_TRANSFER GROUP BY PSNL_CD)
			GROUP BY PSNL_CD
            ) D ON A.PSNL_CD = D.PSNL_CD
        LEFT OUTER JOIN ORG_INFO C ON D.ORG_CD = C.ORG_CD
		WHERE (A.PSNL_CD,GRD_GRADE) IN
		(SELECT PSNL_CD,MIN(GRD_GRADE) AS GRD_GRADE FROM BONDANG_HR.GRADE_HISTORY GROUP BY PSNL_CD)
		GROUP BY A.PSNL_CD
	ORDER BY CNTT DESC, GRD_GRADE ASC
)
SELECT * FROM TEMP_TBL
    ";
    //조건문 지정
    $params = [];
    $types = "";
    $whereSql = " WHERE TRS_TYPE <> 2 AND ((GRD_GRADE>=6 AND CNTT >= 4) OR (GRD_GRADE=5 AND CNTT >= 5))"; //
    if(@$_REQUEST['ORG_NM']){
        $whereSql .= " AND ORG_NM LIKE ?";
        $params[] = '%'.$_REQUEST['ORG_NM'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['PSNL_NM']){
        $whereSql .= " AND PSNL_NM LIKE ?";
        $params[] = '%'.$_REQUEST['PSNL_NM'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['POSITION']){
        $whereSql .= " AND POSITION LIKE ?";
        $params[] = '%'.$_REQUEST['POSITION'].'%';
        $types .= "s";
    }
    if(@$_REQUEST['GRD_GRADE']){
        $whereSql .= " AND GRD_GRADE = ?";
        $params[] = $_REQUEST['GRD_GRADE'];
        $types .= "s";
    }
    //정렬 기준 지정
    $orderSql = safeOrderBy(@$_REQUEST['ORDER'], []);
    //리미트 지정
    $limitSql = safeLimit(@$_REQUEST['LIMIT']);
    $totalCnt = mysqli_fetch_assoc(mysqli_query($conn, $rowCntSql));
    $filterResult = executeQuery($conn, $rowCntSql . $whereSql, $types, $params);
    $filterCnt = $filterResult[0];
    $data = executeQuery($conn, $sql . $whereSql . $orderSql . $limitSql, $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "totalCnt" => $totalCnt["ROW_CNT"], "filterCnt" => $filterCnt["ROW_CNT"]]);

?>