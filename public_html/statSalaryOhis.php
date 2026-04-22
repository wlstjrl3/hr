<?php include('./components/header.php'); ?>

<!-- 상세 내역 모달 -->
<div id="statDetailModal" class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow" style="max-width: 600px;">
        <div class="modalHeader">
            <b id="detailModalTitle">상세 내역</b>
            <button onclick="closeDetailModal()"></button>
        </div>
        <div class="modalBody" id="detailModalBody">
            <!-- 데이터가 여기에 동적으로 삽입됨 -->
        </div>
        <div class="modalFooter">
            <button id="detailActionBtn" class="pddS clBg3 clW rndCorner pointer" style="display:none;">상세보기</button>
            <button onclick="closeDetailModal()" class="pddS clBg2 clW rndCorner pointer">닫기</button>
        </div>
    </div>
</div>

<div class="container">
    <div class="pddS">
        <h4 class="cl3">예산액 비율현황</h4>
        <p class="fs7 cl2">본당별 예산 항목들의 구성 비율과 현황을 비교 분석합니다.</p>
    </div>

    <div class="searchArea stats-search">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>회계연도</b></span></div>
            <div class="colBd"><input id="S_FSC_YEAR" class="filter" placeholder="YYYY"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>구분</b></span></div>
            <div class="colBd">
                <select id="S_ACC_TYPE" class="filter">
                    <option value="수입">수입</option>
                    <option value="지출" selected>지출</option>
                </select>
            </div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>조직명</b></span></div>
            <div class="colBd"><input id="S_ORG_NM" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>신자수 기준년</b></span></div>
            <div class="colBd"><input id="S_HISTORY_YEAR" class="filter" placeholder="YYYY"></div>
        </div>

        <div class="clearB"></div>
        <div style="margin-top:10px; border-top:1px solid #EEE; padding-top:10px;">
            <div class="cl2 fs7 pddSS"><b>합산 그룹 선택</b></div>
            <div id="groupList" class="group-flex pddS">
                <!-- JS에서 동적으로 채움 -->
            </div>
        </div>
        <div style="margin-top:10px; border-top:1px solid #EEE; padding-top:10px;">
            <div class="cl2 fs7 pddSS"><b>합산할 계정 항목 선택</b></div>
            <div id="accountList" class="account-grid">
                <!-- JS에서 동적으로 채움 -->
                <div class="loading-text">계정 항목 로드 중...</div>
            </div>
            <div class="pddS">
                <button id="toggleAllAccounts" class="fs8 clBg2 clW rndCorner pointer" style="padding:2px 8px;">전체 선택/해제</button>
                <button id="resetSelection" class="fs8 clBg5 cl2 rndCorner pointer" style="padding:2px 8px; margin-left:5px;">선택 초기화</button>
            </div>
        </div>
    </div>

    <div class="clearB"></div>
    <br>

    <div class="tableOutFrm">
        <div class="pddS floatL">
            <div class="showColBg"></div>
            <a id="showCol" class="pddS clBg3 clW rndCorner pointer">표시 항목 변경</a>
        </div><div id="showColList"></div>
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
        <div class="xScroll">
            <table id="myTbl" class="hr_tbl width1850"></table>
        </div>
        <div id="tblPagination"></div>
    </div>
    <br>
</div>

<style>
    .detail-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 12px;
    }

    .detail-table th,
    .detail-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #EEE;
        text-align: center;
    }

    .detail-table th {
        font-size: 13px;
        background: #f8f9fa;
        color: #666;
        font-weight: 600;
    }

    .detail-row:hover {
        background: #f1f7ff;
    }

    .amount-cell {
        text-align: right !important;
    }

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

    .group-flex {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .group-btn {
        padding: 6px 12px;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 20px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .group-btn:hover {
        background: #f0f0f0;
        border-color: #999;
    }

    .group-btn.active {
        background: #4a90e2;
        color: #fff;
        border-color: #4a90e2;
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
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border-radius: 12px;
        padding: 20px !important;
    }

    .opacity-7 {
        opacity: 0.7;
    }

    /* 표시 항목 변경 레이어 스타일 */
    #showColList {
        padding: 15px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        width: 250px;
    }
    .col-toggle-section {
        margin-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 10px;
    }
    .col-toggle-section:last-child {
        border-bottom: none;
    }
    .col-toggle-header {
        font-weight: bold;
        font-size: 12px;
        color: #555;
        margin-bottom: 8px;
        background: #f9f9f9;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .col-toggle-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .col-toggle-item {
        display: flex;
        align-items: center;
        font-size: 13px;
        cursor: pointer;
    }
    .col-toggle-item input {
        margin-right: 6px;
    }
    .col-toggle-item label {
        cursor: pointer;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<link href="<?php echo DIR_ROOT; ?>/assets/css/hr_tbl.css?ver=1775259319" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/modal.css?ver=1775259319" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/searchArea.css?ver=1775259319" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/showColList.css?ver=1775259319" rel="stylesheet" />
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/hr_tbl.js?ver=1775259319'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/modal.js?ver=1775259319'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/library/xlsx.mini.min.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/statSalaryOhis.js'></script>

<?php include('components/footer.php'); ?>