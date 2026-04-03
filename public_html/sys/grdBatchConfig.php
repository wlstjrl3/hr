<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);
$inputs = file_get_contents("php://input");
$param = json_decode($inputs, true); // JSON을 PHP 배열로 변환

if ($_REQUEST['CRUD'] == "C") {
    $placeholders = [];
    $params = [];
    $types = "";

    foreach ($param['psnlCd'] as $psnl_cd) {
        // 최신 GRD_GRADE와 GRD_PAY 조회 (안전한 Prepared Statement)
        $query = "SELECT GRD_GRADE, GRD_PAY FROM BONDANG_HR.GRADE_HISTORY WHERE PSNL_CD = ? ORDER BY ADVANCE_DT DESC LIMIT 1";
        $data = executeQuery($conn, $query, "s", [$psnl_cd]);

        if (!empty($data)) {
            $grd_grade = $data[0]['GRD_GRADE'];
            $grd_pay = $data[0]['GRD_PAY'] + 1;
            $advance_dt = @$param['date'];

            $placeholders[] = "(?, ?, ?, ?, '일괄 호봉 갱신처리', ?)";
            $params[] = $psnl_cd;
            $params[] = $advance_dt;
            $params[] = $grd_grade;
            $params[] = $grd_pay;
            $params[] = date("Y-m-d H:i:s");
            $types .= "sssss";
        }
    }

    if (!empty($placeholders)) {
        $sql = "INSERT INTO BONDANG_HR.GRADE_HISTORY (PSNL_CD, ADVANCE_DT, GRD_GRADE, GRD_PAY, GRD_DTL, REG_DT) VALUES " . implode(',', $placeholders);
        $affectedRows = executeUpdate($conn, $sql, $types, $params);
        echo $affectedRows . "건의 데이터 일괄처리 완료";
    }
}
else if ($_REQUEST['CRUD'] == "D") {
    if (!empty($param['grdCd'])) {
        $placeholders = implode(',', array_fill(0, count($param['grdCd']), '?'));
        $types = str_repeat('s', count($param['grdCd']));

        $sql = "DELETE FROM BONDANG_HR.GRADE_HISTORY WHERE GRD_CD IN ($placeholders)";
        $affectedRows = executeUpdate($conn, $sql, $types, $param['grdCd']);
        echo $affectedRows . "건의 데이터 일괄삭제 완료";
    }
}
mysqli_close($conn);
?>