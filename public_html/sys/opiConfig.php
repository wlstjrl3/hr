<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

if ($_REQUEST['CRUD'] == 'C') {
    if ($_REQUEST['OPI_CD'] == "") {
        $regDt = date("Y-m-d h:m:s");
        executeUpdate($conn,
            "INSERT INTO BONDANG_HR.PSNL_OPINION(PSNL_CD,OPI_DT,OPI_PERSON,OPI_DTL,OPI_TYPE,REG_DT) VALUES (?,?,?,?,?,?)",
            "ssssss",
        [$_REQUEST['PSNL_CD'], $_REQUEST['OPI_DT'], $_REQUEST['OPI_PERSON'], $_REQUEST['OPI_DTL'], $_REQUEST['OPI_TYPE'], $regDt]
        );
    }
    else {
        $regDt = date("Y-m-d h:m:s");
        executeUpdate($conn,
            "UPDATE BONDANG_HR.PSNL_OPINION SET PSNL_CD=?,OPI_DT=?,OPI_PERSON=?,OPI_DTL=?,OPI_TYPE=?,REG_DT=? WHERE OPI_CD = ?",
            "sssssss",
        [$_REQUEST['PSNL_CD'], @$_REQUEST['OPI_DT'], @$_REQUEST['OPI_PERSON'], @$_REQUEST['OPI_DTL'], @$_REQUEST['OPI_TYPE'], $regDt, $_REQUEST['OPI_CD']]
        );
    }
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'R') {
    $sql = "SELECT A.*,B.PSNL_NM,C.ORG_NM,P.POSITION FROM BONDANG_HR.PSNL_OPINION A
        LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
            SELECT TRS_CD FROM PSNL_TRANSFER AS P2
            WHERE P2.PSNL_CD = A.PSNL_CD ORDER BY P2.TRS_DT DESC, P2.TRS_CD DESC LIMIT 1
        )
        LEFT OUTER JOIN ORG_INFO C ON P.ORG_CD = C.ORG_CD";
    $params = [];
    $types = "";
    $whereSql = " WHERE 1=1 ";
    if (@$_REQUEST['OPI_CD']) {
        $whereSql .= " AND OPI_CD = ?";
        $params[] = $_REQUEST['OPI_CD'];
        $types .= "s";
    }
    $data = executeQuery($conn, $sql . $whereSql . " LIMIT 1", $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "date" => "2021-99-99"]);
}
else if ($_REQUEST['CRUD'] == 'D') {
    executeUpdate($conn, "DELETE FROM BONDANG_HR.PSNL_OPINION WHERE OPI_CD = ?", "s", [$_REQUEST['OPI_CD']]);
    mysqli_close($conn);
}
else {
    echo 'opiConfig 잘못된 접근방식입니다.';
}

?>