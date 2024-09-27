<?php include('./components/header.php'); ?>
<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader">직원 종합정보 조회</div>
        <div class="modalBody" style="background:#FFF;">
            <table id="psnlTbl">
                <tr>
                    <th><span>소속</span></th><td><span id="mdBdOrgNm">소속임</span></td>
                    <th><span>직책</span></th><td><span id="mdBdPosition">직책임</span></td>
                    <th><span>성명</span></th><td><span id="mdBdPsnlNm">이름임</span></td>
                    <th><span>세례명</span></th><td><span id="mdBdBaptNm">세례임</span></td>
                </tr>
                <tr>
                    <th><span>인사구분</span></th><td><span id="mdBdTrsType">인사임</span></td>
                    <th><span>발령일</span></th><td><span id="mdBdTrsDt">1000-01-01</span></td>
                    <th><span>채용구분</span></th><td><span id="mdBdWorkType">채용임</span></td>
                    <th><span>급호봉</span></th><td><span id="mdBdGrdPay">N급 N호</span></td>
                </tr>
                <tr>
                    <th><span>승급일</span></th><td><span id="mdBdAdvDt">1000-01-01</span></td>
                    <th><span>승급분기</span></th><td><span id="mdBdAdvRng">분기임</span></td>
                    <th><span>주민번호</span></th><td><span id="mdBdPsnlNum">000000-0000000</span></td>
                    <th><span>연락처</span></th><td><span id="mdBdPhoneNum">010-0000-0000</span></td>
                </tr>
            </table>
            <table id="fmlTbl"></table>
            <table id="lcsTbl"></table>
            <table id="opiTbl"></table>
        </div>
        <div class="modalFooter">
            <button id="goPsnlListBtn" style="padding:5px 9px;">기초정보</button>
            <button id="goTrsListBtn" style="padding:5px 9px;">발령정보</button>
            <button id="goGrdListBtn" style="padding:5px 9px;">급호봉관리</button>
            <button id="goFmlListBtn" style="padding:5px 9px;">가족정보</button>
            <button id="goLcsListBtn" style="padding:5px 9px;">자격/면허</button>
            <button id="goInsListBtn" style="padding:5px 9px;">보증보험</button>
            <button id="goOpiListBtn" style="padding:5px 9px;">상벌정보</button>
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
                <option value="">전체</option><option>정규</option><option>계약</option><option>정규기능</option>
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
        <div class="">
            <div><span><b>빠른세팅 도우미</b></span>
            <button>정년 대상자</button>
            <button>입사1~5년</button>
            <button>입사5~10년</button>
            <button>입사10~15년</button>
            <button>입사15년 이상</button>
        </div>
    </div>
    <div class="clearB"></div>

    <br>
    <div class="tableOutFrm">
        <div class="pddS floatL">
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
            <table id="myTbl" class="width2000"></table>
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

<?php include('components/footer.php'); ?>

<script>

</script>