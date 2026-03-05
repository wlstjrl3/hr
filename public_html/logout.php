<?php
include("./dbconn/dbconn.php");
session_start();
session_destroy();
echo "<script> 
        document.location.href='" . DIR_ROOT . "/'; 
    </script>";
die('로그아웃 완료');
?>