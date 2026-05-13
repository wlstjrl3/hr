<?php
include "sql_safe_helper.php";
verifyApiKey($conn);

$userAuth = $_SESSION['USER_AUTH'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($userAuth != 'admin') {
        die(json_encode(["result" => "error", "message" => "권한이 없습니다."]));
    }
    
    $smtpHost = $_POST['SMTP_HOST'] ?? '';
    $smtpPort = $_POST['SMTP_PORT'] ?? 0;
    $smtpUser = $_POST['SMTP_USER'] ?? '';
    $smtpPass = $_POST['SMTP_PASS'] ?? '';
    $smtpSecure = $_POST['SMTP_SECURE'] ?? '';

    // Always update the first row (or insert if empty)
    $check = executeQuery($conn, "SELECT COUNT(*) as cnt FROM TB_SMTP_CONFIG");
    if ($check[0]['cnt'] == 0) {
        $sql = "INSERT INTO TB_SMTP_CONFIG (SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_SECURE) VALUES (?, ?, ?, ?, ?)";
        executeUpdate($conn, $sql, "sisss", [$smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpSecure]);
    } else {
        $sql = "UPDATE TB_SMTP_CONFIG SET SMTP_HOST=?, SMTP_PORT=?, SMTP_USER=?, SMTP_PASS=?, SMTP_SECURE=?";
        executeUpdate($conn, $sql, "sisss", [$smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpSecure]);
    }
    
    jsonResponse($conn, ["result" => "success"]);
} else {
    // READ
    $data = executeQuery($conn, "SELECT * FROM TB_SMTP_CONFIG LIMIT 1");
    jsonResponse($conn, ["result" => "success", "data" => $data[0] ?? null]);
}
?>
