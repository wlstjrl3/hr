<?php

include('./components/header.php');

?>
<div class="container">

    <h4 class="cl3 pddS">
        본당별직원현황
    </h4>

    <div class="searchArea">
        <div class="colGrp" style="display:none;">
            <div class="colHd clBg5 cl2"><span><b>대리구</b></span></div>
            <div class="colBd"><select id="UUPR_ORG" class="filter" disabled>
                <option value="13061001" selected>제1대리구</option><option value="13062001">제2대리구</option>
            </select></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>지구</b></span></div>
            <div class="colBd"><select id="UPR_ORG" class="filter">
                <option value="">전체</option>
                <?php
$sql = "SELECT UPPR_ORG_CD,ORG_NM,ORG_CD FROM ORG_INFO WHERE ORG_TYPE=9 ORDER BY UPPR_ORG_CD ASC,ORG_NM ASC";
$result = mysqli_query($conn, $sql);
mysqli_close($conn);
while ($row = mysqli_fetch_assoc($result)) {
    echo '<option class="';
    if ($row['UPPR_ORG_CD'] == '13061001') {
        echo 'sw1d';
    }
    else {
        echo 'sw2d';
    }
    echo '" value="' . $row['ORG_CD'] . '">' . $row['ORG_NM'] . '</option>"';
}
?>
            </select></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>조직명</b></span></div>
            <div class="colBd"><input id="ORG_NM" class="filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>신자수</b></span></div>
            <div class="colBd"><input type="number" class="dualDateBox filter" id="PERSON_CNT_From" style="width:40%;"><span>~</span><input type="number" class="dualDateBox filter" id="PERSON_CNT_To" style="width:40%;"></div>
        </div>
    </div>
    <div class="clearB"></div>

    <br>
    <div class="tableOutFrm">
        <div class="pddS floatL">
            <a id="xport" class="pddS clBg3 clW rndCorner pointer">엑셀 다운로드</a>
        </div>
        <div class="pddS floatR">
            <span>페이지당</span>
            <select class="tblLimit fs7 pddSS rndCorner clBrC">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select> 
            <span>개씩 보기</span>
        </div>
        <table id="myTbl"></table>
        <div id="tblPagination"></div>
    </div>
    <br>
</div>

<link href="<?php echo DIR_ROOT; ?>/assets/css/hr_tbl.css?ver=0" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/searchArea.css?ver=0" rel="stylesheet" />
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/hr_tbl.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/library/xlsx.mini.min.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/orgHrcount.js'></script>

<?php include('components/footer.php'); ?>
