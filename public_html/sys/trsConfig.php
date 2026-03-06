<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

if ($_REQUEST['CRUD'] == 'C') {
    if ($_REQUEST['TRS_CD'] == "") {
        $cols = "PSNL_CD,ORG_CD,WORK_TYPE,POSITION,TRS_TYPE,TRS_DTL,TRS_DT,REG_DT";
        $placeholders = "?,?,?,?,?,?,?,?";
        $types = "ssssssss";
        $regDt = date("Y-m-d h:m:s");
        $params = [$_REQUEST['PSNL_CD'], $_REQUEST['ORG_CD'], $_REQUEST['WORK_TYPE'], $_REQUEST['POSITION'],
            $_REQUEST['TRS_TYPE'], $_REQUEST['TRS_DTL'], $_REQUEST['TRS_DT'], $regDt];

        if (@$_REQUEST['APP_DT']) {
            $cols .= ",APP_DT";
            $placeholders .= ",?";
            $types .= "s";
            $params[] = $_REQUEST['APP_DT'];
        }
        executeUpdate($conn, "INSERT INTO BONDANG_HR.PSNL_TRANSFER($cols) VALUES ($placeholders)", $types, $params);
    }
    else {
        $setSql = "PSNL_CD=?, ORG_CD=?, WORK_TYPE=?, POSITION=?, TRS_TYPE=?, TRS_DTL=?, TRS_DT=?";
        $types = "sssssss";
        $params = [$_REQUEST['PSNL_CD'], @$_REQUEST['ORG_CD'], @$_REQUEST['WORK_TYPE'], @$_REQUEST['POSITION'],
            @$_REQUEST['TRS_TYPE'], @$_REQUEST['TRS_DTL'], @$_REQUEST['TRS_DT']];

        if (@$_REQUEST['APP_DT']) {
            $setSql .= ", APP_DT=?";
            $types .= "s";
            $params[] = $_REQUEST['APP_DT'];
        }
        $setSql .= ", REG_DT=?";
        $types .= "s";
        $params[] = date("Y-m-d h:m:s");

        $types .= "s";
        $params[] = $_REQUEST['TRS_CD'];

        executeUpdate($conn, "UPDATE BONDANG_HR.PSNL_TRANSFER SET $setSql WHERE TRS_CD = ?", $types, $params);
    }
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'R') {
    $sql = "SELECT A.*,B.PSNL_NM,D.ORG_NM FROM BONDANG_HR.PSNL_TRANSFER A
        LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
        LEFT OUTER JOIN ORG_INFO D ON A.ORG_CD = D.ORG_CD";
    $whereSql = " WHERE 1=1 ";
    $params = [];
    $types = "";
    if (@$_REQUEST['TRS_CD']) {
        $whereSql .= " AND TRS_CD = ?";
        $params[] = $_REQUEST['TRS_CD'];
        $types .= "s";
    }
    $data = executeQuery($conn, $sql . $whereSql . " LIMIT 1", $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "date" => "2021-99-99"]);
}
else if ($_REQUEST['CRUD'] == 'D') {
    executeUpdate($conn, "DELETE FROM BONDANG_HR.PSNL_TRANSFER WHERE TRS_CD = ?", "s", [$_REQUEST['TRS_CD']]);
    mysqli_close($conn);
}
else {
    echo 'trsConfig 잘못된 접근방식입니다.';
}

?>