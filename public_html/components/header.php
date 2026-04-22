<?php
include("./dbconn/dbconn.php");
error_reporting(E_ALL);
ini_set("display_errors", 0);   // 실서버: 화면 오류 출력 비활성화
ini_set("log_errors", 1);       // PHP 에러 로그에 기록
session_start();
if (empty($_SESSION["USER_ID"]) && $_SERVER['PHP_SELF'] != DIR_ROOT . '/login.php' && $_SERVER['PHP_SELF'] != DIR_ROOT . '/style.php') {
    echo "<script>document.location.href='" . DIR_ROOT . "/login.php';</script>";
    die('관리자로그인 필요');
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8' />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <link href="<?php echo DIR_ROOT; ?>/assets/css/common.css?ver=1775259319" rel="stylesheet" />
    <link href="<?php echo DIR_ROOT; ?>/assets/css/header.css?ver=1775259319" rel="stylesheet" />
    <script defer src="https://sinseiki.github.io/noIE.js/noIE.js"></script><!--익스플로러 사용제한-->
    <script type='text/javascript'>
        const DIR_ROOT = '<?php echo DIR_ROOT; ?>';
        const API_TOKEN = '<?php echo $_SESSION['API_TOKEN'] ?? ''; ?>';
    </script>
    <script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/header.js?ver=1775259319'></script>

    <title>제1대리구 소속직원 인적 관리시스템</title>
</head>

<body>
    <!-- psnlKey hidden input removed for security -->
    <div id="closeNav" style="width:0%;height:0%;background:rgba(100,100,100,0.5);position:absolute;z-index:2;"
        onclick="toggleNav()"></div>
    <header id="header">
        <!-- Sidebar navigation {{{ -->
        <div class="hd-nav-margin"
            style="width:0%;height:100%;float:left;background-color:white;position:fixed;z-index:3;top:54px;"></div>
        <div class="side-nav" id="sideNav">
            <ul class="sideNavBlock">
                <li>
                    <a class="fs5" href="<?php echo DIR_ROOT; ?>/">
                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="18" height="18"
                            viewBox="0 0 50 50">
                            <path fill="white"
                                d="M 25 1.0507812 C 24.7825 1.0507812 24.565859 1.1197656 24.380859 1.2597656 L 1.3808594 19.210938 C 0.95085938 19.550938 0.8709375 20.179141 1.2109375 20.619141 C 1.5509375 21.049141 2.1791406 21.129062 2.6191406 20.789062 L 4 19.710938 L 4 46 C 4 46.55 4.45 47 5 47 L 19 47 L 19 29 L 31 29 L 31 47 L 45 47 C 45.55 47 46 46.55 46 46 L 46 19.710938 L 47.380859 20.789062 C 47.570859 20.929063 47.78 21 48 21 C 48.3 21 48.589063 20.869141 48.789062 20.619141 C 49.129063 20.179141 49.049141 19.550938 48.619141 19.210938 L 25.619141 1.2597656 C 25.434141 1.1197656 25.2175 1.0507812 25 1.0507812 z M 35 5 L 35 6.0507812 L 41 10.730469 L 41 5 L 35 5 z">
                            </path>
                        </svg>
                    </a>
                    <hr>
                </li>
                <li>
                    <a class="fs5" href="#">인사정보
                        <img src="<?php echo DIR_ROOT; ?>/assets/img/svgs/direction.svg" alt="더보기" />
                    </a>
                    <ul>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/psnlTotal?TRS_TYPE=1">
                                └&nbsp;직원 종합정보
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/psnlList">
                                └&nbsp;직원 기초등록
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/trsList">
                                └&nbsp;입퇴사 발령
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/fmlList">
                                └&nbsp;가족정보 관리
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/opiList">
                                └&nbsp;상벌/직무평가
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/certPrint">
                                └&nbsp;증명서 발급
                            </a>
                        </li>
                    </ul>
                    <hr>
                </li>
                <li>
                    <a class="fs5" href="#">개발예정
                        <img src="<?php echo DIR_ROOT; ?>/assets/img/svgs/direction.svg" alt="더보기" />
                    </a>
                    <ul>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/insList">
                                └&nbsp;보증보험 정보
                            </a>
                        </li>
                    </ul>
                    <hr>
                </li>
                <li>
                    <a class="fs5" href="#">승급
                        <img src="<?php echo DIR_ROOT; ?>/assets/img/svgs/direction.svg" alt="더보기" />
                    </a>
                    <ul>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/advList">
                                └&nbsp;승급 대상자 현황
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/grdBatch">
                                └&nbsp;일괄 호봉 갱신
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/grdList">
                                └&nbsp;개별 급호봉 관리
                            </a>
                        </li>
                    </ul>
                    <hr>
                </li>
                <li>
                    <a class="fs5" href="#">급여
                        <img src="<?php echo DIR_ROOT; ?>/assets/img/svgs/direction.svg" alt="더보기" />
                    </a>
                    <ul>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/salaryList.php">
                                └&nbsp;급호봉 테이블
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/pttList.php">
                                └&nbsp;최저임금 등록
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/adjList.php">
                                └&nbsp;제수당 관리
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/tuition.php">
                                └&nbsp;자녀학비 보조금
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/mpayList.php">
                                └&nbsp;월별 개인급여
                            </a>
                        </li>
                    </ul>
                    <hr>
                </li>
                <li>
                    <a class="fs5" href="#">기초정보
                        <img src="<?php echo DIR_ROOT; ?>/assets/img/svgs/direction.svg" alt="더보기" />
                    </a>
                    <ul>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/orgList">
                                └&nbsp;본당/성지 관리
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/orgBudget">
                                └&nbsp;본당예산 관리
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/ohisList">
                                └&nbsp;년도별 신자수
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/user">
                                └&nbsp;사용자 관리
                            </a>
                        </li>
                    </ul>
                    <hr>
                </li>
                <li>
                    <a class="fs5" href="#">통계/자료
                        <img src="<?php echo DIR_ROOT; ?>/assets/img/svgs/direction.svg" alt="더보기" />
                    </a>
                    <ul>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/statOrgHr">
                                └&nbsp;본당별직원현황
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/statWorkType">
                                └&nbsp;고용형태현황
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/statWorkTypeGraph">
                                └&nbsp;고용형태 그래프
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/statWorkTypeGraph2">
                                └&nbsp;직군별 상세 그래프
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/statPsnlTable">
                                └&nbsp;인사관리 대상 현황
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo DIR_ROOT; ?>/statSalaryOhis">
                                └&nbsp;예산액 비율현황
                            </a>
                        </li>
                    </ul>
                    <!--(남여/나이대/직종/평균임금/평균근속)직종별 남여분포, 나이대별 평균임금, 성별에 따른 평균근속 등 조합 조회 그래프 표시기능-->
                    <hr>
                </li>
            </ul>
        </div>
        <!-- Sidebar navigation }}}-->
        <div class="nav" style="box-shadow: inset 0px -1px 0px rgba(186, 186, 186, 0.25);">
            <div style="height:54px;max-width:1280px;margin:0 auto;width:100%;white-space:nowrap;">
                <div id="navToggle">
                    <a data-activates="slide-out" onclick="toggleNav()">
                        <img src="<?php echo DIR_ROOT; ?>/assets/img/svgs/menu.svg" alt="메뉴" />
                    </a>
                </div>
            </div>
        </div>
    </header>
    <div style="height:55px;">네비게이션 높이 여백</div>
    <a id="MOVE_TOP_BTN" href="#"><img src="<?php echo DIR_ROOT; ?>/assets/img/svgs/topBtn.svg" alt="스크롤 상단으로 올리기"></a>