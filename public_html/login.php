<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    session_start();
    if(@$_SESSION["USER_PASS"]=='' && $_SERVER['PHP_SELF']!='/login.php' && $_SERVER['PHP_SELF']!='/style.php'){
        echo "<script>document.location.href='/login.php';</script>";
        die('관리자로그인 필요');
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf8' />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <link href="/assets/css/common.css?ver=0.001" rel="stylesheet" />
    <link href="/assets/css/header.css?ver=0.001" rel="stylesheet" />
    <script defer src="https://sinseiki.github.io/noIE.js/noIE.js" ></script><!--익스플로러 사용제한-->    
    <script type='text/javascript' src='/assets/js/header.js'></script>

    <title>제1대리구 통합사목허브 관리시스템</title>
</head>
<body>

<script type='text/javascript' src='/assets/js/login.js'></script>
<link href="/assets/css/login.css?ver=0.001" rel="stylesheet" />

<div id="loginBg" class="clBg2">
    <div id="loginFrm">
        <div id="loginTitle">
            <h3 class="fontWExtr cl1">천주교 수원교구 제1대리구</h3>
            <h3 class="fontWSlim clW">인사관리 프로그램</h3>
            <br>
            <h2 class="fontWExtr cl1">Diocese of Suwon<br>First Vicariate</h2>
            <h3 class="fontWSlim clW">Employee of Parish</h3>
            <h3 class="fontWSlim clW">Administration Program</h3>
        </div>
        <div id="loginBody">
            <br><br>
            <form autocomplete="off" name="form-signin" class="form-signin" method="post" target="_self" action="../dbconn/login_chk.php" onsubmit="return frm_check();">
                <div><h4>로그인</h4></div>
                <br>
                <div><input class="pddS" placeholder="ID" id="admin-id" name="admin-id" min=3 required></div>
                <div><input class="pddS" placeholder="PASSWORD" type="password" id="admin-password" name="admin-password" min=4 required></div>
                <div><button class="pddM clBg3 clW clBr0">로그인</button></div>
                <div><input type="checkbox" name="saveId" id="saveId"><label for="saveId" style=""> 아이디 저장</label></div>
            </form>
            <br><br>
        </div>
        <div style="clear:both;"></div>
    </div>
</div>
<?php include('components/footer.php'); ?>
