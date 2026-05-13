<?php
include "public_html/sys/sql_safe_helper.php";

$sql = "CREATE TABLE IF NOT EXISTS TB_SMTP_CONFIG (
    SMTP_HOST VARCHAR(255),
    SMTP_PORT INT,
    SMTP_USER VARCHAR(255),
    SMTP_PASS VARCHAR(255),
    SMTP_SECURE VARCHAR(50),
    UPDATE_DT DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Table TB_SMTP_CONFIG created or already exists.\n";
    
    // Check if data already exists
    $check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM TB_SMTP_CONFIG");
    $row = mysqli_fetch_assoc($check);
    
    if ($row['cnt'] == 0) {
        // Initialize from .env
        $envPath = 'public_html/.env';
        if (file_exists($envPath)) {
            $env = parse_ini_file($envPath);
            $stmt = $conn->prepare("INSERT INTO TB_SMTP_CONFIG (SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_SECURE) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisss", 
                $env['SMTP_HOST'], 
                $env['SMTP_PORT'], 
                $env['SMTP_USER'], 
                $env['SMTP_PASS'], 
                $env['SMTP_SECURE']
            );
            $stmt->execute();
            echo "Initialized TB_SMTP_CONFIG from .env.\n";
        }
    }
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
