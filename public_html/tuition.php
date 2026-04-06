<?php
include('./components/header.php');
?>
<div class="container">
    <h4 class="cl3 pddS">자녀 학비 보조금 관리</h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>직원 성명</b></span></div>
            <div class="colBd"><input type="text" name="PSNL_NM" id="PSNL_NM" class="filter" placeholder="직원이름 입력">
            </div>
        </div>

        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>자녀 성명</b></span></div>
            <div class="colBd"><input type="text" name="FML_NM" id="FML_NM" class="filter" placeholder="자녀이름 입력"></div>
        </div>

        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>소속 검색</b></span></div>
            <div class="colBd"><input type="text" name="ORG_NM" id="ORG_NM" class="filter" placeholder="본당/소속명 입력">
            </div>
        </div>
        <div class="clearB"></div>
    </div>

    <div class="infoArea mt20 mb10" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="fs7 cl3">
            <i class="fas fa-info-circle"></i> 지급 이력이 있는 직원 전체 정보가 먼저 표시되며, 신규 학자금 등록은 우측 '지급관리' 또는 필터로 직원을 검색하여 추가 하실 수
            있습니다. (상한: 총 8학기)
        </div>
    </div>

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
        <div class="xScroll">
            <table id="myTbl" class="hr_tbl"></table>
        </div>
        <div id="tblPagination"></div>
    </div>
</div>

<!-- 지급 이력 관리 모달 -->
<div class="modalForm" id="tuitionModal">
    <div class="modalBg" onclick="closeModal()"></div>
    <div class="modalWindow popWid800">
        <div class="modalHeader">
            <b>자녀 학자금 지급 이력 관리</b>
            <button type="button" onclick="closeModal()"></button>
        </div>
        <div class="modalBody">
            <input type="hidden" id="modal_FML_CD">
            <input type="hidden" id="modal_PSNL_CD">
            <input type="hidden" id="modal_ISSUE_CD">
            <div class="targetInfo pddS fs7" style="border:1px solid #CCC; border-radius:4px;">
                직원명: <span id="info_psnlNm"></span> &nbsp;|&nbsp;
                자녀명: <span id="info_fmlNm"></span> &nbsp;|&nbsp;
                생년월일: <span id="info_fmlBirth"></span>
            </div>

            <div class="fs7" style="margin-top:15px; padding-bottom:5px;">기존 지급 내역</div>
            <div class="xScroll">
                <table class="hr_tbl fs7" id="historyTbl" style="margin-bottom:20px;">
                    <thead>
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="checkAll"></th>
                            <th style="width:20%">지급일</th>
                            <th style="width:15%">지급액(원)</th>
                            <th style="width:20%">학년</th>
                            <th>비고</th>
                        </tr>
                    </thead>
                    <tbody id="historyBody">
                    </tbody>
                </table>
            </div>

            <div
                style="margin-top:15px; font-weight:bold; padding-bottom:10px; border-top:1px solid #DDD; padding-top:15px;">
                지급 정보 상세</div>
            <form id="addFrm" onsubmit="saveIssue(); return false;">
                <div class="modalGrpFrame">
                    <div class="modalGrp">
                        <div class="modalHd">지급일</div>
                        <div class="modalBd"><input type="date" id="add_issueDt" required></div>
                    </div>
                    <div class="modalGrp">
                        <div class="modalHd">지급액 (원)</div>
                        <div class="modalBd"><input type="number" id="add_issueAmt" class="tar" placeholder="예: 1500000"
                                required></div>
                    </div>
                    <div class="modalGrp">
                        <div class="modalHd">학년 학기</div>
                        <div class="modalBd"><input type="text" id="add_schoolGrade" placeholder="예: 1학년 1학기"></div>
                    </div>
                    <div class="modalGrp">
                        <div class="modalHd">비고</div>
                        <div class="modalBd"><input type="text" id="add_memo" placeholder="비고 입력"></div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </form>

        </div>
        <div class="modalFooter">
            <button id="modalNewBtn" style="padding:5px 9px;">신규 입력</button>
            <button id="modalEdtBtn" style="padding:5px 9px;">저장</button>
            <button id="modalDelBtn" style="padding:5px 9px;">삭제</button>
        </div>
    </div>
</div>

<link href="<?php echo DIR_ROOT; ?>/assets/css/hr_tbl.css?ver=1775259319" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/modal.css?ver=1775259319" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/searchArea.css?ver=1775259319" rel="stylesheet" />

<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/hr_tbl.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/modal.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/library/xlsx.mini.min.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/tuition.js'></script>

<?php include('components/footer.php'); ?>