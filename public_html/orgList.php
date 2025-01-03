<?php 
include('./components/header.php'); 
include "./dbconn/dbconn.php";
?>
<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader">
            <b>조직현황 모달창</b>
            <button></button>
        </div>
        <div class="modalBody">
            <div class="modalGrp">
                <div class="modalHd">조직번호</div>
                <div class="modalBd"><input autocomplete='off' placeholder="교구양업조직코드"></div>
            </div>            
            <div class="modalGrp">
                <div class="modalHd">상위조직</div>
                <div class="modalBd">
                    <input id="orgCd" style="width:calc(50% - 30px);background:#EEE" readonly autocomplete='off' placeholder="조직코드">
                    <input id="orgNm" style="width:calc(50% - 30px);" autocomplete='off' placeholder="조직명">
                    <button id="orgSerchPop" style="width:30px;padding:4px 0;">검색</button>
                </div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">조직명</div>
                <div class="modalBd"><input autocomplete='off'></div>
            </div>
            <div class="modalGrp">
                <div class="modalHd">조직타입</div>
                <div class="modalBd"><select>
                    <option value="11">본당</option>
                    <option value="1">성지</option>
                    <option value="9">지구</option>
                </select></div>
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
        조직 현황
    </h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>대리구</b></span></div>
            <div class="colBd"><select id="UUPR_ORG" class="filter">
                <option value="">전체</option><option value="13061001">제1대리구</option><option value="13062001">제2대리구</option>
            </select></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>지구</b></span></div>
            <div class="colBd"><select id="UPR_ORG" class="filter">
                <option value="">전체</option>
                <?php
                    $sql = "SELECT UPPR_ORG_CD,ORG_NM,ORG_CD FROM BONDANG_HR.ORG_INFO WHERE ORG_TYPE=9 ORDER BY UPPR_ORG_CD ASC,ORG_NM ASC";
                    $result = mysqli_query($conn,$sql);
                    mysqli_close($conn);
                    while($row = mysqli_fetch_assoc($result)){
                        echo '<option class="';
                        if($row['UPPR_ORG_CD']=='13061001'){echo 'sw1d';}else{echo 'sw2d';}
                        echo '" value="'.$row['ORG_CD'].'">'.$row['ORG_NM'].'</option>"';
                    }
                ?>
            </select></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>조직명</b></span></div>
            <div class="colBd"><input id="ORG_NM" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>조직구분</b></span></div>
            <div class="colBd"><select id="ORG_TYPE" class="filter">
                <option value="">전체</option>
                <option value="1">성지</option>
                <option value="11">본당</option>
                <option value="9">지구</option>
            </select></div>
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
<script type='text/javascript' src='/assets/js/orgList.js'></script>

<?php include('components/footer.php'); ?>

<script>

</script>