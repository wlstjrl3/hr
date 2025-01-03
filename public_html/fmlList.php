<?php include('./components/header.php'); ?>
<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader">
            <b>가족 정보 </b>
            <button></button>
        </div>
        <div class="modalBody">
            <p>직원정보 : <b>조직 직책 성명</b></p>
            <div class="modalGrp">
                <div class="modalHd">일련번호</div>
                <div class="modalBd"><input readonly style="background:#EEE;" autocomplete='off'></div>
            </div>            
            <div class="modalGrp">
                <div class="modalHd">가족성명</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">가족관계</div>
                <div class="modalBd"><select>
                    <option>자녀</option><option>배우자</option>
                    <option>부모</option><option>형제</option><option>조부모</option>
                </select></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">생년월일</div>
                <div class="modalBd"><input autocomplete='off' class="dateBox"></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">상세정보</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">가족수당(금액)</div>
                <div class="modalBd"><input type="number" autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">수당시작일</div>
                <div class="modalBd"><input autocomplete='off' class="dateBox"></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">수당종료일</div>
                <div class="modalBd"><input autocomplete='off' class="dateBox"></div>
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
        가족정보 관리
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
            <div class="colHd clBg5 cl2"><span><b>가족성명</b></span></div>
            <div class="colBd"><input id="FML_NM" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>가족관계</b></span></div>
            <div class="colBd"><select id="FML_RELATION" class="filter">
                <option value="">전체</option><option>조부모</option><option>부모</option>
                <option>배우자</option><option>형제</option><option>자녀</option>
            </select></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>생년월일</b></span></div>
            <div class="colBd"><input id="FML_BIRTH" class="filter dateBox"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>상세정보</b></span></div>
            <div class="colBd"><input id="FML_DTL" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd2L clBg5 cl2"><span><b>가족수당<br>금액범위</b></span></div>
            <div class="colBd"><input class="dualDateBox filter" id="FML_PAY_From"><span>~</span><input class="dualDateBox filter" id="FML_PAY_To"></div>
        </div>
    </div>
    <div class="colGrp">
        <div class="colHd2L clBg5 cl2"><span><b>수당지급<br>시작일</b></span></div>
        <div class="colBd"><input class="dualDateBox dateBox filter" id="FML_STT_DT_From"><span>~</span><input class="dualDateBox dateBox filter" id="FML_STT_DT_To"></div>
    </div>
    <div class="colGrp">
        <div class="colHd2L clBg5 cl2"><span><b>수당지급<br>종료일</b></span></div>
        <div class="colBd"><input class="dualDateBox dateBox filter" id="FML_END_DT_From"><span>~</span><input class="dualDateBox dateBox filter" id="FML_END_DT_To"></div>
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
<script type='text/javascript' src='/assets/js/fmlList.js'></script>
<script type='text/javascript' src='/assets/js/dateForm.js'></script>

<?php include('components/footer.php'); ?>

<script>

</script>