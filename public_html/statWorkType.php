<?php
include('./components/header.php');
?>
<div class="container">
    <h4 class="cl3 pddS">
        고용형태현황
    </h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>대상</b></span></div>
            <div class="colBd">
                <select id="TARGET_TYPE" class="filter">
                    <option value="ALL">제1대리구</option>
                    <option value="DISTRICT">지구</option>
                    <option value="PARISH">본당</option>
                    <option value="HOLY">성지</option>
                </select>
            </div>
        </div>
        <div class="colGrp" id="districtFilterArea" style="display:none;">
            <div class="colHd clBg5 cl2"><span><b>지구선택</b></span></div>
            <div class="colBd">
                <select id="UPR_ORG" class="filter">
                    <option value="">전체</option>
                    <?php
                    $sql = "SELECT UPPR_ORG_CD, ORG_NM, ORG_CD FROM BONDANG_HR.ORG_INFO WHERE ORG_TYPE=9 AND UPPR_ORG_CD='13061001' ORDER BY ORG_NM ASC";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<option value="' . $row['ORG_CD'] . '">' . $row['ORG_NM'] . '</option>';
                    }
                    ?>
                </select>
            </div>
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
        <div class="xScroll">
            <table id="myTbl" style="width: 100%; min-width: 1000px; table-layout: fixed;"></table>
        </div>
        <div id="tblPagination"></div>
    </div>
    <br>
</div>

<link href="<?php echo DIR_ROOT; ?>/assets/css/hr_tbl.css?ver=0" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/searchArea.css?ver=0" rel="stylesheet" />
<style>
.hr_tbl th {
    white-space: normal !important;
    vertical-align: middle !important;
    word-break: keep-all;
    font-size: 13px;
    padding: 5px !important;
}
.statCol {
    width: 12%;
}
.nameCol {
    width: auto !important;
}
</style>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/hr_tbl.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/library/xlsx.mini.min.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/statWorkType.js'></script>

<?php include('components/footer.php'); ?>
