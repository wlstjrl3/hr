<?php
    session_start();
    session_destroy();
    echo "<script> 
        document.location.href='../admin/'; 
    </script>"; 
    die('로그아웃 완료');
?>