let myChart = null;

function initChart() {
    const ctx = document.getElementById('myChart').getContext('2d');
    myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: '남성',
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    data: [],
                    borderWidth: 2,
                    tension: 0.1
                },
                {
                    label: '여성',
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    data: [],
                    borderWidth: 2,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: '고용형태별 인원 추이'
                }
            }
        }
    });
}

let isLoading = false;
function loadData() {
    if (isLoading) return;
    isLoading = true;
    setTimeout(() => { isLoading = false; }, 200);

    const sttDate = document.getElementById('STT_DATE').value;
    const endDate = document.getElementById('END_DATE').value;
    const workType = document.getElementById('WORK_TYPE_FILTER').value;

    // 2022년 이전 데이터 안내 문구 제어
    const notice = document.getElementById('dateNotice');
    if (notice) {
        if (sttDate && parseInt(sttDate.substring(0, 4)) < 2023) {
            notice.style.display = 'block';
        } else {
            notice.style.display = 'none';
        }
    }

    const interval = document.getElementById('INTERVAL').value;
    const key = document.getElementById('psnlKey').value;

    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.style.display = 'flex';

    fetch(`${DIR_ROOT}/sys/statWorkTypeGraph.php?key=${key}&STT_DATE=${sttDate}&END_DATE=${endDate}&WORK_TYPE=${workType}&INTERVAL=${interval}`)
        .then(response => response.json())
        .then(data => {
            myChart.data.labels = data.labels;
            myChart.data.datasets[0].data = data.male;
            myChart.data.datasets[1].data = data.female;
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

    // 일자 입력 핸들링: 타이핑 중에는 조회를 피하고, 달력 선택이나 포커스 이동 시에만 조회
    document.querySelectorAll('input.filter').forEach(f => {
        let isTyping = false;
        
        f.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                f.blur();
            } else {
                isTyping = true;
            }
        });

        f.addEventListener('mousedown', () => {
            isTyping = false; // 마우스 클릭(달력 선택 등) 시에는 타이핑 중이 아닌 것으로 간주
        });

        f.addEventListener('blur', () => {
            loadData();
            isTyping = false;
        });

        f.addEventListener('change', () => {
            if (!isTyping) {
                loadData();
            }
        });
    });

    // 셀렉트 박스는 선택 즉시 조회
    document.querySelectorAll('select.filter').forEach(f => {
        f.addEventListener('change', loadData);
    });
});
