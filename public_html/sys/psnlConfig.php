<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

if ($_REQUEST['CRUD'] == 'C') {
    if ($_REQUEST['PSNL_NUM'] != "") { //주민번호가 기입되어 있다면 중복데이터 존재여부를 체크한다.
        $dupChk = executeQuery($conn, "SELECT 1 FROM BONDANG_HR.PSNL_INFO WHERE PSNL_CD != ? AND PSNL_NUM = ? LIMIT 1", "ss", [@$_REQUEST['PSNL_CD'], $_REQUEST['PSNL_NUM']]);
        if (!empty($dupChk)) {
            echo "중복되는 주민번호가 존재합니다.";
            die;
        }
    }

    if ($_REQUEST['PSNL_CD'] == "") { //신규 작성
        executeUpdate($conn,
            "INSERT INTO BONDANG_HR.PSNL_INFO(PSNL_NM, BAPT_NM, PHONE_NUM, PSNL_NUM, REG_DT, REG_PER) VALUES (?,?,?,?,?,?)",
            "ssssss",
        [$_REQUEST['PSNL_NM'], $_REQUEST['BAPT_NM'], $_REQUEST['PHONE_NUM'], $_REQUEST['PSNL_NUM'], date("Y-m-d h:m:s"), 'regPer']
        );
    }
    else { //기존 데이터 UPDATE
        executeUpdate($conn,
            "UPDATE BONDANG_HR.PSNL_INFO SET PSNL_NM=?, BAPT_NM=?, PHONE_NUM=?, PSNL_NUM=?, REG_DT=?, REG_PER=? WHERE PSNL_CD=?",
            "sssssss",
        [$_REQUEST['PSNL_NM'], $_REQUEST['BAPT_NM'], $_REQUEST['PHONE_NUM'], $_REQUEST['PSNL_NUM'], date("Y-m-d h:m:s"), 'edtPer', $_REQUEST['PSNL_CD']]
        );
    }
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'R') {
    $sql = "SELECT B.ORG_NM,A.* FROM BONDANG_HR.PSNL_INFO A
            LEFT OUTER JOIN PSNL_TRANSFER P ON P.TRS_CD = (
                SELECT TRS_CD FROM PSNL_TRANSFER AS P2
                WHERE P2.PSNL_CD = A.PSNL_CD ORDER BY P2.TRS_DT DESC, P2.TRS_CD DESC LIMIT 1
            )
     LEFT OUTER JOIN BONDANG_HR.ORG_INFO B ON P.ORG_CD = B.ORG_CD";
    $whereSql = " WHERE 1=1 ";
    $params = [];
    $types = "";
    if (@$_REQUEST['PSNL_CD']) {
        $whereSql .= " AND A.PSNL_CD = ?";
        $params[] = $_REQUEST['PSNL_CD'];
        $types .= "s";
    }
    $data = executeQuery($conn, $sql . $whereSql . " LIMIT 1", $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "date" => "2021-99-99"]);
}
else if ($_REQUEST['CRUD'] == 'D') {
    executeUpdate($conn, "DELETE FROM BONDANG_HR.PSNL_INFO WHERE PSNL_CD = ?", "s", [$_REQUEST['PSNL_CD']]);
    mysqli_close($conn);
}
else {
    echo 'userConfig 잘못된 접근방식입니다.';
}

?>