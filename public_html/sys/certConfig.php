<?php
include "sql_safe_helper.php";
verifyApiKey($conn);
$userAuth = $_SESSION['USER_AUTH'] ?? '';
$userName = $_SESSION['USER_NM'] ?? '';

if ($_REQUEST['CRUD'] == 'C') { // Create or Update
    if ($userAuth != 'auth' && $userAuth != 'admin') {
        die('권한이 없는 사용자 입니다.');
    }

    mysqli_begin_transaction($conn);
    try {
        if (!@$_REQUEST['ISSUE_NO']) { // 신규 등록 및 채번
            $year = date('Y');
            $certType = $_REQUEST['CERT_TYPE'] ?? '재직';
            
            // 해당 연도 + 해당 증명서 종류의 마지막 채번 번호 조회 (Lock 적용)
            $lastNoData = executeQuery($conn, 
                "SELECT ISSUE_NO FROM TB_CERT_PRINT WHERE ISSUE_NO LIKE ? ORDER BY ISSUE_NO DESC LIMIT 1 FOR UPDATE", 
                "s", ["{$certType}-{$year}-%"]
            );
            
            $nextSeq = 1;
            if (!empty($lastNoData)) {
                if (preg_match('/-(\d{3})$/', $lastNoData[0]['ISSUE_NO'], $matches)) {
                    $nextSeq = (int)$matches[1] + 1;
                }
            }
            
            $newIssueNo = sprintf("%s-%d-%03d", $certType, $year, $nextSeq);
            
            executeUpdate($conn,
                "INSERT INTO TB_CERT_PRINT (ISSUE_NO, EMP_NO, CERT_TYPE, ORIGIN_ADDR, CURR_ADDR, ORG_ADDR, ISSUE_DT, REG_EMP_NO, MEMO) 
                 VALUES (?, ?, ?, ?, ?, ?, CURRENT_DATE, ?, ?)",
                "ssssssss",
                [$newIssueNo, $_REQUEST['EMP_NO'], $_REQUEST['CERT_TYPE'], $_REQUEST['ORIGIN_ADDR'], $_REQUEST['CURR_ADDR'], $_REQUEST['ORG_ADDR'], $userName, $_REQUEST['MEMO']]
            );
            
            mysqli_commit($conn);
            jsonResponse($conn, ["result" => "success", "ISSUE_NO" => $newIssueNo]);
        } else { // 기존 데이터 수정
            $oldIssueNo = $_REQUEST['ISSUE_NO'];
            $newCertType = $_REQUEST['CERT_TYPE'];
            
            $oldData = executeQuery($conn, "SELECT CERT_TYPE FROM TB_CERT_PRINT WHERE ISSUE_NO = ? FOR UPDATE", "s", [$oldIssueNo]);
            
            if (!empty($oldData) && $oldData[0]['CERT_TYPE'] != $newCertType) {
                executeUpdate($conn, "DELETE FROM TB_CERT_PRINT WHERE ISSUE_NO = ?", "s", [$oldIssueNo]);
                
                $year = date('Y');
                $lastNoData = executeQuery($conn, 
                    "SELECT ISSUE_NO FROM TB_CERT_PRINT WHERE ISSUE_NO LIKE ? ORDER BY ISSUE_NO DESC LIMIT 1 FOR UPDATE", 
                    "s", ["{$newCertType}-{$year}-%"]
                );
                $nextSeq = 1;
                if (!empty($lastNoData)) {
                    if (preg_match('/-(\d{3})$/', $lastNoData[0]['ISSUE_NO'], $matches)) {
                        $nextSeq = (int)$matches[1] + 1;
                    }
                }
                $finalIssueNo = sprintf("%s-%d-%03d", $newCertType, $year, $nextSeq);
                
                executeUpdate($conn,
                    "INSERT INTO TB_CERT_PRINT (ISSUE_NO, EMP_NO, CERT_TYPE, ORIGIN_ADDR, CURR_ADDR, ORG_ADDR, ISSUE_DT, REG_EMP_NO, MEMO) 
                     VALUES (?, ?, ?, ?, ?, ?, CURRENT_DATE, ?, ?)",
                    "ssssssss",
                    [$finalIssueNo, $_REQUEST['EMP_NO'], $newCertType, $_REQUEST['ORIGIN_ADDR'], $_REQUEST['CURR_ADDR'], $_REQUEST['ORG_ADDR'], $userName, $_REQUEST['MEMO']]
                );
            } else {
                $finalIssueNo = $oldIssueNo;
                executeUpdate($conn,
                    "UPDATE TB_CERT_PRINT SET ORIGIN_ADDR=?, CURR_ADDR=?, ORG_ADDR=?, MEMO=? WHERE ISSUE_NO=?",
                    "sssss",
                    [$_REQUEST['ORIGIN_ADDR'], $_REQUEST['CURR_ADDR'], $_REQUEST['ORG_ADDR'], $_REQUEST['MEMO'], $oldIssueNo]
                );
            }
            mysqli_commit($conn);
            jsonResponse($conn, ["result" => "success", "ISSUE_NO" => $finalIssueNo]);
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        jsonResponse($conn, ["result" => "error", "message" => $e->getMessage()]);
    }
}
else if ($_REQUEST['CRUD'] == 'R') { // 단건 조회 (인쇄용 등)
    $issueNo = $_REQUEST['ISSUE_NO'];
    
    // 1. 기본 마스터 정보 조회
    $sql = "SELECT 
                A.*, 
                B.PSNL_NM,
                B.PSNL_NUM,
                (SELECT ORG_NM FROM ORG_INFO WHERE ORG_CD = (SELECT ORG_CD FROM PSNL_TRANSFER WHERE PSNL_CD = A.EMP_NO AND TRS_DT <= A.ISSUE_DT ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1)) AS ORG_NM,
                (SELECT ORG_TYPE FROM ORG_INFO WHERE ORG_CD = (SELECT ORG_CD FROM PSNL_TRANSFER WHERE PSNL_CD = A.EMP_NO AND TRS_DT <= A.ISSUE_DT ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1)) AS ORG_TYPE,
                (SELECT EMAIL FROM ORG_INFO WHERE ORG_CD = (SELECT ORG_CD FROM PSNL_TRANSFER WHERE PSNL_CD = A.EMP_NO AND TRS_DT <= A.ISSUE_DT ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1)) AS ORG_EMAIL,
                (SELECT ORG_NM FROM ORG_INFO WHERE ORG_CD = (SELECT ORG_CD FROM PSNL_TRANSFER WHERE PSNL_CD = A.EMP_NO ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1)) AS CURR_ORG_NM,
                (SELECT EMAIL FROM ORG_INFO WHERE ORG_CD = (SELECT ORG_CD FROM PSNL_TRANSFER WHERE PSNL_CD = A.EMP_NO ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1)) AS CURR_ORG_EMAIL,
                (SELECT POSITION FROM PSNL_TRANSFER WHERE PSNL_CD = A.EMP_NO AND TRS_DT <= A.ISSUE_DT ORDER BY TRS_DT DESC, TRS_CD DESC LIMIT 1) AS POSITION,
                
                /* [JOIN_DT 로직]: 퇴직증명서일 경우 마지막 퇴직 직전의 입사일을 가져옴 */
                CASE 
                    WHEN A.CERT_TYPE = '퇴직' THEN 
                        (SELECT MAX(TRS_DT) FROM PSNL_TRANSFER 
                         WHERE PSNL_CD = A.EMP_NO AND TRS_TYPE = '1' 
                         AND TRS_DT <= (SELECT MAX(TRS_DT) FROM PSNL_TRANSFER WHERE PSNL_CD = A.EMP_NO AND TRS_TYPE = '2'))
                    ELSE (SELECT MIN(TRS_DT) FROM PSNL_TRANSFER WHERE PSNL_CD = A.EMP_NO) 
                END AS JOIN_DT,
                
                /* [RETIRE_DT 로직]: 마지막 퇴직일 */
                (SELECT MAX(TRS_DT) FROM PSNL_TRANSFER WHERE PSNL_CD = A.EMP_NO AND TRS_TYPE = '2') AS RETIRE_DT
            FROM TB_CERT_PRINT A
            LEFT JOIN PSNL_INFO B ON A.EMP_NO = B.PSNL_CD
            WHERE A.ISSUE_NO = ? LIMIT 1";
            
    $data = executeQuery($conn, $sql, "s", [$issueNo]);
    $res = $data[0] ?? null;

    if ($res) {
        // 2. 경력증명서일 경우 전체 변동 이력(Transfer History) 추가 조회
        if ($res['CERT_TYPE'] == '경력') {
            $historySql = "SELECT 
                                T.TRS_DT AS STT_DT,
                                (SELECT MIN(T2.TRS_DT) FROM PSNL_TRANSFER T2 WHERE T2.PSNL_CD = T.PSNL_CD AND T2.TRS_DT > T.TRS_DT AND T2.TRS_DT <= ?) AS END_DT,
                                O.ORG_NM,
                                O.ORG_TYPE,
                                T.POSITION
                           FROM PSNL_TRANSFER T
                           LEFT JOIN ORG_INFO O ON T.ORG_CD = O.ORG_CD
                           WHERE T.PSNL_CD = ? AND T.TRS_TYPE IN ('1', '3') AND T.TRS_DT <= ?
                           ORDER BY T.TRS_DT ASC";
            $res['history'] = executeQuery($conn, $historySql, "sss", [$res['ISSUE_DT'], $res['EMP_NO'], $res['ISSUE_DT']]);
        }
    }

    jsonResponse($conn, ["data" => $res]);
}
else if ($_REQUEST['CRUD'] == 'D') { // 삭제
    if ($userAuth != 'admin') {
        die('삭제 권한이 없습니다.');
    }
    executeUpdate($conn, "DELETE FROM TB_CERT_PRINT WHERE ISSUE_NO = ?", "s", [$_REQUEST['ISSUE_NO']]);
    jsonResponse($conn, ["result" => "success"]);
}
else {
    die('잘못된 접근방식입니다.');
}
?>
