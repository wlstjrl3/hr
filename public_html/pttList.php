<?php include('./components/header.php'); ?>
<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader">
            <b>최저임금 대상 정보 </b>
            <button></button>
        </div>
        <div class="modalBody">
            <p>직원정보 : <b>조직 직책 성명</b></p>
            <div class="modalGrp">
                <div class="modalHd">일련번호</div>
                <div class="modalBd"><input readonly style="background:#EEE;" autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">기준년도</div>
                <div class="modalBd"><input autocomplete='off' placeholder="2000"  maxlength="4"></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">주 근무일수</div>
                <div class="modalBd"><input autocomplete='off' class="dateBox"></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">주 근무시간</div>
                <div class="modalBd"><input autocomplete='off' class="dateBox"></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">연장근무시간</div>
                <div class="modalBd"><input autocomplete='off' class="dateBox"></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">추가사유</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">추가금액</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div style="clear:both;"></div>
        </div>
        <div class="modalFooter">
            <button id="goPsnlTotalBtn" style="padding:5px 9px;">종합정보페이지</button>
            <button id="modalEdtBtn" style="padding:5px 9px;">저장</button>
            <button id="modalDelBtn" style="padding:5px 9px;">삭제</button>
        </div>
    </div>
</div>
<br><!--이 위로는 모달 팝업영역, 아래로는 페이지 코드-->
<div class="container">

    <h4 class="cl3 pddS">
        최저임금 대상 정보
    </h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd2L clBg5 cl2"><span><b>개인코드<br>/ 조직명</b></span></div>
            <div class="colBd">
                <input class="clBg5 dualDateBox" id="PSNL_CD" class="filter" readonly style="border:0;" value="<?php echo @$_REQUEST['PSNL_CD'];?>"><span>/</span><input class="clBg5 dualDateBox" id="ORG_NM" class="" readonly style="border:0;" value="<?php echo @$_REQUEST['ORG_NM'];?>">
            </div>
        </div>        
        <div class="colGrp">
            <div class="colHd2L clBg5 cl2">
                <span><b>직책<br>/ 직원성명</b></span><br>
            </div>
            <div class="colBd" style="">
                <input class="clBg5" id="POSITION" readonly style="width:calc(40%);border:0;" value="<?php echo @$_REQUEST['POSITION'];?>">
                <input id="PSNL_NM" style="width:calc(60% - 45px);" placeholder="성명" value="<?php echo @$_REQUEST['PSNL_NM'];?>">
                <button id="psnlSerchPop" style="padding:3px;">검색</button>
            </div>    
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>기준년도</b></span></div>
            <div class="colBd"><input id="PTT_YEAR" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>주근무일수</b></span></div>
            <div class="colBd"><input id="PTT_DAY" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>주근무시간</b></span></div>
            <div class="colBd"><input id="PTT_HOUR" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>사제인원수</b></span></div>
            <div class="colBd"><input id="PTT_ADJPAY" class="filter"></div>
        </div> 
        <div class="clearB"></div>
    </div>
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
<script type='text/javascript' src='/assets/js/pttList.js'></script>
<script type='text/javascript' src='/assets/js/dateForm.js'></script>

<?php include('components/footer.php'); ?>

<script>

</script>