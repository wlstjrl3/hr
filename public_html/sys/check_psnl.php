<?php
include "sql_safe_helper.php";
$res = mysqli_query($conn, "DESC BONDANG_HR.PSNL_INFO");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " ";
}
echo "\n";
?>
