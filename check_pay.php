<?php
include 'public_html/dbconn/dbconn.php';
$res = mysqli_query($conn, 'SELECT DISTINCT GRD_PAY FROM GRADE_HISTORY LIMIT 20');
if ($res) {
    while($row = mysqli_fetch_assoc($res)) {
        echo "[" . $row['GRD_PAY'] . "]\n";
    }
} else {
    echo "Query failed";
}
?>
