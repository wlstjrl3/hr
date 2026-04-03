<?php include('../dbconn/dbconn.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf8' />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>조직정보 조회</title>
</head>
<body>
	<div>
		<h4 class="cl3 pddS">
			조직정보 조회
		</h4>

		<div class="searchArea">
			<div class="colGrp">
				<div class="colHd clBg5 cl2"><span><b>조직명</b></span></div>
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
		
		<link href="<?php echo DIR_ROOT; ?>/assets/css/common.css?ver=1775259319" rel="stylesheet" />
		<link href="<?php echo DIR_ROOT; ?>/assets/css/hr_tbl.css?ver=1775259319" rel="stylesheet" />
		<link href="<?php echo DIR_ROOT; ?>/assets/css/searchArea.css?ver=1775259319" rel="stylesheet" />
		<script type='text/javascript'>const DIR_ROOT = opener && opener.DIR_ROOT ? opener.DIR_ROOT : '<?php echo DIR_ROOT; ?>';</script>
		<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/hr_tbl.js?ver=1775259319'></script>
		<script type='text/javascript' defer src='<?php echo DIR_ROOT; ?>/assets/js/orgPopup.js'></script>
	</div>
</body>
</html>