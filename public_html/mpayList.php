<?php include('./components/header.php'); ?>
<br>
<div class="container">

    <h4 class="cl3 pddS">
        월별 개인급여 상세
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
            <div class="colBd"><input id="MPAY_YEAR" type="number" class="filter" placeholder="2000" value="<?php echo @$_REQUEST['MPAY_YEAR'];?>"></div>
        </div>
        <div class="clearB"></div>
    </div>
    <br>
    <div class="tableOutFrm xScroll">
        <div id="mpayTbl" class="fs7" style="width:1000px;">별도 테이블 갱신</div>
    </div>
    <br>
</div>

<link href="/assets/css/modal.css?ver=0" rel="stylesheet" />
<link href="/assets/css/searchArea.css?ver=0" rel="stylesheet" />
<link href="/assets/css/mpayList.css?ver=0" rel="stylesheet" />
<script type='text/javascript' src='/assets/js/library/xlsx.mini.min.js'></script>
<script type='text/javascript' src='/assets/js/mpayList.js'></script>

<?php include('components/footer.php'); ?>

<script>

</script>