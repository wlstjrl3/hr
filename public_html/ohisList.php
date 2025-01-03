<?php include('./components/header.php'); ?>
<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader">
            <b>년도별 신자수 모달창</b>
            <button></button>
        </div>
        <div class="modalBody">
            <div class="modalGrp">
                <div class="modalHd">일련번호</div>
                <div class="modalBd"><input readonly style="background:#EEE;" autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">조직</div>
                <div class="modalBd">
                    <input id="orgCd" style="width:calc(50% - 30px);background:#EEE" readonly autocomplete='off' placeholder="조직코드">
                    <input id="orgNm" style="width:calc(50% - 30px);" autocomplete='off' placeholder="조직명">
                    <button id="orgSerchPop" style="width:30px;padding:4px 0;">검색</button>
                </div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">기준일</div>
                <div class="modalBd"><input autocomplete='off' class="dateBox"></div>
            </div>            
            <div class="modalGrp">
                <div class="modalHd">신자수</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">기타사항</div>
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
        년도별 신자수 테이블
    </h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>조직명</b></span></div>
            <div class="colBd"><input id="ORG_NM" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>기준일</b></span></div>
            <div class="colBd"><input class="dualDateBox dateBox filter" id="OH_DT_From"><span>~</span><input class="dualDateBox dateBox filter" id="OH_DT_To"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>신자수</b></span></div>
            <div class="colBd"><input class="dualDateBox filter" id="PERSON_CNT_From"><span>~</span><input class="dualDateBox filter" id="PERSON_CNT_To"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>기타사항</b></span></div>
            <div class="colBd"><input id="ETC" class="filter"></div>
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
<script type='text/javascript' src='/assets/js/excelDtForm.js'></script>
<script type='text/javascript' src='/assets/js/library/xlsx.mini.min.js'></script>
<script type='text/javascript' src='/assets/js/ohisList.js'></script>
<script type='text/javascript' src='/assets/js/dateForm.js'></script>

<?php include('components/footer.php'); ?>

<script>

</script>