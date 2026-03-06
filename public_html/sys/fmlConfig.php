<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

if ($_REQUEST['CRUD'] == 'C') {
    if ($_REQUEST['FML_CD'] == "") {
        $cols = "PSNL_CD,FML_NM,FML_RELATION,FML_BIRTH,FML_DTL";
        $placeholders = "?,?,?,?,?";
        $types = "sssss";
        $params = [$_REQUEST['PSNL_CD'], $_REQUEST['FML_NM'], $_REQUEST['FML_RELATION'], $_REQUEST['FML_BIRTH'], @$_REQUEST['FML_DTL']];

        if (@$_REQUEST['FML_PAY']) {
            $cols .= ",FML_PAY";
            $placeholders .= ",?";
            $types .= "s";
            $params[] = $_REQUEST['FML_PAY'];
        }
        if (@$_REQUEST['FML_STT_DT']) {
            $cols .= ",FML_STT_DT";
            $placeholders .= ",?";
            $types .= "s";
            $params[] = $_REQUEST['FML_STT_DT'];
        }
        if (@$_REQUEST['FML_END_DT']) {
            $cols .= ",FML_END_DT";
            $placeholders .= ",?";
            $types .= "s";
            $params[] = $_REQUEST['FML_END_DT'];
        }

        $cols .= ",REG_DT";
        $placeholders .= ",?";
        $types .= "s";
        $params[] = date("Y-m-d h:m:s");
        executeUpdate($conn, "INSERT INTO BONDANG_HR.PSNL_FAMILY($cols) VALUES ($placeholders)", $types, $params);
    }
    else {
        $setSql = "PSNL_CD=?,FML_NM=?,FML_RELATION=?,FML_BIRTH=?,FML_DTL=?";
        $types = "sssss";
        $params = [$_REQUEST['PSNL_CD'], $_REQUEST['FML_NM'], $_REQUEST['FML_RELATION'], $_REQUEST['FML_BIRTH'], @$_REQUEST['FML_DTL']];

        if (@$_REQUEST['FML_PAY']) {
            $setSql .= ",FML_PAY=?";
            $types .= "s";
            $params[] = $_REQUEST['FML_PAY'];
        }
        if (@$_REQUEST['FML_STT_DT']) {
            $setSql .= ",FML_STT_DT=?";
            $types .= "s";
            $params[] = $_REQUEST['FML_STT_DT'];
        }
        else {
            $setSql .= ",FML_STT_DT=NULL";
        }
        if (@$_REQUEST['FML_END_DT']) {
            $setSql .= ",FML_END_DT=?";
            $types .= "s";
            $params[] = $_REQUEST['FML_END_DT'];
        }
        else {
            $setSql .= ",FML_END_DT=NULL";
        }

        $setSql .= ",REG_DT=?";
        $types .= "s";
        $params[] = date("Y-m-d h:m:s");
        $types .= "s";
        $params[] = $_REQUEST['FML_CD'];
        executeUpdate($conn, "UPDATE BONDANG_HR.PSNL_FAMILY SET $setSql WHERE FML_CD = ?", $types, $params);
    }
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'R') {
    $sql = "SELECT A.*,B.PSNL_NM,C.ORG_NM,P.POSITION FROM BONDANG_HR.PSNL_FAMILY A
        LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
            SELECT TRS_CD FROM PSNL_TRANSFER AS P2
            WHERE P2.PSNL_CD = A.PSNL_CD ORDER BY P2.REG_DT DESC LIMIT 1
        )
        LEFT OUTER JOIN ORG_INFO C ON P.ORG_CD = C.ORG_CD";
    $whereSql = " WHERE 1=1 ";
    $params = [];
    $types = "";
    if (@$_REQUEST['FML_CD']) {
        $whereSql .= " AND FML_CD = ?";
        $params[] = $_REQUEST['FML_CD'];
        $types .= "s";
    }
    $data = executeQuery($conn, $sql . $whereSql . " LIMIT 1", $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "date" => "2021-99-99"]);
}
else if ($_REQUEST['CRUD'] == 'D') {
    executeUpdate($conn, "DELETE FROM BONDANG_HR.PSNL_FAMILY WHERE FML_CD = ?", "s", [$_REQUEST['FML_CD']]);
    mysqli_close($conn);
}
else {
    echo 'fmlConfig 잘못된 접근방식입니다.';
}

?>