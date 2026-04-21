//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    xhr: {
        url: DIR_ROOT + '/sys/statSalaryOhis.php',
        columXHR: '',
        key: API_TOKEN,
        where: {
            FSC_YEAR: '',
            ORG_NM: '',
            ACCOUNTS: '',
            INIT: 'Y' // 초기 로딩 시 데이터 없음 처리 방지
        },
        order: {
            column: 0,
            direction: 'desc',
        },
        page: 0,
        limit: 10,
    },
    columns: [
        { title: "회계연도", data: "FSC_YEAR", className: "" }
        , { title: "조직명", data: "ORG_NM", className: "" }
        , { title: "직원수", data: "EMP_CNT", className: "txtR", render: (data, row) => {
            if (!data || data == '0') return '0';
            return `<a href="${DIR_ROOT}/psnlTotal?ORG_NM=${encodeURIComponent(row.ORG_NM)}" class="cl3 bold" style="text-decoration:underline;">${Number(data).toLocaleString()}</a>`;
        } }
        , { title: "신자수", data: "PERSON_CNT", className: "txtR", render: (data) => data ? Number(data).toLocaleString() : '0' }
        , { title: "연간 합산금액", data: "TOTAL_AMOUNT", className: "txtR", render: (data) => data ? Number(data).toLocaleString() : '0' }
        , { title: "월간 평균합산액", data: "TOTAL_AMOUNT", className: "txtR italic", render: (data) => data ? Math.round(Number(data) / 12).toLocaleString() : '0' }
        , { title: "연간 1인당 금액", data: "PER_PERSON", className: "txtR cl3 bold", render: (data) => data ? Number(data).toLocaleString() : '0' }
        , { title: "월간 1인당 금액", data: "PER_PERSON", className: "txtR cl3 bold italic", render: (data) => data ? Math.round(Number(data) / 12).toLocaleString() : '0' }
    ],
});

// 초기 실행
document.addEventListener('DOMContentLoaded', () => {
    loadAccountNames();
    mytbl.show('myTbl');
    mytbl.xportBind();
});

// 계정 항목명 로드
function loadAccountNames() {
    const url = DIR_ROOT + "/sys/statSalaryOhisConfig.php?key=" + API_TOKEN;
    fetch(url)
        .then(response => response.json())
        .then(res => {
            const container = document.getElementById('accountList');
            container.innerHTML = '';
            
            if (res.data && res.data.length > 0) {
                res.data.forEach(item => {
                    const div = document.createElement('label');
                    div.className = 'account-item';
                    
                    div.innerHTML = `
                        <input type="checkbox" name="acc_check" value="${item.ACC_NM}">
                        <span>${item.ACC_NM}</span>
                    `;
                    container.appendChild(div);
                });
                
                // 체크박스 변경 시 테이블 갱신
                container.querySelectorAll('input').forEach(input => {
                    input.addEventListener('change', updateTable);
                });
                
                // 초기 선택값 반영
                updateTable();
            } else {
                container.innerHTML = '<div class="loading-text">등록된 계정 항목이 없습니다.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading accounts:', error);
            document.getElementById('accountList').innerHTML = '<div class="loading-text">로드실패</div>';
        });
}

// 전체 선택/해제
document.getElementById('toggleAllAccounts').addEventListener('click', () => {
    const checks = document.querySelectorAll('input[name="acc_check"]');
    const allChecked = Array.from(checks).every(c => c.checked);
    checks.forEach(c => c.checked = !allChecked);
    updateTable();
});

// 검색 필터 및 체크박스 변경 시 테이블 갱신
function updateTable() {
    const selected = Array.from(document.querySelectorAll('input[name="acc_check"]:checked'))
                          .map(c => c.value);
    
    mytbl.hrDt.xhr.where.ACCOUNTS = selected.join(',');
    mytbl.hrDt.xhr.where.FSC_YEAR = document.getElementById('S_FSC_YEAR').value;
    mytbl.hrDt.xhr.where.ORG_NM = document.getElementById('S_ORG_NM').value;
    mytbl.hrDt.xhr.where.INIT = 'N';
    
    mytbl.hrDt.xhr.page = 0;
    mytbl.show("myTbl");
}

// 텍스트 필터 이벤트
document.querySelectorAll(".filter").forEach(f => {
    f.addEventListener("keyup", (e) => {
        if (e.keyCode === 13) updateTable();
    });
    f.addEventListener("change", updateTable);
});
