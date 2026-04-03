<!DOCTYPE HTML>
<HTML itemscope itemtype="http://schema.org/WebPage">
	<HEAD>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	<BODY>
<?php
if (empty($_POST['admin-id']) || empty($_POST['admin-password'])) {
    echo "<script> 
            alert('정상적인 접근이 아닙니다.[Err_Code:0]');
            history.back();
        </script>";
    die('정상적인 접근이 아닙니다.');
}
include "./dbconn.php";

$adminId = $_POST['admin-id'];
$adminPw = $_POST['admin-password'];

$stmt = $conn->prepare("SELECT * FROM USER_TB WHERE USER_ID = ?");
$stmt->bind_param("s", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$loginSuccess = false;
$needsMigration = false;

if ($row) {
    if (password_verify($adminPw, $row['USER_PASS'])) {
        $loginSuccess = true;
    } elseif ($row['USER_PASS'] === md5($adminPw)) {
        $loginSuccess = true;
        $needsMigration = true;
    }
}

if (!$loginSuccess) {
    mysqli_close($conn);
    echo "<script> 
        alert('아이디 또는 비밀번호 오류');
        history.back();
        </script>";
    die('아이디 또는 비밀번호 오류');
}

// 비밀번호 마이그레이션 (MD5 -> password_hash)
if ($needsMigration) {
    $newHash = password_hash($adminPw, PASSWORD_DEFAULT);
    $updStmt = $conn->prepare("UPDATE USER_TB SET USER_PASS = ? WHERE USER_ID = ?");
    $updStmt->bind_param("ss", $newHash, $adminId);
    $updStmt->execute();
    $updStmt->close();
}

$stmt->close();
mysqli_close($conn);

session_start();
session_regenerate_id(true);

$_SESSION["USER_ID"] = $row['USER_ID'];
$_SESSION["USER_NM"] = $row['USER_NM'];
$_SESSION["USER_AUTH"] = $row['USER_AUTH'];
$_SESSION["API_TOKEN"] = bin2hex(random_bytes(32)); // API용 보안 토큰 생성

echo "<script> 
        console.log('" . $row['USER_NM'] . "님 환영합니다!');
        document.location.href='" . DIR_ROOT . "/'; 
    </script>";

?>
    </BODY>
</HTML>