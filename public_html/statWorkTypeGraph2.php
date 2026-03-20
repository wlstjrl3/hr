<?php
include('./components/header.php');
?>
<div class="container">
    <h4 class="cl3 pddS">
        직군별 상세 통계 그래프
    </h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>기준일</b></span></div>
            <div class="colBd">
                <input type="date" id="BASE_DATE" class="filter" value="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>상세유형</b></span></div>
            <div class="colBd">
                <select id="GRAPH_TYPE" class="filter">
                    <option value="age">연령</option>
                    <option value="service_years">근속연수</option>
                    <option value="reg_cont_ratio">정규직/계약직 비율</option>
                    <option value="reg_grade_ratio">정규직 급수별 비율</option>
                </select>
            </div>
        </div>
    </div>
    <div class="clearB"></div>
    <div id="dateNotice" style="display:none; color: #d9534f; background: #fff1f0; border: 1px solid #ffa39e; padding: 10px; margin-top: 10px; border-radius: 5px; font-size: 13px;">
        <strong>⚠️ 안내:</strong> 본 인사 프로그램의 데이터 입력은 <b>2023년 이후</b>부터 시작되었습니다. 2023년 이전의 인원 통계는 실제 고용 인원수와 차이가 있을 수 있으니 참고하시기 바랍니다.
    </div>

    <br>
    
    <div style="display:flex; justify-content:space-between; gap:20px;">
        <div class="chartContainer" style="position: relative; height:40vh; width:50%; border:1px solid #e0e0e0; border-radius:5px; padding:10px;">
            <div style="text-align:center; font-weight:bold; padding:5px; background:#f5f5f5;">사무직</div>
            <div id="loadingOverlayOffice" style="display:none; position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:10; justify-content:center; align-items:center; flex-direction:column;">
                <div style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            </div>
            <canvas id="chartOffice"></canvas>
        </div>
        
        <div class="chartContainer" style="position: relative; height:40vh; width:50%; border:1px solid #e0e0e0; border-radius:5px; padding:10px;">
            <div style="text-align:center; font-weight:bold; padding:5px; background:#f5f5f5;">관리직</div>
            <div id="loadingOverlayManagement" style="display:none; position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:10; justify-content:center; align-items:center; flex-direction:column;">
                <div style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            </div>
            <canvas id="chartManagement"></canvas>
        </div>
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
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/statWorkTypeGraph2.js'></script>

<?php include('components/footer.php'); ?>
