<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

if ($_REQUEST['CRUD'] == 'C') {
    if ($_REQUEST['ADJ_CD'] == "") { //신규 작성
        $cols = "PSNL_CD,ADJ_TYPE,ADJ_NM,ADJ_LEVEL,ADJ_NUM,ADJ_DTL";
        $placeholders = "?,?,?,?,?,?";
        $types = "ssssss";
        $params = [$_REQUEST['PSNL_CD'], $_REQUEST['ADJ_TYPE'], $_REQUEST['ADJ_NM'],
            $_REQUEST['ADJ_LEVEL'], $_REQUEST['ADJ_NUM'], @$_REQUEST['ADJ_DTL']];

        if (@$_REQUEST['ADJ_PAY']) {
            $cols .= ",ADJ_PAY";
            $placeholders .= ",?";
            $types .= "s";
            $params[] = $_REQUEST['ADJ_PAY'];
        }
        if (@$_REQUEST['ADJ_GET_DT']) {
            $cols .= ",ADJ_GET_DT";
            $placeholders .= ",?";
            $types .= "s";
            $params[] = $_REQUEST['ADJ_GET_DT'];
        }
        if (@$_REQUEST['ADJ_STT_DT']) {
            $cols .= ",ADJ_STT_DT";
            $placeholders .= ",?";
            $types .= "s";
            $params[] = $_REQUEST['ADJ_STT_DT'];
        }
        if (@$_REQUEST['ADJ_END_DT']) {
            $cols .= ",ADJ_END_DT";
            $placeholders .= ",?";
            $types .= "s";
            $params[] = $_REQUEST['ADJ_END_DT'];
        }

        $cols .= ",REG_DT";
        $placeholders .= ",?";
        $types .= "s";
        $params[] = date("Y-m-d h:m:s");
        executeUpdate($conn, "INSERT INTO BONDANG_HR.PSNL_ADJUST($cols) VALUES ($placeholders)", $types, $params);
    }
    else { //기존 데이터 UPDATE
        $setSql = "PSNL_CD=?,ADJ_TYPE=?,ADJ_NM=?,ADJ_LEVEL=?,ADJ_NUM=?,ADJ_DTL=?";
        $types = "ssssss";
        $params = [$_REQUEST['PSNL_CD'], $_REQUEST['ADJ_TYPE'], $_REQUEST['ADJ_NM'],
            $_REQUEST['ADJ_LEVEL'], $_REQUEST['ADJ_NUM'], @$_REQUEST['ADJ_DTL']];

        if (@$_REQUEST['ADJ_PAY']) {
            $setSql .= ",ADJ_PAY=?";
            $types .= "s";
            $params[] = $_REQUEST['ADJ_PAY'];
        }
        if (@$_REQUEST['ADJ_GET_DT']) {
            $setSql .= ",ADJ_GET_DT=?";
            $types .= "s";
            $params[] = $_REQUEST['ADJ_GET_DT'];
        }
        else {
            $setSql .= ",ADJ_GET_DT=NULL";
        }
        if (@$_REQUEST['ADJ_STT_DT']) {
            $setSql .= ",ADJ_STT_DT=?";
            $types .= "s";
            $params[] = $_REQUEST['ADJ_STT_DT'];
        }
        else {
            $setSql .= ",ADJ_STT_DT=NULL";
        }
        if (@$_REQUEST['ADJ_END_DT']) {
            $setSql .= ",ADJ_END_DT=?";
            $types .= "s";
            $params[] = $_REQUEST['ADJ_END_DT'];
        }
        else {
            $setSql .= ",ADJ_END_DT=NULL";
        }

        $setSql .= ",REG_DT=?";
        $types .= "s";
        $params[] = date("Y-m-d h:m:s");
        $types .= "s";
        $params[] = $_REQUEST['ADJ_CD'];
        executeUpdate($conn, "UPDATE BONDANG_HR.PSNL_ADJUST SET $setSql WHERE ADJ_CD = ?", $types, $params);
    }
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'R') {
    $sql = "SELECT A.*,B.PSNL_NM,C.ORG_NM,P.POSITION FROM BONDANG_HR.PSNL_ADJUST A
        LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
        LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
            SELECT TRS_CD FROM PSNL_TRANSFER AS P2
            WHERE P2.PSNL_CD = A.PSNL_CD ORDER BY P2.TRS_DT DESC, P2.TRS_CD DESC LIMIT 1
        )
        LEFT OUTER JOIN ORG_INFO C ON P.ORG_CD = C.ORG_CD";
    $whereSql = " WHERE 1=1 ";
    $params = [];
    $types = "";
    if (@$_REQUEST['ADJ_CD']) {
        $whereSql .= " AND ADJ_CD = ?";
        $params[] = $_REQUEST['ADJ_CD'];
        $types .= "s";
    }
    $data = executeQuery($conn, $sql . $whereSql . " LIMIT 1", $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "date" => "2021-99-99"]);
}
else if ($_REQUEST['CRUD'] == 'D') {
    executeUpdate($conn, "DELETE FROM BONDANG_HR.PSNL_ADJUST WHERE ADJ_CD = ?", "s", [$_REQUEST['ADJ_CD']]);
    mysqli_close($conn);
}
else {
    echo 'ADJConfig 잘못된 접근방식입니다.';
}

?>