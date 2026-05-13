<?php
$envPath = __DIR__ . '/../public_html/.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    echo "SMTP_USER: [" . $env['SMTP_USER'] . "]\n";
    echo "SMTP_PASS: [" . $env['SMTP_PASS'] . "]\n";
} else {
    echo ".env not found at $envPath\n";
}
?>
