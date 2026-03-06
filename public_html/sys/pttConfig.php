<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

if ($_REQUEST['CRUD'] == 'C') {
    if ($_REQUEST['PTT_CD'] == "") { //신규 작성
        $addHour = @$_REQUEST['PTT_ADDHOUR'] ? $_REQUEST['PTT_ADDHOUR'] : "0";
        $adjPay = @$_REQUEST['PTT_ADJPAY'] ? $_REQUEST['PTT_ADJPAY'] : "0";

        executeUpdate($conn,
            "INSERT INTO BONDANG_HR.PSNL_PARTTIME(PSNL_CD,PTT_YEAR,PTT_DAY,PTT_HOUR,PTT_ADDHOUR,PTT_ADJ,PTT_ADJPAY,REG_DT) VALUES (?,?,?,?,?,?,?,?)",
            "ssssssss",
        [$_REQUEST['PSNL_CD'], $_REQUEST['PTT_YEAR'], $_REQUEST['PTT_DAY'], $_REQUEST['PTT_HOUR'], $addHour, $_REQUEST['PTT_ADJ'], $adjPay, date("Y-m-d h:m:s")]
        );
    }
    else { //기존 데이터 UPDATE
        $addHour = @$_REQUEST['PTT_ADDHOUR'] ? $_REQUEST['PTT_ADDHOUR'] : "0";
        $adjPay = @$_REQUEST['PTT_ADJPAY'] ? $_REQUEST['PTT_ADJPAY'] : "0";

        executeUpdate($conn,
            "UPDATE BONDANG_HR.PSNL_PARTTIME SET PSNL_CD=?, PTT_YEAR=?, PTT_DAY=?, PTT_HOUR=?, PTT_ADDHOUR=?, PTT_ADJ=?, PTT_ADJPAY=?, REG_DT=? WHERE PTT_CD = ?",
            "sssssssss",
        [$_REQUEST['PSNL_CD'], @$_REQUEST['PTT_YEAR'], @$_REQUEST['PTT_DAY'], @$_REQUEST['PTT_HOUR'], $addHour, @$_REQUEST['PTT_ADJ'], $adjPay, date("Y-m-d h:m:s"), $_REQUEST['PTT_CD']]
        );
    }
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'R') {
    $sql = "SELECT A.*,B.PSNL_NM,C.ORG_NM,POSITION FROM BONDANG_HR.PSNL_PARTTIME A
        LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
            SELECT TRS_CD FROM PSNL_TRANSFER AS P2 
            WHERE P2.PSNL_CD = A.PSNL_CD ORDER BY P2.REG_DT DESC LIMIT 1
        )            
        LEFT OUTER JOIN ORG_INFO C ON P.ORG_CD = C.ORG_CD";
    $whereSql = " WHERE 1=1 ";
    $params = [];
    $types = "";
    if (@$_REQUEST['PTT_CD']) {
        $whereSql .= " AND PTT_CD = ?";
        $params[] = $_REQUEST['PTT_CD'];
        $types .= "s";
    }
    $limitSql = safeLimit(@$_REQUEST['LIMIT']);
    $data = executeQuery($conn, $sql . $whereSql . $limitSql, $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "date" => "2021-99-99"]);
}
else if ($_REQUEST['CRUD'] == 'D') {
    executeUpdate($conn, "DELETE FROM BONDANG_HR.PSNL_PARTTIME WHERE PTT_CD = ?", "s", [$_REQUEST['PTT_CD']]);
    mysqli_close($conn);
}
else {
    echo 'fmlConfig 잘못된 접근방식입니다.';
}

?>