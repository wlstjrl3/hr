<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
$req = file_get_contents("php://input");
$param = json_decode(json_encode(json_decode($req)), true);

$placeholders = [];
$params = [];
$types = "";

foreach ($param as $key => $row) {
    if (strlen($row['OH_DT']) != 10) {
        $dt = @excelDateToPHPDate($row['OH_DT']);
    }
    else {
        $dt = @$row['OH_DT'];
    }

    $placeholders[] = "(?, ?, ?, ?, ?)";
    $params[] = $dt;
    $params[] = @$row['ORG_NM'];
    $params[] = @$row['PERSON_CNT'];
    $params[] = @$row['ETC'];
    $params[] = date("Y-m-d h:m:s");
    $types .= "sssss";
}

if (!empty($placeholders)) {
    $sql = "INSERT INTO BONDANG_HR.ORG_HISTORY(OH_DT, ORG_CD, PERSON_CNT, ETC, REG_DT) VALUES " . implode(',', $placeholders);
    executeUpdate($conn, $sql, $types, $params);

    $sql2 = "UPDATE BONDANG_HR.ORG_HISTORY A LEFT OUTER JOIN ORG_INFO B ON A.ORG_CD = B.ORG_NM SET A.ORG_CD = B.ORG_CD WHERE A.ORG_CD NOT LIKE '1311%'";
    // 이 쿼리는 사용자 입력값이 포함되어 있지 않으므로 단순 실행
    mysqli_query($conn, $sql2);
}

mysqli_close($conn);

function excelDateToPHPDate($excelDate)
{ /* 엑셀에서 넘어온 자료의 날짜가 숫자로 변환된 경우 재보정 용코드 */
    // 엑셀 기준 날짜: 1900-01-01
    $baseDate = new DateTime('1900-01-01');
    // 엑셀 날짜는 1부터 시작, 1900년 2월 29일 오류 보정을 위해 -2일
    $interval = new DateInterval('P' . ($excelDate - 2) . 'D');
    $baseDate->add($interval);
    return $baseDate->format('Y-m-d');
}

?>