<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

if ($_REQUEST['CRUD'] == 'C') {
    if ($_REQUEST['SLR_CD'] == "") { //신규 작성
        executeUpdate($conn,
            "INSERT INTO BONDANG_HR.SALARY_TB(SLR_YEAR, SLR_TYPE, SLR_GRADE, SLR_PAY, NORMAL_PAY, LEGAL_PAY, REG_DT) VALUES (?,?,?,?,?,?,?)",
            "sssssss",
        [$_REQUEST['SLR_YEAR'], $_REQUEST['SLR_TYPE'], $_REQUEST['SLR_GRADE'], $_REQUEST['SLR_PAY'], $_REQUEST['NORMAL_PAY'], $_REQUEST['LEGAL_PAY'], date("Y-m-d h:m:s")]
        );
    }
    else { //기존 데이터 UPDATE
        executeUpdate($conn,
            "UPDATE BONDANG_HR.SALARY_TB SET SLR_YEAR=?, SLR_TYPE=?, SLR_GRADE=?, SLR_PAY=?, NORMAL_PAY=?, LEGAL_PAY=?, REG_DT=? WHERE SLR_CD = ?",
            "ssssssss",
        [$_REQUEST['SLR_YEAR'], $_REQUEST['SLR_TYPE'], $_REQUEST['SLR_GRADE'], $_REQUEST['SLR_PAY'], $_REQUEST['NORMAL_PAY'], $_REQUEST['LEGAL_PAY'], date("Y-m-d h:m:s"), $_REQUEST['SLR_CD']]
        );
    }
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'R') {
    $sql = "SELECT * FROM BONDANG_HR.SALARY_TB";
    $whereSql = " WHERE 1=1 ";
    $params = [];
    $types = "";
    if (@$_REQUEST['SLR_CD']) {
        $whereSql .= " AND SLR_CD = ?";
        $params[] = $_REQUEST['SLR_CD'];
        $types .= "s";
    }
    $limitSql = safeLimit(@$_REQUEST['LIMIT']);
    $data = executeQuery($conn, $sql . $whereSql . $limitSql, $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "date" => "2021-99-99"]);
}
else if ($_REQUEST['CRUD'] == 'D') {
    executeUpdate($conn, "DELETE FROM BONDANG_HR.SALARY_TB WHERE SLR_CD = ?", "s", [$_REQUEST['SLR_CD']]);
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'BD' && strlen($_REQUEST['REG_DT']) == 10) { //일괄 제거 코드를 추가한다.
    $likeStr = $_REQUEST['REG_DT'] . " %";
    $affectedRows = executeUpdate($conn, "DELETE FROM BONDANG_HR.SALARY_TB WHERE REG_DT LIKE ?", "s", [$likeStr]);
    echo "영향을 받은 행의 수: " . $affectedRows;
    mysqli_close($conn);
}
else {
    echo 'userConfig 잘못된 접근방식입니다.';
}

?>