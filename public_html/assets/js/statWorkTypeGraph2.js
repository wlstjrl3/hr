let chartOffice = null;
let chartManagement = null;
let isLoading = false;

const barPercentagePlugin = {
    id: 'barPercentage',
    afterDatasetsDraw(chart) {
        const { ctx, data } = chart;
        ctx.save();
        data.datasets.forEach((dataset, i) => {
            const meta = chart.getDatasetMeta(i);
            const total = dataset.data.reduce((acc, curr) => acc + (parseFloat(curr) || 0), 0);
            if (total <= 0) return;

            meta.data.forEach((bar, index) => {
                const value = parseFloat(dataset.data[index]);
                if (value <= 0) return;
                
                const percentage = ((value / total) * 100).toFixed(1) + '%';
                const countLabel = `(${value}명)`;
                
                ctx.fillStyle = '#444';
                ctx.font = 'bold 10px sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                
                // Draw percentage above count
                ctx.fillText(percentage, bar.x, bar.y - 16);
                ctx.font = 'normal 9px sans-serif';
                ctx.fillText(countLabel, bar.x, bar.y - 5);
            });
        });
        ctx.restore();
    }
};

function initCharts() {
    const ctxOffice = document.getElementById('chartOffice').getContext('2d');
    chartOffice = new Chart(ctxOffice, {
        type: 'bar',
        plugins: [barPercentagePlugin],
        data: { labels: [], datasets: [] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grace: '10%' },
                x: { ticks: { font: { size: 11 } } }
            },
            onClick: (e, elements) => {
                if (elements.length > 0) {
                    const firstElement = elements[0];
                    const dataIndex = firstElement.index;
                    const datasetIndex = firstElement.datasetIndex;
                    
                    const categoryLabel = chartOffice.data.labels[dataIndex];
                    const datasetLabel = chartOffice.data.datasets[datasetIndex].label;
                    const dataset = chartOffice.data.datasets[datasetIndex];
                    const rawKey = dataset.keys ? dataset.keys[dataIndex] : dataset.key;
                    
                    showDetailModal(categoryLabel, rawKey, datasetLabel, 'office');
                }
            },
            onHover: (e, elements) => {
                e.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
            },
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: '사무직 통계' }
            }

        }
    });

    const ctxManagement = document.getElementById('chartManagement').getContext('2d');
    chartManagement = new Chart(ctxManagement, {
        type: 'bar',
        plugins: [barPercentagePlugin],
        data: { labels: [], datasets: [] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grace: '10%' },
                x: { ticks: { font: { size: 11 } } }
            },
            onClick: (e, elements) => {
                if (elements.length > 0) {
                    const firstElement = elements[0];
                    const dataIndex = firstElement.index;
                    const datasetIndex = firstElement.datasetIndex;
                    
                    const categoryLabel = chartManagement.data.labels[dataIndex];
                    const datasetLabel = chartManagement.data.datasets[datasetIndex].label;
                    const dataset = chartManagement.data.datasets[datasetIndex];
                    const rawKey = dataset.keys ? dataset.keys[dataIndex] : dataset.key;
                    
                    showDetailModal(categoryLabel, rawKey, datasetLabel, 'management');
                }
            },
            onHover: (e, elements) => {
                e.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
            },
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: '관리직 통계' }
            }

        }
    });
}

function loadData() {
    if (isLoading) return;
    isLoading = true;
    setTimeout(() => { isLoading = false; }, 200);

    const baseDate = document.getElementById('BASE_DATE').value;
    const graphType = document.getElementById('GRAPH_TYPE').value;
    const useKoreanAge = document.getElementById('USE_KOREAN_AGE') ? document.getElementById('USE_KOREAN_AGE').value : 'N';
    const key = document.getElementById('psnlKey') ? document.getElementById('psnlKey').value : '';

    const notice = document.getElementById('dateNotice');
    if (notice) {
        if (baseDate && parseInt(baseDate.substring(0, 4)) < 2023) notice.style.display = 'block';
        else notice.style.display = 'none';
    }

    const overlayO = document.getElementById('loadingOverlayOffice');
    const overlayM = document.getElementById('loadingOverlayManagement');
    if (overlayO) overlayO.style.display = 'flex';
    if (overlayM) overlayM.style.display = 'flex';

    fetch(`${DIR_ROOT}/sys/statWorkTypeGraph2.php?key=${key}&BASE_DATE=${baseDate}&GRAPH_TYPE=${graphType}&USE_KOREAN_AGE=${useKoreanAge}`)
        .then(response => response.json())
        .then(data => {
            // data format: { labels: [...], officeLabels: [...], managementLabels: [...], office: { datasets: [...] }, management: { datasets: [...] } }
            chartOffice.data.labels = data.officeLabels || data.labels || [];
            chartManagement.data.labels = data.managementLabels || data.labels || [];

            if(data.office && data.office.datasets) {
                if (data.officeTitle) {
                    chartOffice.options.plugins.title.text = data.officeTitle;
                    chartOffice.options.plugins.title.display = true;
                    // Also update the card header title
                    const el = document.getElementById('chartTitleOffice');
                    if(el) el.innerText = data.officeTitle;
                }
                chartOffice.data.datasets = data.office.datasets.map(ds => ({
                    ...ds,
                    borderWidth: 1
                }));
                chartOffice.update();
            }

            if(data.management && data.management.datasets) {
                if (data.managementTitle) {
                    chartManagement.options.plugins.title.text = data.managementTitle;
                    chartManagement.options.plugins.title.display = true;
                    // Also update the card header title
                    const el = document.getElementById('chartTitleManagement');
                    if(el) el.innerText = data.managementTitle;
                }
                chartManagement.data.datasets = data.management.datasets.map(ds => ({
                    ...ds,
                    borderWidth: 1
                }));
                chartManagement.update();
            }
        })
        .catch(error => console.error('Error loading chart data:', error))
        .finally(() => {
            if (overlayO) overlayO.style.display = 'none';
            if (overlayM) overlayM.style.display = 'none';
        });
}

document.addEventListener('DOMContentLoaded', () => {
    initCharts();
    loadData();

    document.getElementById('GRAPH_TYPE').addEventListener('change', (e) => {
        let ageOptionsArea = document.getElementById('ageOptionsArea');
        if (ageOptionsArea) ageOptionsArea.style.display = (e.target.value === 'age') ? 'block' : 'none';
        // loadData is handled by the generic class select.filter listener below
    });
    
    let ageOptionsArea = document.getElementById('ageOptionsArea');
    if (ageOptionsArea) ageOptionsArea.style.display = (document.getElementById('GRAPH_TYPE').value === 'age') ? 'block' : 'none';

    document.querySelectorAll('input.filter').forEach(f => {
        let isTyping = false;
        f.addEventListener('keydown', (e) => { if (e.key === 'Enter') f.blur(); else isTyping = true; });
        f.addEventListener('mousedown', () => { isTyping = false; });
        f.addEventListener('blur', () => { loadData(); isTyping = false; });
        f.addEventListener('change', () => { if (!isTyping) loadData(); });
    });

    document.querySelectorAll('select.filter').forEach(f => {
        f.addEventListener('change', loadData);
    });

    window.addEventListener('click', (e) => {
        const modal = document.getElementById('detailModal');
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});

function showDetailModal(categoryLabel, targetKey, label, group) {
    const modal = document.getElementById('detailModal');
    const title = document.getElementById('modalTitle');
    const body = document.getElementById('modalBody');
    
    const baseDate = document.getElementById('BASE_DATE').value;
    title.innerText = `${baseDate} ${categoryLabel} - ${label} 상세 내역 (직책별)`;
    body.innerHTML = '<div style="text-align:center; padding:20px;">데이터를 불러오는 중...</div>';
    modal.style.display = 'flex';

    const graphType = document.getElementById('GRAPH_TYPE').value;
    const useKoreanAge = document.getElementById('USE_KOREAN_AGE') ? document.getElementById('USE_KOREAN_AGE').value : 'N';
    const apiKey = document.getElementById('psnlKey').value;

    fetch(`${DIR_ROOT}/sys/statWorkTypeGraph2.php?key=${apiKey}&MODE=detail&BASE_DATE=${baseDate}&TARGET_KEY=${targetKey}&GRAPH_TYPE=${graphType}&TARGET_GROUP=${group}&USE_KOREAN_AGE=${useKoreanAge}`)
        .then(res => res.json())
        .then(json => {
            let html = '<table style="width:100%; border-collapse:collapse;">';
            html += '<tr style="background:#f1f3f5;"><th style="padding:10px; border:1px solid #ddd; text-align:left;">직책</th><th style="padding:10px; border:1px solid #ddd; text-align:center; width:80px;">인원</th></tr>';
            
            const entries = Object.entries(json.data);
            if (entries.length === 0) {
                html += '<tr><td colspan="2" style="padding:20px; text-align:center; color:#888;">해당하는 인원이 없습니다.</td></tr>';
            } else {
                entries.forEach(([pos, count]) => {
                    let url = `${DIR_ROOT}/psnlTotal?STAT_BASE_DATE=${baseDate}&POSITION=${encodeURIComponent(pos)}&TRS_TYPE=1`;
                    if (group === 'office') url += '&EXCLUDE_POS=' + encodeURIComponent('관리');
                    


                    // psnlTotal페이지는 statOrgHr나 statWorkType 등처럼 STAT_MODE 등의 처리를 받아줄 수 있도록 설계되어 있기 때문에 여기서는 단일 조건(직책)만 넘겨도 되긴 하지만
                    // 세부 필터를 다 넘기려면 STAT_MODE 1 방식이 편하거나, 지원되는 필터를 넘겨야 함 (예: GENDER, POSITION).
                    // 현재는 POSITION 만으로도 대부분 구분되며, 추가 필터링은 필요시 구현.
                    // 위 statWorkTypeGraph1 처럼 간단히 URL 파라미터만 전달.
                    
                    if (graphType === 'age') {
                        const date = baseDate;
                        const year = parseInt(date.substring(0, 4));
                        let minAge = 0, maxAge = 999;
                        if (targetKey === 'age_20_24') { minAge = 20; maxAge = 24; }
                        else if (targetKey === 'age_25_29') { minAge = 25; maxAge = 29; }
                        else if (targetKey === 'age_30_34') { minAge = 30; maxAge = 34; }
                        else if (targetKey === 'age_35_39') { minAge = 35; maxAge = 39; }
                        else if (targetKey === 'age_40_44') { minAge = 40; maxAge = 44; }
                        else if (targetKey === 'age_45_49') { minAge = 45; maxAge = 49; }
                        else if (targetKey === 'age_50_54') { minAge = 50; maxAge = 54; }
                        else if (targetKey === 'age_55_59') { minAge = 55; maxAge = 59; }
                        else if (targetKey === 'age_60_64') { minAge = 60; maxAge = 64; }
                        else if (targetKey === 'age_65_69') { minAge = 65; maxAge = 69; }
                        else if (targetKey === 'age_70') { minAge = 70; }
                        
                        url += `&AGE_MIN=${minAge}&AGE_MAX=${maxAge}&USE_KOREAN_AGE=${useKoreanAge}`;
                    } else if (graphType === 'reg_cont_ratio') {
                         if (targetKey === 'reg') url += '&WORK_TYPE=' + encodeURIComponent('정규,기능');
                         else if (targetKey === 'cont') url += '&WORK_TYPE=' + encodeURIComponent('계약');
                    } else if (graphType === 'service_years') {
                        const baseDateObj = new Date(baseDate);
                        let fromDate = null;
                        let toDate = new Date(baseDate);

                        if (targetKey === 'sy_1') {
                            fromDate = new Date(baseDateObj);
                            fromDate.setFullYear(fromDate.getFullYear() - 1);
                            fromDate.setDate(fromDate.getDate() + 1);
                        } else if (targetKey === 'sy_3') {
                            toDate = new Date(baseDateObj);
                            toDate.setFullYear(toDate.getFullYear() - 1);
                            fromDate = new Date(baseDateObj);
                            fromDate.setFullYear(fromDate.getFullYear() - 3);
                            fromDate.setDate(fromDate.getDate() + 1);
                        } else if (targetKey === 'sy_6') {
                            toDate = new Date(baseDateObj);
                            toDate.setFullYear(toDate.getFullYear() - 3);
                            fromDate = new Date(baseDateObj);
                            fromDate.setFullYear(fromDate.getFullYear() - 6);
                            fromDate.setDate(fromDate.getDate() + 1);
                        } else if (targetKey === 'sy_10') {
                            toDate = new Date(baseDateObj);
                            toDate.setFullYear(toDate.getFullYear() - 6);
                            fromDate = new Date(baseDateObj);
                            fromDate.setFullYear(fromDate.getFullYear() - 10);
                            fromDate.setDate(fromDate.getDate() + 1);
                        } else if (targetKey === 'sy_15') {
                            toDate = new Date(baseDateObj);
                            toDate.setFullYear(toDate.getFullYear() - 10);
                            fromDate = new Date(baseDateObj);
                            fromDate.setFullYear(fromDate.getFullYear() - 15);
                            fromDate.setDate(fromDate.getDate() + 1);
                        } else if (targetKey === 'sy_20') {
                            toDate = new Date(baseDateObj);
                            toDate.setFullYear(toDate.getFullYear() - 15);
                            fromDate = new Date(baseDateObj);
                            fromDate.setFullYear(fromDate.getFullYear() - 20);
                            fromDate.setDate(fromDate.getDate() + 1);
                        } else if (targetKey === 'sy_25') {
                            toDate = new Date(baseDateObj);
                            toDate.setFullYear(toDate.getFullYear() - 20);
                            fromDate = new Date(baseDateObj);
                            fromDate.setFullYear(fromDate.getFullYear() - 25);
                            fromDate.setDate(fromDate.getDate() + 1);
                        } else if (targetKey === 'sy_over') {
                            toDate = new Date(baseDateObj);
                            toDate.setFullYear(toDate.getFullYear() - 25);
                        }

                        if (fromDate) url += `&TRS_DT_From=${fromDate.toISOString().split('T')[0]}`;
                        if (toDate) url += `&TRS_DT_To=${toDate.toISOString().split('T')[0]}`;
                        url += '&USE_FIRST_TRS=Y';
                    } else if (graphType === 'reg_grade_ratio') {
                         if (targetKey.startsWith('grade_')) {
                             url += '&WORK_TYPE=' + encodeURIComponent('정규,기능');
                             if (targetKey !== 'grade_unknown') {
                                 const gradeNum = targetKey.split('_')[1];
                                 url += `&GRD_GRADE=${gradeNum}&HAS_PAY=Y`;
                             } else {
                                 url += '&GRD_PAY=EMPTY';
                             }
                         } else if (targetKey.startsWith('lv_')) {
                             url += '&WORK_TYPE=' + encodeURIComponent('계약');
                             const parts = targetKey.split('_'); // [lv, 1, 20]
                             url += `&GRD_GRADE_From=${parts[1]}&GRD_GRADE_To=${parts[2]}`;
                         }
                    }

                    html += `<tr>
                        <td style="padding:10px; border:1px solid #ddd;">${pos}</td>
                        <td style="padding:10px; border:1px solid #ddd; text-align:center; font-weight:bold;">
                            <a href="${url}" target="_blank" style="color:#007bff; text-decoration:underline;">${count}명</a>
                        </td>
                    </tr>`;
                });
                const total = entries.reduce((acc, curr) => acc + curr[1], 0);
                html += `<tr style="background:#fff9db;"><td style="padding:10px; border:1px solid #ddd; font-weight:bold; text-align:right;">총계</td><td style="padding:10px; border:1px solid #ddd; text-align:center; font-weight:bold; color:#e03131;">${total}명</td></tr>`;
            }
            html += '</table>';
            body.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            body.innerHTML = '<div style="color:red; text-align:center; padding:20px;">오류가 발생했습니다.</div>';
        });
}
