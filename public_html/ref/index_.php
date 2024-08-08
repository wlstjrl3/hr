<?php include('components/header.php'); ?>

<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader">
            <b>팀정보 수정</b>
            <button></button>
        </div>
        <div class="modalBody">
            <div class="modalGrp">
                <div class="modalHd">팀명</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">대표성명</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">전화번호</div>
                <div class="modalBd"><input autocomplete='off' oninput="oninputPhone(this)"></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">아이디</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">비밀번호</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">메모</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>        
            <div style="clear:both;"></div>
        </div>
        <div class="modalFooter">
            <button id="modalEdtBtn" style="padding:5px 9px;">수정</button>
            <button id="modalDelBtn" style="padding:5px 9px;">삭제</button>
        </div>
    </div>
</div>
<br><!--이 위로는 모달 팝업영역, 아래로는 페이지 코드-->
<div class="titleArea">
    팀정보 관리
</div>
<div class="searchArea">
    <div class="colGrp">
        <div class="colHd">팀명</div>
        <div class="colBd"><input id="filter-teamNm" class="filter"></div>
    </div>
    <div class="colGrp">
        <div class="colHd">대표성명</div>
        <div class="colBd"><input id="filter-leader" class="filter"></div>
    </div>
    <div class="colGrp">
        <div class="colHd">전화번호</div>
        <div class="colBd"><input id="filter-phoneNum" class="filter"></div>
    </div>
    <div class="colGrp">
        <div class="colHd">아이디</div>
        <div class="colBd"><input id="filter-userId" class="filter"></div>
    </div>
    <div class="colGrp">
        <div class="colHd">등록일</div>
        <div class="colBd"><input class="dualDateBox filter" id="filter-regDtFrom">~<input class="dualDateBox filter" id="filter-regDtTo"></div>
    </div>

    <div style="clear:both;"></div>
</div>
<br>
<table id="userListTable" class="uk-table uk-table-hover uk-table-striped" style="width:100%;">
</table>

<script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>

<script async type='text/javascript' src='./js/dist/datepickerLang.js'></script>
<script async type='text/javascript' src="./js/dist/xlsx.js"></script>

<script type='text/javascript' src='./js/user/user.js'></script>
<link rel="stylesheet" type="text/css" href="./css/user/user.css">
<script type='text/javascript' src='./js/modal.js'></script>
<link rel="stylesheet" type="text/css" href="./css/modal.css">

<?php include('components/footer.php'); ?>
