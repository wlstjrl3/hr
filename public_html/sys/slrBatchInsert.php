<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
$req = file_get_contents("php://input");
$param = json_decode(json_encode(json_decode($req)), true);

$placeholders = [];
$params = [];
$types = "";

foreach ($param as $key => $row) {
    $placeholders[] = "(?, ?, ?, ?, ?, ?, ?)";
    $params[] = $row['SLR_YEAR'];
    $params[] = $row['SLR_TYPE'];
    $params[] = $row['SLR_GRADE'];
    $params[] = $row['SLR_PAY'];
    $params[] = $row['NORMAL_PAY'];
    $params[] = $row['LEGAL_PAY'];
    $params[] = date("Y-m-d h:m:s");
    $types .= "sssssss";
}

if (!empty($placeholders)) {
    $sql = "INSERT INTO BONDANG_HR.SALARY_TB(SLR_YEAR, SLR_TYPE, SLR_GRADE, SLR_PAY, NORMAL_PAY, LEGAL_PAY, REG_DT) VALUES " . implode(',', $placeholders);
    executeUpdate($conn, $sql, $types, $params);
}

mysqli_close($conn);
?>