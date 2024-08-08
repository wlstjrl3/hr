<!DOCTYPE HTML>
<HTML itemscope itemtype="http://schema.org/WebPage">
	<HEAD>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	<BODY>
<?php
    if ($_POST['admin-id']=="" || $_POST['admin-password']=="")
    {
        echo "<script> 
            alert('정상적인 접근이 아닙니다.[Err_Code:0]');
            history.back();
        </script>"; 
        die('정상적인 접근이 아닙니다.');
    }
    include "./dbconn.php";

    $result = mysqli_query($conn, "SELECT * FROM USER_TB WHERE USER_ID='".$_POST['admin-id']."'");
    $row = mysqli_fetch_array($result);
    mysqli_close($conn);
    if(@$row['USER_ID'] == '' || $row['USER_PASS'] != md5($_POST['admin-password']))
    {//.@$row['USER_ID'].@$row['USER_PASS'].$_POST['admin-password'].
        echo "<script> 
        alert('아이디 또는 비밀번호 오류');
        history.back();
        </script>"; 
        die('아이디 또는 비밀번호 오류');
    }
    session_start();
    $_SESSION["USER_ID"]=$row['USER_ID'];
    $_SESSION["USER_PASS"]=$row['USER_PASS'];
    $_SESSION["USER_NM"]=$row['USER_NM'];
    $_SESSION["USER_AUTH"]=$row['USER_AUTH'];
    echo "<script> 
        alert('".$row['USER_NM']."님 환영합니다!');
        document.location.href='/'; 
    </script>"; 
?>
    </BODY>
</HTML>