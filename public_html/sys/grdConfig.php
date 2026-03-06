<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

if ($_REQUEST['CRUD'] == 'C') {
    if ($_REQUEST['GRD_CD'] == "") {
        $regDt = date("Y-m-d h:m:s");
        executeUpdate($conn,
            "INSERT INTO BONDANG_HR.GRADE_HISTORY(PSNL_CD,ADVANCE_DT,GRD_GRADE,GRD_PAY,GRD_DTL,REG_DT) VALUES (?,?,?,?,?,?)",
            "ssssss",
        [$_REQUEST['PSNL_CD'], $_REQUEST['ADVANCE_DT'], $_REQUEST['GRD_GRADE'], $_REQUEST['GRD_PAY'], $_REQUEST['GRD_DTL'], $regDt]
        );
    }
    else {
        $regDt = date("Y-m-d h:m:s");
        executeUpdate($conn,
            "UPDATE BONDANG_HR.GRADE_HISTORY SET PSNL_CD=?,ADVANCE_DT=?,GRD_GRADE=?,GRD_PAY=?,GRD_DTL=?,REG_DT=? WHERE GRD_CD = ?",
            "sssssss",
        [$_REQUEST['PSNL_CD'], @$_REQUEST['ADVANCE_DT'], @$_REQUEST['GRD_GRADE'], @$_REQUEST['GRD_PAY'], @$_REQUEST['GRD_DTL'], $regDt, $_REQUEST['GRD_CD']]
        );
    }
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'R') {
    $sql = "SELECT A.*,B.PSNL_NM,C.ORG_NM,P.POSITION FROM BONDANG_HR.GRADE_HISTORY A
        LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
            SELECT TRS_CD FROM PSNL_TRANSFER AS P2
            WHERE P2.PSNL_CD = A.PSNL_CD ORDER BY P2.REG_DT DESC LIMIT 1
        )
        LEFT OUTER JOIN ORG_INFO C ON P.ORG_CD = C.ORG_CD";
    $params = [];
    $types = "";
    $whereSql = " WHERE 1=1 ";
    if (@$_REQUEST['GRD_CD']) {
        $whereSql .= " AND GRD_CD = ?";
        $params[] = $_REQUEST['GRD_CD'];
        $types .= "s";
    }
    $data = executeQuery($conn, $sql . $whereSql . " LIMIT 1", $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "date" => "2021-99-99"]);
}
else if ($_REQUEST['CRUD'] == 'D') {
    executeUpdate($conn, "DELETE FROM BONDANG_HR.GRADE_HISTORY WHERE GRD_CD = ?", "s", [$_REQUEST['GRD_CD']]);
    mysqli_close($conn);
}
else {
    echo 'grdConfig 잘못된 접근방식입니다.';
}

?>