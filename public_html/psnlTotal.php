<?php include('./components/header.php'); ?>
<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader">직원 종합정보 조회</div>
        <div class="modalBody">
            <div id="psnlTbl">
                <span class="fontWBold" id="mdBdOrgInTel">내선 : </span>
                <ul>
                    <li class="th clBg5"><span>소속</span></li><li class="td clBgW"><span id="mdBdOrgNm">소속임</span></li>
                    <li class="th clBg5"><span>직책</span></li><li class="td clBgW"><span id="mdBdPosition">직책임</span></li>
                    <li class="th clBg5"><span>성명</span></li><li class="td clBgW"><span id="mdBdPsnlNm">이름임</span></li>
                    <li class="th clBg5"><span>세례명</span></li><li class="td clBgW"><span id="mdBdBaptNm">세례임</span></li>
                    <li class="th clBg5"><span>인사구분</span></li><li class="td clBgW"><span id="mdBdTrsType">인사임</span></li>
                    <li class="th clBg5"><span>발령일</span></li><li class="td clBgW"><span id="mdBdTrsDt">1000-01-01</span></li>
                    <li class="th clBg5"><span>채용구분</span></li><li class="td clBgW"><span id="mdBdWorkType">채용임</span></li>
                    <li class="th clBg5"><span>급호봉</span></li><li class="td clBgW"><span id="mdBdGrdPay">N급 N호</span></li>
                    <li class="th clBg5"><span>승급일</span></li><li class="td clBgW"><span id="mdBdAdvDt">1000-01-01</span></li>
                    <li class="th clBg5"><span>승급분기</span></li><li class="td clBgW"><span id="mdBdAdvRng">분기임</span></li>
                    <li class="th clBg5"><span>주민번호</span></li><li class="td clBgW"><span id="mdBdPsnlNum" class="fs8">000000-0000000</span></li>
                    <li class="th clBg5"><span>연락처</span></li><li class="td clBgW"><span id="mdBdPhoneNum" class="fs8">010-0000-0000</span></li>
                </ul>
            </div>
            <div id="fmlTbl"></div>
            <div id="adjTbl"></div>
            <div id="opiTbl"></div>
        </div>
        <div class="modalFooter">
            <button id="goPsnlListBtn" style="margin:0 0 5px 0;padding:5px 9px;">기초정보</button>
            <button id="goTrsListBtn" style="margin:0 0 5px 0;padding:5px 9px;">발령정보</button>
            <button id="goGrdListBtn" style="margin:0 0 5px 0;padding:5px 9px;">급호봉관리</button>
            <button id="goFmlListBtn" style="margin:0 0 5px 0;padding:5px 9px;">가족정보</button>
            <button id="goAdjListBtn" style="margin:0 0 5px 0;padding:5px 9px;">제수당</button>
            <button id="goInsListBtn" style="margin:0 0 5px 0;padding:5px 9px;">보증보험</button>
            <button id="goOpiListBtn" style="margin:0 0 5px 0;padding:5px 9px;">상벌정보</button>
            <button id="goMPayListBtn" style="margin:0 0 5px 0;padding:5px 9px;">월별급여상세</button>
            <button id="goPttListBtn" style="margin:0 0 5px 0;padding:5px 9px;">최저시급정보</button>
        </div>
    </div>
</div>
<br><!--이 위로는 모달 팝업영역, 아래로는 페이지 코드-->
<div class="container">

    <h4 class="cl3 pddS">
        직원 종합정보 조회
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
            <div class="colHd clBg5 cl2"><span><b>직책</b></span></div>
            <div class="colBd"><input id="POSITION" class="filter" value="<?php echo @$_REQUEST['POSITION'];?>"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>고용형태</b></span></div>
            <div class="colBd"><select id="WORK_TYPE" class="filter">
                <option value="">전체</option><option>정규</option><option>계약</option><option>기능</option>
            </select></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>재직구분</b></span></div>
            <div class="colBd"><select id="TRS_TYPE" class="filter">
                <option value="1">재직(+전보)</option><option value="2">퇴사</option><option value="">전체</option>
            </select></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>연락처</b></span></div>
            <div class="colBd"><input id="PHONE_NUM" class="phoneNumBox filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>생년월일</b></span></div>
            <div class="colBd"><input class="dateBox dualDateBox filter" id="PSNL_BIRTH_From"><span>~</span><input class="dateBox dualDateBox filter" id="PSNL_BIRTH_To"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>주민번호</b></span></div>
            <div class="colBd"><input id="PSNL_NUM" class="juminNumBox filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>입/퇴사일</b></span></div>
            <div class="colBd"><input class="dateBox dualDateBox filter" id="TRS_DT_From"><span>~</span><input class="dateBox dualDateBox filter" id="TRS_DT_To"></div>
        </div>        
        <div class="colGrp clBg4">
            <div class="txtCenter" style="padding:5px 0 6px 0;">
                <a class="pddSS clBg2 clW rndCorner pointer quikSetBtn" id="setRetire">정년 대상</a>
                <a class="pddSS clBg2 clW rndCorner pointer quikSetBtn" id="set10Yr">근속10년</a>
                <a class="pddSS clBg2 clW rndCorner pointer quikSetBtn" id="set20Yr">근속20년</a>
                <a class="pddSS clBg2 clW rndCorner pointer quikSetBtn" id="set30Yr">근속30년</a>
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
            <a href="/psnlList" class="pddS clBg3 clW rndCorner pointer">신규입력 페이지이동</a>
        </div>
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
            <table id="myTbl" class="width1850"></table>
        </div>
        <div id="tblPagination"></div>
    </div>
    <br>
</div>

<link href="/assets/css/hr_tbl.css?ver=0" rel="stylesheet" />
<link href="/assets/css/modal.css?ver=0" rel="stylesheet" />
<link href="/assets/css/searchArea.css?ver=0" rel="stylesheet" />
<link href="/assets/css/showColList.css?ver=0" rel="stylesheet" />
<link href="/assets/css/psnlTotal.css?ver=0" rel="stylesheet" />
<script type='text/javascript' src='/assets/js/hr_tbl.js'></script>
<script type='text/javascript' src='/assets/js/modal.js'></script>
<script type='text/javascript' src='/assets/js/library/xlsx.mini.min.js'></script>
<script type='text/javascript' src='/assets/js/psnlTotal.js'></script>
<script type='text/javascript' src='/assets/js/dateForm.js'></script>
<script type='text/javascript' src='/assets/js/phoneForm.js'></script>
<script type='text/javascript' src='/assets/js/juminForm.js'></script>
<script type='text/javascript' src='/assets/js/dateFormat.js'></script>

<?php include('components/footer.php'); ?>

<script>

</script>