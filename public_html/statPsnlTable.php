<?php
include("./components/header.php");
?>
<style>
    .parish-even { background-color: #f5f5f5; }
    /*#statPsnlTableBody tr:hover { background-color: #f1f1f1; }*/
</style>
<div id="contents">
    <div class="pddS">
        <div class="rndCorner clBrC clBgW pddS" style="margin-bottom: 10px;">
            <label style="margin-right: 20px;">
                <input type="checkbox" id="shortenPos" class="filter" checked> 직책단축표시
            </label>
            <label style="margin-right: 20px;">
                <input type="checkbox" id="showExt" class="filter" checked> 내선 표시
            </label>
            <label style="margin-right: 20px;">
                <input type="checkbox" id="showTel" class="filter" checked> 국번 표시
            </label>
            <label style="margin-right: 20px;">
                <input type="checkbox" id="showBapt" class="filter" checked> 세례명 표시
            </label>
            <label style="margin-right: 20px;">
                <input type="checkbox" id="showWorkType" class="filter" checked> 고용형태 표시
            </label>
            <label style="margin-right: 20px;">
                <input type="checkbox" id="includeDomestic" class="filter"> 가사사용인 포함
            </label>
            <label style="margin-right: 20px;">
                정렬기준: 
                <select id="sortOrder" class="filter" style="padding: 4px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="POSITION">직책순</option>
                    <option value="NAME">성명순</option>
                </select>
            </label>
            <label>
                기준일: 
                <input type="date" id="statBaseDate" class="filter" value="<?php echo date('Y-m-d'); ?>" style="padding: 4px; border: 1px solid #ccc; border-radius: 4px;">
            </label>
        </div>
        
        <div id="statPsnlTableArea" class="rndCorner clBrC clBgW" style="overflow-x:auto;">
            <table id="statPsnlTable" class="hr_tbl" style="width:100%; border-collapse: collapse;font-size:13px;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">번호</th>
                        <th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">지구</th>
                        <th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">본당 및 기관명</th>
                        <th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">[내선(국번)]</th>
                        <th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">성명</th>
                        <th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">세례명</th>
                        <th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">고용형태</th>
                    </tr>
                </thead>
                <tbody id="statPsnlTableBody">
                    <tr>
                        <td colspan="7" class="txtCenter pddS">데이터를 불러오는 중입니다...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/statPsnlTable.js?ver=0.001'></script>

<?php
//footer logic if any, though header.php usually covers opening tags
?>
</body>
</html>
