<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

$crud = $_REQUEST['CRUD'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

if ($crud === 'U') {
    // Bulk Update (Excel Upload)
    if (!is_array($input)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
        exit;
    }

    $successCount = 0;
    foreach ($input as $row) {
        $org_cd = $row['org_cd'];
        $acc_nm = $row['acc_nm'];
        $amount = $row['amount'] ?: 0;
        $fsc_year = $row['fsc_year'];

        $sql = "INSERT INTO BONDANG_HR.ORG_FINANCIAL (ORG_CD, ACC_NM, AMOUNT, FSC_YEAR) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE AMOUNT = VALUES(AMOUNT)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $org_cd, $acc_nm, $amount, $fsc_year);
        if ($stmt->execute()) {
            $successCount++;
        }
        $stmt->close();
    }

    echo json_encode(['status' => 'success', 'message' => $successCount . ' rows processed']);

} else if ($crud === 'C') {
    // Create or Update single record
    $org_cd = $input['ORG_CD'];
    $acc_nm = $input['ACC_NM'];
    $amount = $input['AMOUNT'] ?: 0;
    $fsc_year = $input['FSC_YEAR'];

    $sql = "INSERT INTO BONDANG_HR.ORG_FINANCIAL (ORG_CD, ACC_NM, AMOUNT, FSC_YEAR) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE AMOUNT = VALUES(AMOUNT)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $org_cd, $acc_nm, $amount, $fsc_year);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    $stmt->close();

} else if ($crud === 'D') {
    // Delete single record
    $fsc_year = $_REQUEST['FSC_YEAR'];
    $org_cd = $_REQUEST['ORG_CD'];
    $acc_nm = $_REQUEST['ACC_NM'];

    $sql = "DELETE FROM BONDANG_HR.ORG_FINANCIAL WHERE FSC_YEAR = ? AND ORG_CD = ? AND ACC_NM = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $fsc_year, $org_cd, $acc_nm);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    $stmt->close();

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CRUD operation']);
}

mysqli_close($conn);
?>
