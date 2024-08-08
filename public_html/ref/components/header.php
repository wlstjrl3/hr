<?php
    session_start();
    if($_SESSION["LOGIN_ID"]==''){
        echo "<script>document.location.href='./login.php';</script>";
        die('관리자로그인 필요');
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf8' />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css" type="text/css" />
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js" integrity="sha256-eTyxS0rkjpLEo16uXTS0uVCS4815lc40K2iVpWDvdSY=" crossorigin="anonymous"></script>


    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.2/css/uikit.min.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>

    <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.dataTables.min.css">

    <link rel="stylesheet" href="./css/header.css" type='text/css'>
    <script src='./js/header.js'></script> 

    <link href="https://fonts.googleapis.com/css2?family=Jua&family=Nanum+Gothic&family=Noto+Sans+KR:wght@100;300;400;500;700;900&family=Roboto0&display=swap" rel="stylesheet">



    <title>온라인 성경이어쓰기 팀정보 관리자페이지</title>
</head>
<body>
    <div id="closeNav" style="width:0%;height:0%;background:rgba(100,100,100,0.5);position:absolute;z-index:9;" onclick="toggleNav()"></div>
    <header>
        <!-- Sidebar navigation {{{ --> 
        <div class="hd-nav-margin" style="width:0%;height:100%;float:left;background-color:white;position:fixed;z-index:3;top:64px;"></div>
        <div class="side-nav">
            <ul class="sideNavBlock">
				<?php if($_SESSION['ORG_NM']=='관리자'){?>
                <li>
                    <a href="./group.php">그룹관리</a>
                    <hr>
                </li>
				<?php }?>
                <li>
                    <a href="./">이용자관리</a>
                    <hr>
                </li>
                <li>
                    정보 관리
                    <img src="https://mocatholic.or.kr/assets/images/nav/direction.svg" alt="더보기" style="transition:0.2s;width:10px; float:right;margin-top:20px;"/>
                    <ul>
                        <li>
                            <a href="./logout.php">
                                └&nbsp;로그아웃
                            </a>
                        </li>
                    </ul>
                    <hr>
                </li>             
            </ul>
        </div>
        <!-- Sidebar navigation }}}-->  

        <div class="nav" style="box-shadow: inset 0px -1px 0px rgba(186, 186, 186, 0.25);">
            <div style="height:64px;max-width:1280px;margin:0 auto;width:100%;white-space:nowrap;">

                <div>
                    <a data-activates="slide-out" style="float:left;padding:14px 5px;" onclick="toggleNav()">
                        <img id="navOpen" src="https://mocatholic.or.kr/assets/images/nav/menu.svg" alt="메뉴" style="width:36px;"/>
                    </a>
                </div>
                <div style="font-size:14px;margin:20px 0 0 0;float:left;">
                    <?=@$_SESSION["ORG_NM"]?>
                    <input type="hidden" id="loginId" value="<?=@$_SESSION["ORG_ID"]?>">            
                </div>
            </div>   
        </div>        
    </header>
    <a id="MOVE_TOP_BTN" href="#"><img src="https://mocatholic.or.kr/assets/images/topBtn.svg" alt="스크롤 상단으로 올리기"></a>

