<?php
include "sql_safe_helper.php";
$res = mysqli_query($conn, "SHOW TABLES IN BONDANG_HR LIKE 'PSNL_%'");
while($row = mysqli_fetch_array($res)) {
    echo $row[0] . "\n";
}
?>
