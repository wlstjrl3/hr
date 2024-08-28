<!DOCTYPE html>
<html>
<head>
    <meta charset='utf8' />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>직원정보 조회</title>
</head>
<body>
	<div>
		<h4 class="cl3 pddS">
			직원정보 조회
		</h4>

		<div class="searchArea">
			<div class="colGrp">
				<div class="colHd clBg5 cl2"><span><b>성명</b></span></div>
				<div class="colBd"><input id="PSNL_NM" class="filter"></div>
			</div>
			<div class="colGrp">
				<div class="colHd clBg5 cl2"><span><b>세례명</b></span></div>
				<div class="colBd"><input id="BAPT_NM" class="filter"></div>
			</div>
			<div class="colGrp">
				<div class="colHd clBg5 cl2"><span><b>조직</b></span></div>
				<div class="colBd"><input id="ORG_NM" class="filter"></div>
			</div>
		</div>
		<div class="clearB"></div>

		<br>
		<div class="tableOutFrm">
			<table id="myTbl"></table>
			<div id="tblPagination"></div>
		</div>
		<br>
		<div class="modalForm tblLimit" id="xport" style="hidden"></div>
		
		<link href="/assets/css/common.css?ver=0.001" rel="stylesheet" />
		<link href="/assets/css/hr_tbl.css?ver=0" rel="stylesheet" />
		<link href="/assets/css/searchArea.css?ver=0" rel="stylesheet" />
		<script type='text/javascript' src='/assets/js/hr_tbl.js'></script>
		<script type='text/javascript' defer src='/assets/js/psnlPopup.js'></script>
	</div>
</body>
</html>