<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

if ($_REQUEST['CRUD'] == 'C') {
    if ($_REQUEST['INS_CD'] == "") { //신규 작성
        $regDt = date("Y-m-d h:m:s");
        executeUpdate($conn,
            "INSERT INTO BONDANG_HR.PSNL_INSURANCE(PSNL_CD,INS_AMOUNT,INS_STT_DT,INS_END_DT,INS_DTL,REG_DT) VALUES (?,?,?,?,?,?)",
            "ssssss",
        [$_REQUEST['PSNL_CD'], $_REQUEST['INS_AMOUNT'], $_REQUEST['INS_STT_DT'], $_REQUEST['INS_END_DT'], $_REQUEST['INS_DTL'], $regDt]
        );
    }
    else { //기존 데이터 UPDATE
        $setSql = "PSNL_CD=?, INS_AMOUNT=?";
        $types = "ss";
        $params = [$_REQUEST['PSNL_CD'], @$_REQUEST['INS_AMOUNT']];

        if (@$_REQUEST['INS_STT_DT']) {
            $setSql .= ",INS_STT_DT=?";
            $types .= "s";
            $params[] = $_REQUEST['INS_STT_DT'];
        }
        else {
            $setSql .= ",INS_STT_DT=NULL";
        }
        if (@$_REQUEST['INS_END_DT']) {
            $setSql .= ",INS_END_DT=?";
            $types .= "s";
            $params[] = $_REQUEST['INS_END_DT'];
        }
        else {
            $setSql .= ",INS_END_DT=NULL";
        }

        $setSql .= ",INS_DTL=?,REG_DT=?";
        $types .= "ss";
        $regDt = date("Y-m-d h:m:s");
        $params[] = @$_REQUEST['INS_DTL'];
        $params[] = $regDt;

        $types .= "s";
        $params[] = $_REQUEST['INS_CD'];

        executeUpdate($conn, "UPDATE BONDANG_HR.PSNL_INSURANCE SET $setSql WHERE INS_CD = ?", $types, $params);
    }
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'R') {
    $sql = "SELECT A.*,B.PSNL_NM,C.ORG_NM,P.POSITION FROM BONDANG_HR.PSNL_INSURANCE A
            LEFT OUTER JOIN PSNL_INFO B ON A.PSNL_CD = B.PSNL_CD
            LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
                SELECT TRS_CD FROM PSNL_TRANSFER AS P2
                WHERE P2.PSNL_CD = A.PSNL_CD
                ORDER BY P2.TRS_DT DESC, P2.TRS_CD DESC
                LIMIT 1
            )
            LEFT OUTER JOIN ORG_INFO C ON P.ORG_CD = C.ORG_CD
            ";
    $whereSql = " WHERE 1=1 ";
    $params = [];
    $types = "";
    if (@$_REQUEST['INS_CD']) {
        $whereSql .= " AND INS_CD = ?";
        $params[] = $_REQUEST['INS_CD'];
        $types .= "s";
    }

    $data = executeQuery($conn, $sql . $whereSql . " LIMIT 1", $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "date" => "2021-99-99"]);
}
else if ($_REQUEST['CRUD'] == 'D') {
    executeUpdate($conn, "DELETE FROM BONDANG_HR.PSNL_INSURANCE WHERE INS_CD = ?", "s", [$_REQUEST['INS_CD']]);
    mysqli_close($conn);
}
else {
    echo 'insConfig 잘못된 접근방식입니다.';
}

?>