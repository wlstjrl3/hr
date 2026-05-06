<?php
include("./dbconn/dbconn.php");
error_reporting(E_ALL);
ini_set("display_errors", 0);
ini_set("log_errors", 1);
session_start();
if (empty($_SESSION["USER_ID"]) && $_SERVER['PHP_SELF'] != DIR_ROOT . '/login.php' && $_SERVER['PHP_SELF'] != DIR_ROOT . '/style.php') {
    echo "<script>document.location.href='" . DIR_ROOT . "/login.php';</script>";
    die('관리자로그인 필요');
}
// 현재 페이지 경로 (active 클래스 부여용)
$curPath = strtok($_SERVER['REQUEST_URI'], '?');
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset='utf-8' />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo DIR_ROOT; ?>/assets/css/common.css?ver=1775259319" rel="stylesheet" />
    <link href="<?php echo DIR_ROOT; ?>/assets/css/header.css?ver=<?php echo time(); ?>" rel="stylesheet" />
    <script defer src="https://sinseiki.github.io/noIE.js/noIE.js"></script>
    <script type='text/javascript'>
        const DIR_ROOT = '<?php echo DIR_ROOT; ?>';
        const API_TOKEN = '<?php echo $_SESSION['API_TOKEN'] ?? ''; ?>';
    </script>
    <title>제1대리구 소속직원 인적 관리시스템</title>
</head>

<body>
<?php
// 메뉴 구조 정의
$menuGroups = [
    [
        'label' => '인사정보',
        'items' => [
            ['url' => DIR_ROOT . '/newEmpReg',      'label' => '✚ 통합 직원등록',  'highlight' => true],
            ['url' => DIR_ROOT . '/psnlTotal?TRS_TYPE=1', 'label' => '직원 종합정보'],
            ['url' => DIR_ROOT . '/certPrint',       'label' => '증명서 발급'],
            ['url' => DIR_ROOT . '/insList',         'label' => '보증보험 정보'],
        ]
    ],
    [
        'label' => '승급/급여',
        'items' => [
            ['url' => DIR_ROOT . '/advList',  'label' => '승급 대상자 현황'],
            ['url' => DIR_ROOT . '/grdBatch', 'label' => '일괄 호봉 갱신'],
            ['url' => DIR_ROOT . '/salaryList.php', 'label' => '급호봉 테이블'],
            ['url' => DIR_ROOT . '/mpayList.php',   'label' => '월별 개인급여'],
        ]
    ],
    [
        'label' => '기초정보',
        'items' => [
            ['url' => DIR_ROOT . '/orgList',   'label' => '본당/성지 관리'],
            ['url' => DIR_ROOT . '/orgBudget', 'label' => '본당예산 관리'],
            ['url' => DIR_ROOT . '/ohisList',  'label' => '년도별 신자수'],
            ['url' => DIR_ROOT . '/user',      'label' => '사용자 관리'],
        ]
    ],
    [
        'label' => '통계/자료',
        'items' => [
            ['url' => DIR_ROOT . '/statOrgHr',          'label' => '본당별 직원현황'],
            ['url' => DIR_ROOT . '/statWorkType',        'label' => '고용형태 현황'],
            ['url' => DIR_ROOT . '/statWorkTypeGraph',   'label' => '고용형태 그래프'],
            ['url' => DIR_ROOT . '/statWorkTypeGraph2',  'label' => '직군별 상세 그래프'],
            ['url' => DIR_ROOT . '/statPsnlTable',       'label' => '인사관리 대상 현황'],
            ['url' => DIR_ROOT . '/statSalaryOhis',      'label' => '예산액 비율현황'],
        ]
    ],
];

// active 판별 함수
function isActive($url, $curPath) {
    $urlPath = strtok($url, '?');
    return ($urlPath && strpos($curPath, $urlPath) !== false);
}
function groupHasActive($group, $curPath) {
    foreach ($group['items'] as $item) {
        if (isActive($item['url'], $curPath)) return true;
    }
    return false;
}
?>

<header id="header">
    <div class="hd-inner">
        <!-- 브랜드 -->
        <a class="hd-brand" href="<?php echo DIR_ROOT; ?>/">
            <div class="hd-brand-icon">⛪</div>
            <div class="hd-brand-text">
                대리구 HR
                <small>직원 인사 관리 시스템</small>
            </div>
        </a>

        <!-- 데스크탑 메뉴 -->
        <ul class="hd-menu" id="hdMenuDesktop">
            <?php foreach ($menuGroups as $group): ?>
            <li class="hd-item<?php echo groupHasActive($group, $curPath) ? ' active' : ''; ?>">
                <a href="#">
                    <?php echo $group['label']; ?>
                    <span class="arrow">▾</span>
                </a>
                <ul class="hd-dropdown">
                    <?php foreach ($group['items'] as $item): ?>
                    <li>
                        <a href="<?php echo $item['url']; ?>"
                           class="<?php echo (!empty($item['highlight']) ? 'hd-highlight' : '') . (isActive($item['url'], $curPath) ? ' hd-highlight' : ''); ?>">
                            <?php echo $item['label']; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- 우측 영역 -->
        <div class="hd-right">
            <?php if (!empty($_SESSION['USER_ID'])): ?>
            <span class="hd-user-badge">👤 <?php echo htmlspecialchars($_SESSION['USER_ID']); ?></span>
            <?php endif; ?>
            <a class="hd-logout" href="<?php echo DIR_ROOT; ?>/logout.php">로그아웃</a>
        </div>

        <!-- 모바일 햄버거 -->
        <button class="hd-hamburger" id="hdHamburger" onclick="toggleMobileMenu()" aria-label="메뉴 열기">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<!-- 모바일 오버레이 메뉴 -->
<div class="hd-mobile-overlay" id="hdMobileOverlay">
    <?php foreach ($menuGroups as $group): ?>
    <div class="hd-mob-group">
        <div class="hd-mob-group-title"><?php echo $group['label']; ?></div>
        <?php foreach ($group['items'] as $item): ?>
        <a href="<?php echo $item['url']; ?>"
           class="<?php echo (!empty($item['highlight']) ? 'hd-highlight' : '') . (isActive($item['url'], $curPath) ? ' hd-highlight' : ''); ?>"
           onclick="closeMobileMenu()">
            <?php echo $item['label']; ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    <div class="hd-mob-group">
        <div class="hd-mob-group-title">계정</div>
        <a href="<?php echo DIR_ROOT; ?>/logout.php">로그아웃</a>
    </div>
</div>

<div class="hd-spacer"></div>
<a id="MOVE_TOP_BTN" href="#"><img src="<?php echo DIR_ROOT; ?>/assets/img/svgs/topBtn.svg" alt="스크롤 상단으로 올리기"></a>

<script>
// ── 스크롤 숨김/표시 ──
(function() {
    let lastY = 0;
    window.addEventListener('scroll', function() {
        const hd = document.getElementById('header');
        const y  = window.scrollY;
        if (y < 10) {
            hd.classList.remove('nav-up');
        } else if (y > lastY + 8) {
            hd.classList.add('nav-up');
            closeMobileMenu();
        } else if (y < lastY - 8) {
            hd.classList.remove('nav-up');
        }
        lastY = y;
        // TOP 버튼
        const btn = document.getElementById('MOVE_TOP_BTN');
        if (btn) btn.style.opacity = y > 200 ? '1' : '0';
    }, { passive: true });
})();

// ── 모바일 메뉴 ──
function toggleMobileMenu() {
    const overlay = document.getElementById('hdMobileOverlay');
    const ham     = document.getElementById('hdHamburger');
    const isOpen  = overlay.classList.contains('open');
    if (isOpen) { closeMobileMenu(); } else {
        overlay.classList.add('open');
        ham.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}
function closeMobileMenu() {
    document.getElementById('hdMobileOverlay').classList.remove('open');
    document.getElementById('hdHamburger').classList.remove('open');
    document.body.style.overflow = '';
}

// ESC 키로 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeMobileMenu();
});
</script>