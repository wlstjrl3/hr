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
        $acc_type = $row['acc_type'];
        $amount = $row['amount'] ?: 0;
        $fsc_year = $row['fsc_year'];

        $sql = "INSERT INTO BONDANG_HR.ORG_BUDGET (ORG_CD, ACC_NM, ACC_TYPE, AMOUNT, FSC_YEAR) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE AMOUNT = VALUES(AMOUNT)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $org_cd, $acc_nm, $acc_type, $amount, $fsc_year);
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
    $acc_type = $input['ACC_TYPE'];
    $amount = $input['AMOUNT'] ?: 0;
    $fsc_year = $input['FSC_YEAR'];

    $sql = "INSERT INTO BONDANG_HR.ORG_BUDGET (ORG_CD, ACC_NM, ACC_TYPE, AMOUNT, FSC_YEAR) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE AMOUNT = VALUES(AMOUNT)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $org_cd, $acc_nm, $acc_type, $amount, $fsc_year);
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
    $acc_type = $_REQUEST['ACC_TYPE'];

    $sql = "DELETE FROM BONDANG_HR.ORG_BUDGET WHERE FSC_YEAR = ? AND ORG_CD = ? AND ACC_NM = ? AND ACC_TYPE = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $fsc_year, $org_cd, $acc_nm, $acc_type);
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
