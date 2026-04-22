<?php
/**
 * sys/*.php 공통 헬퍼 모듈
 * - DB 연결 초기화
 * - 보안 검증 (API Key)
 * - ORDER BY / LIMIT 안전 검증
 * - Prepared Statement 실행 헬퍼
 */

error_reporting(E_ALL);
ini_set("display_errors", 0);
ini_set("log_errors", 1);
session_start();

// Fatal Error 발생 시에도 JSON 형태로 에러 반환 (진단용)
ob_start();
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => true,
            'message' => $error['message'],
            'file' => basename($error['file']),
            'line' => $error['line']
        ], JSON_UNESCAPED_UNICODE);
    } else {
        ob_end_flush();
    }
});
include __DIR__ . "/../dbconn/dbconn.php";

/**
 * API Key 보안 검증을 수행합니다 (세션 기반).
 * 검증 실패 시 스크립트를 종료합니다.
 * 
 * @param mysqli $conn DB 연결 객체
 * @param string $key (Deprecated) 이전 버전 호환성을 위해 남겨둠
 */
function verifyApiKey($conn, $key = "")
{
    if (!isset($_SESSION['API_TOKEN'])) {
        die("Authentication Required");
    }
}

/**
 * 날짜 파라미터를 검증하여 안전한 YYYY-MM-DD 형식을 반환합니다.
 * 
 * @param string $date 날짜 문자열
 * @return string 검증된 날짜 문자열 또는 오늘 날짜
 */
function safeDateParam($date)
{
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $date;
    }
    return date('Y-m-d');
}

/**
 * Prepared Statement를 실행하고 결과를 배열로 반환합니다.
 * $stmt->close()를 fetch 이후에 호출하여 결과 set 해제 문제를 방지합니다.
 * 
 * @param mysqli $conn DB 연결 객체
 * @param string $sql 전체 SQL 문자열 (플레이스홀더 포함)
 * @param string $types bind_param 타입 문자열 (예: "ssi")
 * @param array $params 바인딩할 파라미터 배열
 * @return array 결과 행 배열
 */
function executeQuery($conn, $sql, $types = "", $params = [])
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error . " | SQL: " . $sql);
    }
    if (!empty($params)) {
        // Bind parameters by reference to satisfy mysqli_stmt::bind_param requirements.
        $bind_names = array_merge([$types], $params);
        $tmp = [];
        foreach ($bind_names as $key => $value) {
            $tmp[$key] = &$bind_names[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $tmp);
    }
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    $stmt->close();
    return $data;
}

function executeUpdate($conn, $sql, $types = "", $params = [])
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error . " | SQL: " . $sql);
    }
    if (!empty($params)) {
        $bind_names = array_merge([$types], $params);
        $tmp = [];
        foreach ($bind_names as $key => $value) {
            $tmp[$key] = &$bind_names[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $tmp);
    }
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}

/**
 * ORDER BY 파라미터를 검증하여 안전한 SQL 문자열을 반환합니다.
 * 프론트엔드에서 "COLUMN_NAME asc" 또는 "COLUMN_NAME desc" 형식으로 전달됩니다.
 * 
 * @param string $orderInput $_REQUEST['ORDER'] 값
 * @param array $allowedColumns 허용된 컬럼명 배열
 * @return string 안전한 ORDER BY SQL 문자열 또는 빈 문자열
 */
function safeOrderBy($orderInput, $allowedColumns)
{
    if (empty($orderInput))
        return "";

    // "COLUMN_NAME direction" 형식으로 분리
    $parts = explode(' ', trim($orderInput));
    $column = $parts[0];
    $direction = isset($parts[1]) ? strtolower($parts[1]) : 'asc';

    // direction 검증
    if ($direction !== 'asc' && $direction !== 'desc') {
        $direction = 'asc';
    }

    if (!empty($allowedColumns)) {
        // 컬럼명 화이트리스트 검증
        if (in_array($column, $allowedColumns)) {
            return " ORDER BY " . $column . " " . $direction;
        }
    }

    // 허용 컬럼 목록이 없거나 (빈 배열), 화이트리스트 검증실패시 안전성 검증 정규식으로 재확인
    if (preg_match('/^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)?$/', $column)) {
        return " ORDER BY " . $column . " " . $direction;
    }

    return "";
}

/**
 * LIMIT 파라미터를 검증하여 안전한 SQL 문자열을 반환합니다.
 * 프론트엔드에서 "offset,limit" 형식 (예: "0,10")으로 전달됩니다.
 * 
 * @param string $limitInput $_REQUEST['LIMIT'] 값
 * @return string 안전한 LIMIT SQL 문자열 또는 빈 문자열
 */
function safeLimit($limitInput)
{
    if (empty($limitInput))
        return "";

    // "offset,limit" 형식 확인
    if (strpos($limitInput, ',') !== false) {
        $parts = explode(',', $limitInput);
        $offset = intval($parts[0]);
        $limit = intval($parts[1]);
        if ($offset >= 0 && $limit > 0) {
            return " LIMIT " . $offset . "," . $limit;
        }
    }
    else {
        // 단일 숫자 형식
        $limit = intval($limitInput);
        if ($limit > 0) {
            return " LIMIT " . $limit;
        }
    }

    return "";
}

/**
 * JSON 응답을 출력하고 DB 연결을 닫습니다.
 * 
 * @param mysqli $conn DB 연결 객체
 * @param array $data 출력할 데이터
 */
function jsonResponse($conn, $data)
{
    mysqli_close($conn);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}
?>
