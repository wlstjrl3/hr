<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

if ($_REQUEST['CRUD'] == 'C') {
    if ($_REQUEST['CERT_CD'] == "") {
        $regDt = date("Y-m-d h:m:s");
        executeUpdate($conn,
            "INSERT INTO BONDANG_HR.CERTIFICATE_HISTORY(PSNL_CD,CERT_TYPE,ISSUE_DT,CERT_DTL,REG_DT) VALUES (?,?,?,?,?)",
            "sssss",
        [$_REQUEST['PSNL_CD'], $_REQUEST['CERT_TYPE'], $_REQUEST['ISSUE_DT'], $_REQUEST['CERT_DTL'], $regDt]
        );
    }
    else {
        $regDt = date("Y-m-d h:m:s");
        executeUpdate($conn,
            "UPDATE BONDANG_HR.CERTIFICATE_HISTORY SET PSNL_CD=?,CERT_TYPE=?,ISSUE_DT=?,CERT_DTL=?,REG_DT=? WHERE CERT_CD = ?",
            "ssssss",
        [$_REQUEST['PSNL_CD'], @$_REQUEST['CERT_TYPE'], @$_REQUEST['ISSUE_DT'], @$_REQUEST['CERT_DTL'], $regDt, $_REQUEST['CERT_CD']]
        );
    }
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'R') {
    $sql = "SELECT A.*,B.PSNL_NM,C.ORG_NM,P.POSITION FROM BONDANG_HR.CERTIFICATE_HISTORY A
        LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
            SELECT TRS_CD FROM PSNL_TRANSFER AS P2
            WHERE P2.PSNL_CD = A.PSNL_CD ORDER BY P2.REG_DT DESC LIMIT 1
        )
        LEFT OUTER JOIN ORG_INFO C ON P.ORG_CD = C.ORG_CD";
    $params = [];
    $types = "";
    $whereSql = " WHERE 1=1 ";
    if (@$_REQUEST['CERT_CD']) {
        $whereSql .= " AND CERT_CD = ?";
        $params[] = $_REQUEST['CERT_CD'];
        $types .= "s";
    }
    $data = executeQuery($conn, $sql . $whereSql . " LIMIT 1", $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "date" => "2021-99-99"]);
}
else if ($_REQUEST['CRUD'] == 'D') {
    executeUpdate($conn, "DELETE FROM BONDANG_HR.CERTIFICATE_HISTORY WHERE CERT_CD = ?", "s", [$_REQUEST['CERT_CD']]);
    mysqli_close($conn);
}
else {
    echo 'certPrintConfig 잘못된 접근방식입니다.';
}

?>