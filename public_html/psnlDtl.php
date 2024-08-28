<?php include('./components/header.php'); ?>
<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader">
            <b>사용자 정보 </b>
            <button></button>
        </div>
        <div class="modalBody">
            <div class="modalGrp">
                <div class="modalHd">일련번호</div>
                <div class="modalBd"><input readonly style="background:#EEE;" autocomplete='off'></div>
            </div>            
            <div class="modalGrp">
                <div class="modalHd">아이디</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">사용자성명</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">새 비밀번호</div>
                <div class="modalBd"><input type="password" placeholder="비밀번호 변경시에만 작성" autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">비밀번호 확인</div>
                <div class="modalBd"><input type="password" placeholder="비밀번호 변경시에만 작성" autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">사용자권한</div>
                <div class="modalBd"><select>
                    <option value="user">사용자</option>
                    <option value="admin">관리자</option>
                </select></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">이메일</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">직책</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">부서</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">메모</div>
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
        사용자 관리
    </h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>아이디</b></span></div>
            <div class="colBd"><input id="USER_ID" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>성명</b></span></div>
            <div class="colBd"><input id="USER_NM" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>권한</b></span></div>
            <div class="colBd"><select id="USER_AUTH" class="filter">
            <option value="">전체</option><option value="user">사용자</option><option value="admin">관리자</option>
            </select></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>이메일</b></span></div>
            <div class="colBd"><input id="EMAIL" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>직책</b></span></div>
            <div class="colBd"><input id="POSITION" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>부서</b></span></div>
            <div class="colBd"><input id="ORG_NM" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>메모</b></span></div>
            <div class="colBd"><input id="MEMO" class="filter"></div>
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
<script type='text/javascript' src='/assets/js/user.js'></script>

<?php include('components/footer.php'); ?>

<script>

</script>