<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

if ($_REQUEST['CRUD'] == 'C') {
    $issueDt = $_REQUEST['ISSUE_DT'] ? $_REQUEST['ISSUE_DT'] : date('Y-m-d');
    $year = date('Y', strtotime($issueDt));
    
    // 자동 발급번호 채번 (예: 제 2026-001 호)
    $seqSql = "SELECT ISSUE_NO FROM BONDANG_HR.TB_TUITION_ISSUE WHERE ISSUE_NO LIKE '제 {$year}-% 호' ORDER BY ISSUE_CD DESC LIMIT 1";
    $seqRes = mysqli_query($conn, $seqSql);
    $seqNo = 1;
    if ($row = mysqli_fetch_assoc($seqRes)) {
        preg_match('/제 \d{4}-(\d{3}) 호/', $row['ISSUE_NO'], $matches);
        if (isset($matches[1])) {
            $seqNo = intval($matches[1]) + 1;
        }
    }
    $issueNo = sprintf("제 %s-%03d 호", $year, $seqNo);

    $sql = "INSERT INTO BONDANG_HR.TB_TUITION_ISSUE (ISSUE_NO, PSNL_CD, FML_CD, ISSUE_DT, ISSUE_AMT, SCHOOL_GRADE, MEMO) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $types = "ssisiss";
    $params = [
        $issueNo,
        $_REQUEST['PSNL_CD'],
        $_REQUEST['FML_CD'],
        $issueDt,
        $_REQUEST['ISSUE_AMT'] ?: 0,
        @$_REQUEST['SCHOOL_GRADE'],
        @$_REQUEST['MEMO']
    ];
    executeUpdate($conn, $sql, $types, $params);
    mysqli_close($conn);

} else if ($_REQUEST['CRUD'] == 'R') {
    // 단건 조회 필요 시 구현
    $sql = "SELECT * FROM BONDANG_HR.TB_TUITION_ISSUE WHERE ISSUE_CD = ?";
    $data = executeQuery($conn, $sql, "i", [$_REQUEST['ISSUE_CD']]);
    jsonResponse($conn, ["data" => $data ?: null]);

} else if ($_REQUEST['CRUD'] == 'U') {
    $sql = "UPDATE BONDANG_HR.TB_TUITION_ISSUE SET ISSUE_DT=?, ISSUE_AMT=?, SCHOOL_GRADE=?, MEMO=? WHERE ISSUE_CD=?";
    $types = "sissi";
    $params = [
        $_REQUEST['ISSUE_DT'],
        $_REQUEST['ISSUE_AMT'] ?: 0,
        @$_REQUEST['SCHOOL_GRADE'],
        @$_REQUEST['MEMO'],
        $_REQUEST['ISSUE_CD']
    ];
    executeUpdate($conn, $sql, $types, $params);
    mysqli_close($conn);

} else if ($_REQUEST['CRUD'] == 'D') {
    $sql = "DELETE FROM BONDANG_HR.TB_TUITION_ISSUE WHERE ISSUE_CD=?";
    $types = "i";
    $params = [$_REQUEST['ISSUE_CD']];
    executeUpdate($conn, $sql, $types, $params);
    mysqli_close($conn);

} else {
    echo '잘못된 접근방식입니다.';
}
?>
