<?php
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
}
else {
    $env = [];
}

$dbHost = isset($env['DB_HOST']) ? $env['DB_HOST'] : '0.0.0.0';
$dbUser = isset($env['DB_USER']) ? $env['DB_USER'] : 'dbId';
$dbPass = isset($env['DB_PASS']) ? $env['DB_PASS'] : 'dbPass';
$dbName = isset($env['DB_NAME']) ? $env['DB_NAME'] : 'dbName';
$dbPort = isset($env['DB_PORT']) ? $env['DB_PORT'] : 'dbPort';

$conn = mysqli_connect(
    $dbHost,
    $dbUser,
    $dbPass,
    $dbName,
    $dbPort
);

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
else {
    mysqli_set_charset($conn, "utf8");
}
?>