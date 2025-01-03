<?php include('./components/header.php'); ?>
<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader">
            <b>연도별급여 모달창</b>
            <button></button>
        </div>
        <div class="modalBody">
            <div class="modalGrp">
                <div class="modalHd">일련번호</div>
                <div class="modalBd"><input readonly style="background:#EEE;" autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">기준연도</div>
                <div class="modalBd"><input autocomplete='off' class="dateBox"></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">타입</div>
                <div class="modalBd"><select>
                    <option value="">선택</option>
                    <option>정규직</option>
                    <option>기능직</option>
                    <option>계약직</option>
                    <option>최저시급</option>
                </select></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">급</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">호</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">기본급</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">법정수당<small>|최저임금</small></div>
                <div class="modalBd"><input autocomplete='off'></div>
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
        연도별 급호봉 테이블 현황
    </h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>기준연도</b></span></div>
            <div class="colBd"><input id="SLR_YEAR" class="filter dateBox" maxlength="4"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>타입</b></span></div>
            <div class="colBd"><input id="SLR_TYPE" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>급 & 호</b></span></div>
            <div class="colBd"><input id="SLR_GRADE" class="dualDateBox filter"><span style="font-size:10px;">&</span><input id="SLR_PAY" class="dualDateBox filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>기본급</b></span></div>
            <div class="colBd"><input class="dualDateBox filter" id="NORMAL_PAY_From"><span>~</span><input class="dualDateBox filter" id="NORMAL_PAY_To"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>법정수당</b></span></div>
            <div class="colBd"><input class="dualDateBox filter" id="LEGAL_PAY_From"><span>~</span><input class="dualDateBox filter" id="LEGAL_PAY_To"></div>
        </div>
    </div>
    <div class="clearB"></div>

    <br>
    <div class="tableOutFrm">
        <div class="pddS floatL">
            <a id="newCol" class="pddS clBg3 clW rndCorner pointer">신규</a>
            <a id="xport" class="pddS clBg3 clW rndCorner pointer">엑셀 다운로드</a>
            <label class="floatR crud_button pddS clBg3 clW rndCorner pointer" for="file">엑셀 업로드</label>
            <input class="floatR upload-name" value="" placeholder="첨부파일">
            <input class="hidden" type="file" id="file">
            
            <a id="batchDel" class="pddS clBg3 clW rndCorner pointer">일괄삭제</a>
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

<link href="/assets/css/hr_tbl.css?ver=0" rel="stylesheet" />
<link href="/assets/css/modal.css?ver=0" rel="stylesheet" />
<link href="/assets/css/searchArea.css?ver=0" rel="stylesheet" />
<script type='text/javascript' src='/assets/js/hr_tbl.js'></script>
<script type='text/javascript' src='/assets/js/modal.js'></script>
<script type='text/javascript' src='/assets/js/library/xlsx.mini.min.js'></script>
<script type='text/javascript' src='/assets/js/slrList.js'></script>
<script type='text/javascript' src='/assets/js/dateForm.js'></script>

<?php include('components/footer.php'); ?>

<script>

</script>