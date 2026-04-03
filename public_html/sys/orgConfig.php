<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

if ($_REQUEST['CRUD'] == 'C') {
    $now = date("Y-m-d h:m:s");
    $sql = "INSERT INTO BONDANG_HR.ORG_INFO(ORG_CD, ORG_NM, UPPR_ORG_CD, ORG_TYPE, ORG_IN_TEL, ORG_OUT_TEL, REFRESH_DT, REG_DT) 
            VALUES (?,?,?,?,?,?,?,?) 
            ON DUPLICATE KEY UPDATE 
            ORG_NM=?, UPPR_ORG_CD=?, ORG_TYPE=?, ORG_IN_TEL=?, ORG_OUT_TEL=?, REFRESH_DT=?";

    $types = "ssssssssssssss";
    $params = [
        $_REQUEST['ORG_CD'], $_REQUEST['ORG_NM'], $_REQUEST['UPPR_ORG_CD'], $_REQUEST['ORG_TYPE'], $_REQUEST['ORG_IN_TEL'], $_REQUEST['ORG_OUT_TEL'], $now, $now,
        $_REQUEST['ORG_NM'], $_REQUEST['UPPR_ORG_CD'], $_REQUEST['ORG_TYPE'], $_REQUEST['ORG_IN_TEL'], $_REQUEST['ORG_OUT_TEL'], $now
    ];

    executeUpdate($conn, $sql, $types, $params);
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'R') {
    //기본 쿼리
    $sql = "SELECT B.ORG_NM AS UPR_ORG_NM, A.* FROM BONDANG_HR.ORG_INFO A LEFT OUTER JOIN BONDANG_HR.ORG_INFO B ON A.UPPR_ORG_CD = B.ORG_CD";
    $whereSql = " WHERE 1=1 ";
    $params = [];
    $types = "";
    if (@$_REQUEST['ORG_CD']) {
        $whereSql .= " AND A.ORG_CD = ?";
        $params[] = $_REQUEST['ORG_CD'];
        $types .= "s";
    }

    $data = executeQuery($conn, $sql . $whereSql . " LIMIT 1", $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "date" => "2021-99-99"]);
}
else if ($_REQUEST['CRUD'] == 'D') {
    // DELETE
    executeUpdate($conn, "DELETE FROM BONDANG_HR.ORG_INFO WHERE ORG_CD = ?", "s", [$_REQUEST['ORG_CD']]);
    mysqli_close($conn);
}
else {
    echo 'ongConfig 잘못된 접근방식입니다.';
}
?>