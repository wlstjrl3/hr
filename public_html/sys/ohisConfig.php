<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

if ($_REQUEST['CRUD'] == 'C') {
    $regDt = date("Y-m-d h:m:s");
    if ($_REQUEST['OH_CD'] == "") { //신규 작성
        executeUpdate($conn,
            "INSERT INTO BONDANG_HR.ORG_HISTORY(OH_DT, ORG_CD, PERSON_CNT, ETC, REG_DT) VALUES (?,?,?,?,?)",
            "sssss",
        [$_REQUEST['OH_DT'], $_REQUEST['ORG_CD'], $_REQUEST['PERSON_CNT'], $_REQUEST['ETC'], $regDt]
        );
    }
    else { //기존 데이터 UPDATE
        executeUpdate($conn,
            "UPDATE BONDANG_HR.ORG_HISTORY SET OH_DT=?, ORG_CD=?, PERSON_CNT=?, ETC=?, REG_DT=? WHERE OH_CD = ?",
            "ssssss",
        [$_REQUEST['OH_DT'], $_REQUEST['ORG_CD'], $_REQUEST['PERSON_CNT'], $_REQUEST['ETC'], $regDt, $_REQUEST['OH_CD']]
        );
    }
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'R') {
    //기본 쿼리
    $sql = "SELECT B.ORG_NM,A.* FROM BONDANG_HR.ORG_HISTORY A LEFT OUTER JOIN ORG_INFO B ON A.ORG_CD = B.ORG_CD";
    $whereSql = " WHERE 1=1 ";
    $params = [];
    $types = "";
    if (@$_REQUEST['OH_CD']) {
        $whereSql .= " AND OH_CD = ?";
        $params[] = $_REQUEST['OH_CD'];
        $types .= "s";
    }
    $data = executeQuery($conn, $sql . $whereSql . " LIMIT 1", $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "date" => "2021-99-99"]);
}
else if ($_REQUEST['CRUD'] == 'D') {
    // DELETE
    executeUpdate($conn, "DELETE FROM BONDANG_HR.ORG_HISTORY WHERE OH_CD = ?", "s", [$_REQUEST['OH_CD']]);
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'BD' && strlen($_REQUEST['REG_DT']) == 10) { //일괄 제거
    // DELETE 
    $likeStr = $_REQUEST['REG_DT'] . " %";
    $affectedRows = executeUpdate($conn, "DELETE FROM BONDANG_HR.ORG_HISTORY WHERE REG_DT LIKE ?", "s", [$likeStr]);
    echo "영향을 받은 행의 수: " . $affectedRows;
    mysqli_close($conn);
}
else {
    echo 'userConfig 잘못된 접근방식입니다.';
}
?>