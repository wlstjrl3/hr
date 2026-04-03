<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
$inputs = file_get_contents("php://input");
$param = json_decode($inputs, true); // JSON을 PHP 배열로 변환

$placeholders = [];
$params = [];
$types = "";

foreach ($param['psnlCd'] as $key => $row) {
    // INSERT 안에 있는 서브쿼리에도 파라미터를 사용하기 위해 구조 변경
    $placeholders[] = "(?, ?, 
        (SELECT GRD_GRADE FROM BONDANG_HR.GRADE_HISTORY WHERE PSNL_CD=? ORDER BY ADVANCE_DT DESC LIMIT 1),
        (SELECT GRD_PAY+1 FROM BONDANG_HR.GRADE_HISTORY WHERE PSNL_CD=? ORDER BY ADVANCE_DT DESC LIMIT 1),
        '일괄 호봉 갱신처리', ?)";

    $params[] = @$row; // PSNL_CD
    $params[] = @$param['date']; // ADVANCE_DT
    $params[] = @$row; // 서브쿼리 1 PSNL_CD
    $params[] = @$row; // 서브쿼리 2 PSNL_CD
    $params[] = date("Y-m-d h:m:s"); // REG_DT
    $types .= "sssss";
}

if (!empty($placeholders)) {
    $sql = "INSERT INTO BONDANG_HR.GRADE_HISTORY(PSNL_CD, ADVANCE_DT, GRD_GRADE, GRD_PAY, GRD_DTL, REG_DT) VALUES " . implode(',', $placeholders);
    // echo $sql; // 디버그용
    executeUpdate($conn, $sql, $types, $params);
}
mysqli_close($conn);
?>