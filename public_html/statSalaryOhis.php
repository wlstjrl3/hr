<?php include('./components/header.php'); ?>

<div class="container">
    <div class="pddS">
        <h4 class="cl3">신자수 대비 급여 통계</h4>
        <p class="fs7 cl2">본당별 신자수와 결산 데이터(급여/인건비 등)를 비교 분석합니다.</p>
    </div>

    <div class="searchArea stats-search">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>회계연도</b></span></div>
            <div class="colBd"><input id="S_FSC_YEAR" class="filter" placeholder="YYYY"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>조직명</b></span></div>
            <div class="colBd"><input id="S_ORG_NM" class="filter"></div>
        </div>
        
        <div class="clearB"></div>
        <div style="margin-top:10px; border-top:1px solid #EEE; padding-top:10px;">
            <div class="cl2 fs7 pddSS"><b>합산할 계정 항목 선택</b></div>
            <div id="accountList" class="account-grid">
                <!-- JS에서 동적으로 채움 -->
                <div class="loading-text">계정 항목 로드 중...</div>
            </div>
            <div class="pddS">
                <button id="toggleAllAccounts" class="fs8 clBg2 clW rndCorner pointer" style="padding:2px 8px;">전체 선택/해제</button>
            </div>
        </div>
    </div>
    
    <div class="clearB"></div>
    <br>

    <div class="tableOutFrm">
        <div class="pddS floatL">
            <a id="xport" class="pddS clBg3 clW rndCorner pointer">엑셀 다운로드</a>
        </div>
        <div class="pddS floatR">
            <span>페이지당</span>
            <select class="tblLimit fs7 pddSS rndCorner clBrC">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select> 
            <span>개씩 보기</span>
        </div>
        <table id="myTbl"></table>
        <div id="tblPagination"></div>
    </div>
    <br>
</div>

<style>
.account-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 8px;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 8px;
    max-height: 150px;
    overflow-y: auto;
    border: 1px solid #ddd;
}
.account-item {
    display: flex;
    align-items: center;
    font-size: 13px;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: background 0.2s;
}
.account-item:hover {
    background: #eef4ff;
}
.account-item input {
    margin-right: 8px;
}
.loading-text {
    grid-column: 1 / -1;
    text-align: center;
    color: #888;
}
.stats-search {
    background: linear-gradient(135deg, #ffffff 0%, #f3f7ff 100%);
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border-radius: 12px;
    padding: 20px !important;
}
</style>

<link href="<?php echo DIR_ROOT; ?>/assets/css/hr_tbl.css?ver=1775259319" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/searchArea.css?ver=1775259319" rel="stylesheet" />
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/hr_tbl.js?ver=1775259319'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/library/xlsx.mini.min.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/statSalaryOhis.js'></script>

<?php include('components/footer.php'); ?>
