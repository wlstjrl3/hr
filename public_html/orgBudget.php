<?php

include('./components/header.php');

?>
<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader">
            <b>예산 정보 상세</b>
            <button></button>
        </div>
        <div class="modalBody">
            <div class="modalGrp">
                <div class="modalHd">회계연도</div>
                <div class="modalBd"><input id="FSC_YEAR" autocomplete='off'></div>
            </div>            
            <div class="modalGrp">
                <div class="modalHd">조직번호</div>
                <div class="modalBd">
                    <input id="ORG_CD" style="width:calc(100% - 30px);background:#EEE" readonly autocomplete='off'>
                </div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">조직명</div>
                <div class="modalBd"><input id="ORG_NM" style="background:#EEE" readonly autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">계정명</div>
                <div class="modalBd"><input id="ACC_NM" autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">구분</div>
                <div class="modalBd">
                    <select id="ACC_TYPE">
                        <option value="수입">수입</option>
                        <option value="지출">지출</option>
                    </select>
                </div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">금액</div>
                <div class="modalBd"><input id="AMOUNT" autocomplete='off' type="number"></div>
            </div>
            <div style="clear:both;"></div>
        </div>
        <div class="modalFooter">
            <button id="modalEdtBtn" style="padding:5px 9px;">저장</button>
            <button id="modalDelBtn" style="padding:5px 9px;">삭제</button>
        </div>
    </div>
</div>
<br><!--이 위로는 모달 팝업영역, 아래로는 페이지 코드-->
<div class="container">

    <h4 class="cl3 pddS">
        본당 예산 관리
    </h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>회계연도</b></span></div>
            <div class="colBd"><input id="S_FSC_YEAR" class="filter" placeholder="YYYY"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>조직명</b></span></div>
            <div class="colBd"><input id="S_ORG_NM" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>계정명</b></span></div>
            <div class="colBd"><input id="S_ACC_NM" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>구분</b></span></div>
            <div class="colBd">
                <select id="S_ACC_TYPE" class="filter">
                    <option value="">전체</option>
                    <option value="수입">수입</option>
                    <option value="지출">지출</option>
                </select>
            </div>
        </div>
    </div>
    <div class="clearB"></div>

    <br>
    <div class="tableOutFrm">
        <div class="pddS floatL">
            <a id="newCol" class="pddS clBg3 clW rndCorner pointer">신규</a>
            <input type="file" id="excelFile" style="display:none;" accept=".xlsx, .xls">
            <a id="uploadExcel" class="pddS clBg3 clW rndCorner pointer">예산 엑셀 업로드</a>
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

<link href="<?php echo DIR_ROOT; ?>/assets/css/hr_tbl.css?ver=1775259319" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/modal.css?ver=1775259319" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/searchArea.css?ver=1775259319" rel="stylesheet" />
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/hr_tbl.js?ver=1775259319'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/modal.js?ver=1775259319'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/library/xlsx.mini.min.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/orgBudget.js'></script>

<?php include('components/footer.php'); ?>
