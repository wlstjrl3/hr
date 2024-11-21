<?php
$conn = mysqli_connect(
    '0.0.0.0'
    ,'dbId'
    ,'dbPass'
    ,'dbName'
    ,'dbPort'
);
if (mysqli_connect_errno())
{
echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
else{
    mysqli_set_charset($conn, "utf8");
}
?>