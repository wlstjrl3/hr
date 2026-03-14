<?php
include('./components/header.php');
?>
<div class="container">
    <h4 class="cl3 pddS">
        고용형태 그래프
    </h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>기간</b></span></div>
            <div class="colBd">
                <input type="date" id="STT_DATE" class="filter dualDateBox" value="<?php echo date('Y-01-01'); ?>"><span>~</span><input type="date" id="END_DATE" class="filter dualDateBox" value="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>고용형태</b></span></div>
            <div class="colBd">
                <select id="WORK_TYPE_FILTER" class="filter">
                    <option value="ALL">전체</option>
                    <option value="REG">정규직</option>
                    <option value="CONT">계약직</option>
                    <option value="SHORT">단축근로</option>
                </select>
            </div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>구분</b></span></div>
            <div class="colBd">
                <select id="GROUP_BY" class="filter">
                    <option value="gender">성별</option>
                    <option value="age">연령대</option>
                </select>
            </div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>간격</b></span></div>
            <div class="colBd">
                <select id="INTERVAL" class="filter">
                    <option value="year">년별</option>
                    <option value="month" selected>월별</option>
                </select>
            </div>
        </div>
    </div>
    <div class="clearB"></div>
    <div id="dateNotice" style="display:none; color: #d9534f; background: #fff1f0; border: 1px solid #ffa39e; padding: 10px; margin-top: 10px; border-radius: 5px; font-size: 13px;">
        <strong>⚠️ 안내:</strong> 본 인사 프로그램의 데이터 입력은 <b>2023년 이후</b>부터 시작되었습니다. 2023년 이전의 인원 통계는 실제 고용 인원수와 차이가 있을 수 있으니 참고하시기 바랍니다.
    </div>

    <br>
    <div class="chartContainer" style="position: relative; height:60vh; width:100%;">
        <div id="loadingOverlay" style="display:none; position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:10; justify-content:center; align-items:center; flex-direction:column;">
            <div style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <div style="margin-top: 10px; font-weight: bold; color: #555;" id="loadingText">데이터 조회 및 처리중...</div>
        </div>
        <canvas id="myChart"></canvas>
    </div>
    <br>
</div>

<!-- 상세 내역 모달 -->
<div id="detailModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:#fff; width:400px; max-width:90%; border-radius:8px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,0.3);">
        <div style="padding:15px; background:#f8f9fa; border-bottom:1px solid #dee2e6; display:flex; justify-content:space-between; align-items:center;">
            <h5 id="modalTitle" style="margin:0; font-size:16px;">상세 내역</h5>
            <span style="cursor:pointer; font-size:20px;" onclick="document.getElementById('detailModal').style.display='none'">&times;</span>
        </div>
        <div id="modalBody" style="padding:20px; max-height:400px; overflow-y:auto;">
            <!-- 데이터가 여기에 동적으로 로드됩니다 -->
        </div>
        <div style="padding:10px; background:#f8f9fa; border-top:1px solid #dee2e6; text-align:right;">
            <button class="btn btn-secondary" style="padding:5px 15px; background:#6c757d; color:#fff; border:none; border-radius:4px; cursor:pointer;" onclick="document.getElementById('detailModal').style.display='none'">닫기</button>
        </div>
    </div>
</div>

<style>
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<link href="<?php echo DIR_ROOT; ?>/assets/css/searchArea.css?ver=0" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/statWorkTypeGraph.js'></script>

<?php include('components/footer.php'); ?>
