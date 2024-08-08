<?php include('./components/header.php'); ?>
<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader">
            <b>직원현황 모달창</b>
            <button></button>
        </div>
        <div class="modalBody">
            <div class="modalGrp">
                <div class="modalHd">일련번호</div>
                <div class="modalBd"><input readonly style="background:#EEE;" autocomplete='off'></div>
            </div>            
            <div class="modalGrp">
                <div class="modalHd">소속조직</div>
                <div class="modalBd">
                    <input id="orgCd" style="width:calc(50% - 30px);background:#EEE" readonly autocomplete='off' placeholder="조직코드">
                    <input id="orgNm" style="width:calc(50% - 30px);" autocomplete='off' placeholder="조직명">
                    <button id="orgSerchPop" style="width:30px;padding:4px 0;">검색</button>
                </div>
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
                <div class="modalHd">직책</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">고용형태</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">입사일</div>
                <div class="modalBd"><input autocomplete='off' class="dateBox"></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">퇴사일</div>
                <div class="modalBd"><input autocomplete='off' class="dateBox"></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">연락처</div>
                <div class="modalBd"><input autocomplete='off' class="phoneNumBox"></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">이메일</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">축일</div>
                <div class="modalBd"><input autocomplete='off' class="shortDateBox"></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">주민번호</div>
                <div class="modalBd"><input autocomplete='off' class="juminNumBox"></div>
            </div>
            <div style="clear:both;"></div>
        </div>
        <div class="modalFooter">
            <button id="1" style="padding:5px 9px;">인사정보</button>
            <button id="2" style="padding:5px 9px;">가족정보</button>
            <button id="3" style="padding:5px 9px;">자격정보</button>
            <button id="4" style="padding:5px 9px;">상벌정보</button>
            <button id="5" style="padding:5px 9px;">보증보험</button>
            &nbsp; - &nbsp;
            <button id="modalEdtBtn" style="padding:5px 9px;">저장</button>
            <button id="modalDelBtn" style="padding:5px 9px;">삭제</button>
        </div>
    </div>
</div>
<br><!--이 위로는 모달 팝업영역, 아래로는 페이지 코드-->
<div class="container">

    <h4 class="cl3 pddS">
        직원 현황
    </h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>소속</b></span></div>
            <div class="colBd"><input id="ORG_NM" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>성명</b></span></div>
            <div class="colBd"><input id="PSNL_NM" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>세례명</b></span></div>
            <div class="colBd"><input id="BAPT_NM" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>직책</b></span></div>
            <div class="colBd"><input id="POSITION" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>고용형태</b></span></div>
            <div class="colBd"><select id="WORK_TYPE" class="filter">
                <option value="">전체</option><option>정규직</option><option>계약직</option><option>정규기능</option><option>퇴사</option>
            </select></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>입사일</b></span></div>
            <div class="colBd"><input class="dateBox dualDateBox filter" id="JOIN_DT_From"><span>~</span><input class="dateBox dualDateBox filter" id="JOIN_DT_To"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>퇴사일</b></span></div>
            <div class="colBd"><input class="dateBox dualDateBox filter" id="QUIT_DT_From"><span>~</span><input class="dateBox dualDateBox filter" id="QUIT_DT_To"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>연락처</b></span></div>
            <div class="colBd"><input id="PHONE_NUM" class="phoneNumBox filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>이메일</b></span></div>
            <div class="colBd"><input id="PSNL_EMAIL" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>축일</b></span></div>
            <div class="colBd"><input id="FEAST" class="filter"></div>
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