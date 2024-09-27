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

    <title>제1대리구 본당직원 인적 관리시스템</title>
</head>
<body>
    <input type="hidden" id="psnlKey" value="<?php echo @$_SESSION["USER_PASS"];?>">
    <div id="closeNav" style="width:0%;height:0%;background:rgba(100,100,100,0.5);position:absolute;z-index:2;" onclick="toggleNav()"></div>
    <header id="header">
        <!-- Sidebar navigation {{{ --> 
        <div class="hd-nav-margin" style="width:0%;height:100%;float:left;background-color:white;position:fixed;z-index:3;top:54px;"></div>
        <div class="side-nav">
            <ul class="sideNavBlock">
                <li>
                    <a class="fs5" href="#">인사정보 관리
                        <img src="/assets/img/svgs/direction.svg" alt="더보기"/>
                    </a>
                    <ul>
                        <li>
                            <a href="/psnlTotal">
                                └&nbsp;직원 종합정보 조회
                            </a>
                        </li>
                        <li>
                            <a href="/psnlList">
                                └&nbsp;직원 기초정보
                            </a>
                        </li>
                        <li>
                            <a href="/trsList">
                                └&nbsp;입퇴사 발령관리
                            </a>
                        </li>
                        <li>
                            <a href="/fmlList">
                                └&nbsp;가족정보 관리
                            </a>
                        </li>
                        <li>
                            <a href="/lcsList">
                                └&nbsp;자격/면허 관리
                            </a>
                        </li>
                        <li>
                            <a href="/insList">
                                └&nbsp;보증보험 정보 관리
                            </a>
                        </li>
                        <li>
                            <a href="/opiList">
                                └&nbsp;상벌/직무평가 관리
                            </a>
                        </li>
                    </ul>
                    <hr>
                </li>
                <li>
                    <a class="fs5" href="#">승급 관리
                        <img src="/assets/img/svgs/direction.svg" alt="더보기"/>
                    </a>
                    <ul>
                        <li>
                            <a href="/">
                                <span class="cl6">└&nbsp;승급 대상자 현황</span>
                            </a>
                        </li>
                        <li>
                            <a href="/">
                                <span class="cl6">└&nbsp;일괄 호봉 갱신</span>
                            </a>
                        </li>
                        <li>
                            <a href="/grdList">
                                └&nbsp;개별 급호봉 관리
                            </a>
                        </li>
                    </ul>
                    <hr>
                </li>
                <li>
                    <a class="fs5" href="#">급여 관리
                        <img src="/assets/img/svgs/direction.svg" alt="더보기"/>
                    </a>
                    <ul>
                        <li>
                            <a href="/salaryList.php">
                                └&nbsp;급호봉 테이블
                            </a>
                        </li>
                        <li>
                            <a href="/">
                                <span class="cl6">└&nbsp;근무일수/시간</span>
                            </a>
                        </li>
                        <li>
                            <a href="/">
                                <span class="cl6">└&nbsp;조정수당 관리</span>
                            </a>
                        </li>
                        <li>
                            <a href="/">
                                <span class="cl6">└&nbsp;급여 정보</span>
                            </a>
                        </li>
                    </ul>
                    <hr>
                </li>    
                <li>
                    <a class="fs5" href="#">조직 관리
                        <img src="/assets/img/svgs/direction.svg" alt="더보기"/>
                    </a>
                    <ul>
                        <li>
                            <a href="/orgList">
                                └&nbsp;조직 현황
                            </a>
                        </li>
                        <!--li>
                            <a href="/">
                                └&nbsp;조직정보 트리
                            </a>
                        </li-->
                    </ul>
                    <hr>
                </li>                                          
                <li>
                    <a class="fs5" href="/user.php">사용자관리</a>
                    <hr>
                </li>   
                <li>
                    <a class="fs5" href="./logout.php">로그아웃</a>
                    <hr>
                </li>             
            </ul>
        </div>
        <!-- Sidebar navigation }}}-->  
        <div class="nav" style="box-shadow: inset 0px -1px 0px rgba(186, 186, 186, 0.25);">
            <div style="height:54px;max-width:1280px;margin:0 auto;width:100%;white-space:nowrap;">
                <div id="navToggle">
                    <a data-activates="slide-out" onclick="toggleNav()">
                        <img src="/assets/img/svgs/menu.svg" alt="메뉴"/>
                    </a>
                </div>
            </div>   
        </div>        
    </header>
    <div style="height:55px;">네비게이션 높이 여백</div>
    <a id="MOVE_TOP_BTN" href="#"><img src="/assets/img/svgs/topBtn.svg" alt="스크롤 상단으로 올리기"></a>
