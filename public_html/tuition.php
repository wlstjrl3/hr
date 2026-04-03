<?php
include('./components/header.php');
?>
<div class="container">
    <h4 class="cl3 pddS">자녀 학비 보조금 관리</h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>직원 성명</b></span></div>
            <div class="colBd"><input type="text" name="PSNL_NM" id="PSNL_NM" class="filter" placeholder="직원이름 입력"></div>
        </div>
        
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>자녀 성명</b></span></div>
            <div class="colBd"><input type="text" name="FML_NM" id="FML_NM" class="filter" placeholder="자녀이름 입력"></div>
        </div>
        
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>소속 검색</b></span></div>
            <div class="colBd"><input type="text" name="ORG_NM" id="ORG_NM" class="filter" placeholder="본당/소속명 입력"></div>
        </div>
        <div class="clearB"></div>
    </div>

    <div class="infoArea mt20 mb10" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="fs7 cl3">
            <i class="fas fa-info-circle"></i> 지급 이력이 있는 직원 전체 정보가 먼저 표시되며, 신규 학자금 등록은 우측 '지급관리' 또는 필터로 직원을 검색하여 추가 하실 수 있습니다. (상한: 총 8학기)
        </div>
    </div>

    <div class="tableOutFrm">
        <div class="pddS floatL">
            <a id="xportBtn" class="pddS clBg3 clW rndCorner pointer">엑셀 다운로드</a>
        </div>
        <div class="pddS floatR">
            총 <span id="totalCntTxt" class="fwb clBlue fs5">0</span> 건
        </div>
        <div class="xScroll">
            <table id="myTbl" class="hr_tbl">
                <thead>
                    <tr>
                        <th class="openCol">반응</th>
                        <th>소속</th>
                        <th>직원명</th>
                        <th>자녀명</th>
                        <th>자녀생년월일</th>
                        <th>지원시작학년</th>
                        <th>지원회차</th>
                        <th>잔여회차</th>
                        <th>지원금 누계</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody id="listBody">
                    <tr>
                        <td colspan="10" class="tac">조건을 입력하고 검색해주세요.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 지급 이력 관리 모달 -->
<div class="modalForm" id="tuitionModal">
    <div class="modalBg" onclick="closeModal()"></div>
    <div class="modalWindow popWid800">
        <div class="modalHeader">
            <h3 id="modalTitle">자녀 학자금 지급 이력 관리</h3>
            <button type="button" class="closeBtn" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modalBody">
            <input type="hidden" id="modal_FML_CD">
            <input type="hidden" id="modal_PSNL_CD">
            <div class="targetInfo mb10 p10" style="background:#FFF; border:1px solid #CCC; border-radius:4px;">
                <strong>직원명:</strong> <span id="info_psnlNm"></span> &nbsp;|&nbsp; 
                <strong>자녀명:</strong> <span id="info_fmlNm"></span> &nbsp;|&nbsp; 
                <strong>생년월일:</strong> <span id="info_fmlBirth"></span>
            </div>

            <div style="margin-top:15px; font-weight:bold; padding-bottom:5px;">기존 지급 내역</div>
            <table class="hr_tbl" id="historyTbl" style="margin-bottom:20px;">
                <thead>
                    <tr>
                        <th style="width:20%">지급일</th>
                        <th style="width:20%">지급액(원)</th>
                        <th style="width:20%">학년</th>
                        <th style="width:25%">비고</th>
                        <th style="width:15%">관리</th>
                    </tr>
                </thead>
                <tbody id="historyBody">
                </tbody>
            </table>

            <div style="margin-top:15px; font-weight:bold; padding-bottom:5px;">신규 지급내역 추가</div>
            <form id="addFrm" onsubmit="saveIssue(); return false;">
                <div class="modalGrpFrame">
                    <div class="modalGrp">
                        <div class="modalHd">지급일</div>
                        <div class="modalBd"><input type="date" id="add_issueDt" required></div>
                    </div>
                    <div class="modalGrp">
                        <div class="modalHd">지급액 (원)</div>
                        <div class="modalBd"><input type="number" id="add_issueAmt" class="tar" placeholder="예: 1500000" required></div>
                    </div>
                    <div class="modalGrp">
                        <div class="modalHd">해당학년</div>
                        <div class="modalBd"><input type="text" id="add_schoolGrade" placeholder="예: 대학 1학년"></div>
                    </div>
                    <div class="modalGrp">
                        <div class="modalHd">비고</div>
                        <div class="modalBd"><input type="text" id="add_memo" placeholder="비고 입력"></div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <div class="tac mt10">
                    <button type="submit" class="pddM clBg3 clW rndCorner pointer fs7" style="border:none;">내역 추가</button>
                </div>
            </form>

        </div>
        <div class="modalFooter tac">
            <button type="button" class="btn gray" onclick="closeModal()">닫기</button>
        </div>
    </div>
</div>

<link href="<?php echo DIR_ROOT; ?>/assets/css/hr_tbl.css?ver=0" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/modal.css?ver=0" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/searchArea.css?ver=0" rel="stylesheet" />

<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/tuition.js'></script>

<?php include('components/footer.php'); ?>
