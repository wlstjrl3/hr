<?php
/**
 * 제증명서 이메일 발송 처리 (SMTP 기반)
 * 설정 파일(.env)의 정보를 읽어와 발송합니다.
 */

// 라이브러리 경로
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. 공통 환경 설정 및 라이브러리 로드
include "sql_safe_helper.php";
verifyApiKey($conn);

// 2. 필수 라이브러리 파일 포함 (변경된 위치: assets/lib)
$libPath = __DIR__ . '/../assets/lib/PHPMailer/src/';
if (file_exists($libPath . 'Exception.php')) {
    require $libPath . 'Exception.php';
    require $libPath . 'PHPMailer.php';
    require $libPath . 'SMTP.php';
} else {
    die(json_encode(["result" => "error", "message" => "PHPMailer library not found at $libPath."]));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["result" => "error", "message" => "Invalid request method"]));
}

// 3. 파라미터 수집
$issueNo  = $_POST['ISSUE_NO'] ?? '';
$toEmail  = $_POST['EMAIL'] ?? '';
$orgNm    = $_POST['ORG_NM'] ?? '';
$psnlNm   = $_POST['PSNL_NM'] ?? '';
$certType = $_POST['CERT_TYPE'] ?? '';
$pdfData  = $_POST['PDF_DATA'] ?? '';

if (empty($toEmail) || empty($pdfData)) {
    die(json_encode(["result" => "error", "message" => "Required data missing"]));
}

// 4. PDF 데이터 가공 (Data URI -> Binary)
// 형식: data:application/pdf;filename=generated.pdf;base64,JVBERi0xLjQK...
$pdfParts = explode('base64,', $pdfData);
if (count($pdfParts) < 2) {
    die(json_encode(["result" => "error", "message" => "Invalid PDF data"]));
}
$binaryData = base64_decode($pdfParts[1]);

// 5. PHPMailer를 통한 SMTP 발송
$mail = new PHPMailer(true);

try {
    // 서버 설정 (dbconn.php에서 로드된 변수 사용)
    $mail->isSMTP();
    $mail->Host       = $smtpHost;                  // smtp.casuwon.or.kr
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser;                  // v1-samu
    $mail->Password   = $smtpPass;                  // .env 비밀번호
    $mail->SMTPSecure = strtolower($smtpSecure);    // tls
    $mail->Port       = $smtpPort;                  // 587
    $mail->CharSet    = 'UTF-8';

    // 수신자 설정
    $mail->setFrom('v1-samu@casuwon.or.kr', '제1대리구 인사관리시스템');
    $mail->addAddress($toEmail, $psnlNm);

    // 첨부 파일 등록
    $fileName = "Certificate_{$issueNo}.pdf";
    $mail->addStringAttachment($binaryData, $fileName, 'base64', 'application/pdf');

    // 메일 콘텐츠 설정 (HTML)
    $mail->isHTML(true);
    $mail->Subject = "[제증명서 발송] {$orgNm} - {$psnlNm} ({$certType})";
    
    $mail->Body = "
        <div style='font-family: sans-serif; line-height: 1.6;'>
            <h2 style='color: #2c3e50;'>제증명서 발송 안내</h2>
            <p>안녕하세요, <b>제1대리구 인사관리시스템</b>입니다.</p>
            <p>요청하신 제증명서 파일(PDF)을 첨부하여 보내드립니다.</p>
            <div style='background: #f8f9fa; padding: 15px; border-left: 5px solid #3498db; margin: 20px 0;'>
                <ul style='list-style: none; padding: 0;'>
                    <li><b>발급번호:</b> {$issueNo}</li>
                    <li><b>대상자:</b> {$psnlNm}</li>
                    <li><b>증명서 종류:</b> {$certType}증명서</li>
                    <li><b>발송 기관:</b> {$orgNm}</li>
                </ul>
            </div>
            <p>첨부된 파일을 확인해 주시기 바랍니다.</p>
            <p>감사합니다.</p>
        </div>";

    $mail->send();

    // 6. DB의 메모(MEMO) 컬럼에 발송 기록 저장
    $sendHistory = "\n[메일발송: {$toEmail} (" . date('Y-m-d H:i') . ")]";
    $updateSql = "UPDATE TB_CERT_PRINT SET MEMO = CONCAT(IFNULL(MEMO,''), ?) WHERE ISSUE_NO = ?";
    executeUpdate($conn, $updateSql, "ss", [$sendHistory, $issueNo]);

    echo json_encode(["result" => "success", "message" => "Email sent successfully via SMTP"]);

} catch (Exception $e) {
    echo json_encode(["result" => "error", "message" => "SMTP Error: {$mail->ErrorInfo}"]);
}

mysqli_close($conn);
?>
