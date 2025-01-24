<?php include('./components/header.php'); ?>
<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader"></div>
        <div class="modalBody"></div>
        <div class="modalFooter"></div>
    </div>
</div>
<br><!--이 위로는 모달 팝업영역, 아래로는 페이지 코드-->
<div class="container">

    <h4 class="cl3 pddS">
        일괄 호봉 갱신
    </h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>성명</b></span><br></div>
            <div class="colBd"><input id="PSNL_NM" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>승급변동일</b></span></div>
            <div class="colBd"><input class="dualDateBox dateBox filter" id="ADVANCE_DT_From"><span>~</span><input class="dualDateBox dateBox filter" id="ADVANCE_DT_To"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>급</b></span></div>
            <div class="colBd"><input id="GRD_GRADE" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>호</b></span></div>
            <div class="colBd"><input id="GRD_PAY" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>메모</b></span></div>
            <div class="colBd"><input id="GRD_DTL" class="filter"></div>
        </div> 
        <div class="clearB"></div>
    </div>
    <br>
    <div class="tableOutFrm">
        <div class="pddS floatL">
            <a id="newCol" class="hidden pddS clBg3 clW rndCorner pointer">신규</a>
            <a id="xport" class="hidden pddS clBg3 clW rndCorner pointer">엑셀 다운로드</a>

            <a id="batchInsert" class="pddS clBg3 clW rndCorner pointer">일괄처리</a>
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
<script type='text/javascript' src='/assets/js/grdBatch.js'></script>
<script type='text/javascript' src='/assets/js/dateForm.js'></script>

<?php include('components/footer.php'); ?>

<script>

</script>