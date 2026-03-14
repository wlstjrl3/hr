let myChart = null;
let isLoading = false;

function initChart() {
    const ctx = document.getElementById('myChart').getContext('2d');
    myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: []
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            onClick: (e, elements) => {
                if (elements.length > 0) {
                    const firstElement = elements[0];
                    const dataIndex = firstElement.index;
                    const datasetIndex = firstElement.datasetIndex;
                    
                    const date = myChart.data.labels[dataIndex];
                    const datasetLabel = myChart.data.datasets[datasetIndex].label;
                    
                    // Find the key for the dataset (needed for backend matching)
                    let targetKey = null;
                    const rawData = myChart.data.datasets[datasetIndex].rawKey; // We'll add this when loading
                    
                    showDetailModal(date, rawData, datasetLabel);
                }
            },
            onHover: (e, elements) => {
                e.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            },
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: '고용형태별 인원 추이' }
            }
        }
    });
}

function showDetailModal(date, key, label) {
    const modal = document.getElementById('detailModal');
    const title = document.getElementById('modalTitle');
    const body = document.getElementById('modalBody');
    
    title.innerText = `${date} - ${label} 상세 내역 (직책별)`;
    body.innerHTML = '<div style="text-align:center; padding:20px;">데이터를 불러오는 중...</div>';
    modal.style.display = 'flex';

    const workType = document.getElementById('WORK_TYPE_FILTER').value;
    const groupBy = document.getElementById('GROUP_BY').value;
    const apiKey = document.getElementById('psnlKey').value;

    fetch(`${DIR_ROOT}/sys/statWorkTypeGraph.php?key=${apiKey}&MODE=detail&TARGET_DATE=${date}&TARGET_KEY=${key}&GROUP_BY=${groupBy}&WORK_TYPE=${workType}`)
        .then(res => res.json())
        .then(json => {
            let html = '<table style="width:100%; border-collapse:collapse;">';
            html += '<tr style="background:#f1f3f5;"><th style="padding:10px; border:1px solid #ddd; text-align:left;">직책</th><th style="padding:10px; border:1px solid #ddd; text-align:center; width:80px;">인원</th></tr>';
            
            const entries = Object.entries(json.data);
            if (entries.length === 0) {
                html += '<tr><td colspan="2" style="padding:20px; text-align:center; color:#888;">해당하는 인원이 없습니다.</td></tr>';
            } else {
                entries.forEach(([pos, count]) => {
                    html += `<tr>
                        <td style="padding:10px; border:1px solid #ddd;">${pos}</td>
                        <td style="padding:10px; border:1px solid #ddd; text-align:center; font-weight:bold;">${count}명</td>
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

const colorPalette = {
    male: { border: 'rgb(54, 162, 235)', bg: 'rgba(54, 162, 235, 0.2)' },
    female: { border: 'rgb(255, 99, 132)', bg: 'rgba(255, 99, 132, 0.2)' },
    age_20: { border: 'rgb(153, 102, 255)', bg: 'rgba(153, 102, 255, 0.2)' },
    age_30: { border: 'rgb(255, 159, 64)', bg: 'rgba(255, 159, 64, 0.2)' },
    age_40: { border: 'rgb(255, 205, 86)', bg: 'rgba(255, 205, 86, 0.2)' },
    age_50: { border: 'rgb(201, 203, 207)', bg: 'rgba(201, 203, 207, 0.2)' },
    age_60: { border: 'rgb(0, 0, 0)', bg: 'rgba(0, 0, 0, 0.2)' }
};

function loadData() {
    if (isLoading) return;
    isLoading = true;
    setTimeout(() => { isLoading = false; }, 200);

    const sttDate = document.getElementById('STT_DATE').value;
    const endDate = document.getElementById('END_DATE').value;
    const workType = document.getElementById('WORK_TYPE_FILTER').value;
    const interval = document.getElementById('INTERVAL').value;
    const groupBy = document.getElementById('GROUP_BY').value;
    const key = document.getElementById('psnlKey').value;

    const notice = document.getElementById('dateNotice');
    if (notice) {
        if (sttDate && parseInt(sttDate.substring(0, 4)) < 2023) notice.style.display = 'block';
        else notice.style.display = 'none';
    }

    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.style.display = 'flex';

    fetch(`${DIR_ROOT}/sys/statWorkTypeGraph.php?key=${key}&STT_DATE=${sttDate}&END_DATE=${endDate}&WORK_TYPE=${workType}&INTERVAL=${interval}&GROUP_BY=${groupBy}`)
        .then(response => response.json())
        .then(data => {
            myChart.data.labels = data.labels;
            const datasets = [];
            for (const k in data.datasets) {
                const ds = data.datasets[k];
                const color = colorPalette[k] || { border: '#ccc', bg: '#eee' };
                datasets.push({
                    label: ds.label,
                    data: ds.data,
                    rawKey: k, // Store key for detail lookup
                    borderColor: color.border,
                    backgroundColor: color.bg,
                    borderWidth: 2,
                    tension: 0.1
                });
            }
            myChart.data.datasets = datasets;
            myChart.update();
        })
        .catch(error => console.error('Error loading chart data:', error))
        .finally(() => {
            if (overlay) overlay.style.display = 'none';
        });
}

document.addEventListener('DOMContentLoaded', () => {
    initChart();
    loadData();

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
});
