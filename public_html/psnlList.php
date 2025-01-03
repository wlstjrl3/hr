<?php include('./components/header.php'); ?>
<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader">
            <b>직원기초정보 모달창</b>
            <button></button>
        </div>
        <div class="modalBody">
            <div class="modalGrp">
                <div class="modalHd">일련번호</div>
                <div class="modalBd"><input readonly style="background:#EEE;" autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">성명</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">세례명</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">연락처</div>
                <div class="modalBd"><input autocomplete='off' class="phoneNumBox"></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">주민번호</div>
                <div class="modalBd"><input autocomplete='off' class="juminNumBox"></div>
            </div>
            <div style="clear:both;"></div>
        </div>
        <div class="modalFooter">
            <button id="goTrsListBtn" style="padding:5px 9px;">발령정보</button>
            <button id="modalEdtBtn" style="padding:5px 9px;">저장</button>
            <button id="modalDelBtn" style="padding:5px 9px;">삭제</button>
        </div>
    </div>
</div>
<br><!--이 위로는 모달 팝업영역, 아래로는 페이지 코드-->
<div class="container">

    <h4 class="cl3 pddS">
        직원 기초정보 관리
    </h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>소속</b></span></div>
            <div class="colBd"><input id="ORG_NM" class="filter" value="<?php echo @$_REQUEST['ORG_NM'];?>"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>성명</b></span></div>
            <div class="colBd"><input id="PSNL_NM" class="filter" value="<?php echo @$_REQUEST['PSNL_NM'];?>"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>세례명</b></span></div>
            <div class="colBd"><input id="BAPT_NM" class="filter" value="<?php echo @$_REQUEST['BAPT_NM'];?>"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>연락처</b></span></div>
            <div class="colBd"><input id="PHONE_NUM" class="phoneNumBox filter" value="<?php echo @$_REQUEST['PHONE_NUM'];?>"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>생년월일</b></span></div>
            <div class="colBd"><input class="dateBox dualDateBox filter" id="PSNL_BIRTH_From"><span>~</span><input class="dateBox dualDateBox filter" id="PSNL_BIRTH_To"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>주민번호</b></span></div>
            <div class="colBd"><input id="PSNL_NUM" class="juminNumBox filter"></div>
        </div>
    </div>
    <div class="clearB"></div>

    <br>
    <div class="tableOutFrm">
        <div class="pddS floatL">
            <a id="newCol" class="pddS clBg3 clW rndCorner pointer">신규</a>
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

<link href="/assets/css/hr_tbl.css?ver=0" rel="stylesheet" />
<link href="/assets/css/modal.css?ver=0" rel="stylesheet" />
<link href="/assets/css/searchArea.css?ver=0" rel="stylesheet" />
<script type='text/javascript' src='/assets/js/hr_tbl.js'></script>
<script type='text/javascript' src='/assets/js/modal.js'></script>
<script type='text/javascript' src='/assets/js/library/xlsx.mini.min.js'></script>
<script type='text/javascript' src='/assets/js/psnlList.js'></script>
<script type='text/javascript' src='/assets/js/dateForm.js'></script>
<script type='text/javascript' src='/assets/js/phoneForm.js'></script>
<script type='text/javascript' src='/assets/js/juminForm.js'></script>

<?php include('components/footer.php'); ?>

<script>

</script>