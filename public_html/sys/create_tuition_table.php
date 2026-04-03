<?php
include "sql_safe_helper.php";

$sql = "CREATE TABLE IF NOT EXISTS BONDANG_HR.TB_TUITION_ISSUE (
    ISSUE_CD INT AUTO_INCREMENT PRIMARY KEY,
    ISSUE_NO VARCHAR(50) NULL COMMENT '발급번호(표시용)',
    PSNL_CD VARCHAR(50) NOT NULL COMMENT '직원번호',
    FML_CD INT NOT NULL COMMENT '가족번호',
    ISSUE_DT DATE NOT NULL COMMENT '지급일',
    ISSUE_AMT INT NOT NULL DEFAULT 0 COMMENT '지급액',
    SCHOOL_GRADE VARCHAR(20) NULL COMMENT '학년',
    MEMO VARCHAR(255) NULL COMMENT '비고',
    REG_DT DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='자녀학비 보조금 지급 내역';";

if (mysqli_query($conn, $sql)) {
    echo "Table TB_TUITION_ISSUE created successfully.\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}
?>
