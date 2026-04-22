/**
 * statSalaryOhis.js
 * 신자수 대비 급여 통계 (개편 버전: 고정 그룹 컬럼 포함)
 */

var mytbl = new hr_tbl({
    xhr: {
        url: DIR_ROOT + '/sys/statSalaryOhis.php',
        key: API_TOKEN,
        where: {
            FSC_YEAR: '',
            ORG_NM: '',
            ACCOUNTS: '',
            ACC_TYPE: '지출',
            INIT: 'Y'
        },
        order: { column: 0, direction: 'desc' },
        page: 0,
        limit: 10,
    },
    columns: []
});

let SERVER_GROUPS = {};
let CURRENT_TYPE = '지출';

// 초기 실행
document.addEventListener('DOMContentLoaded', () => {
    initPage();
    initColumnToggle();
});

// 컬럼 가시성 관리
let COLUMN_GROUPS = [
    { title: "기본 정보", id: "grpBasic", keys: ["FSC_YEAR", "ORG_NM", "EMP_CNT", "PERSON_CNT", "TOTAL_BUDGET"], checked: true },
    { title: "그룹별 합산", id: "grpBudgetGroups", keys: [], checked: true }, // 동적으로 채워짐
    { title: "선택 항목", id: "grpSelected", keys: ["SELECTED_TOTAL"], checked: true }
];
let HIDDEN_KEYS = new Set();

function initPage() {
    const accType = document.getElementById('S_ACC_TYPE').value;
    const url = DIR_ROOT + "/sys/statSalaryOhisConfig.php?key=" + API_TOKEN + "&ACC_TYPE=" + encodeURIComponent(accType);
    
    fetch(url)
        .then(r => r.json())
        .then(res => {
            SERVER_GROUPS = res.groups;
            CURRENT_TYPE = accType;
            
            // 계정 과목 체크박스 리스트 렌더링
            renderAccountList(res.data);
            
            // 그룹 선택 버튼 렌더링
            renderGroupButtons();
            
            // 테이블 컬럼 동적 정의 및 초기화
            buildTable(accType);
        })
        .catch(err => console.error('Init failed:', err));
}

// 테이블 구축 및 표시
function buildTable(accType) {
    const groups = SERVER_GROUPS[accType] || {};
    const groupNames = Object.keys(groups);
    
    // 기본 컬럼 정의
    let columns = [
        { title: "회계연도", data: "FSC_YEAR", className: "txtC" },
        { title: "조직명", data: "ORG_NM", className: "txtL" },
        { title: "직원수", data: "EMP_CNT", className: "txtR", render: (data, row) => 
            data > 0 ? `<a href="javascript:void(0)" onclick="openEmpModal('${row.ORG_NM}')" class="cl3 bold" style="text-decoration:underline;">${Number(data).toLocaleString()}</a>` : '0'
        },
        { title: "신자수", data: "PERSON_CNT", className: "txtR", render: (data, row) => 
            data > 0 ? `<a href="javascript:void(0)" onclick="openHistoryModal('${row.ORG_NM}', '${row.FSC_YEAR}')" class="cl3 bold" style="text-decoration:underline;">${Number(data).toLocaleString()}</a>` : '0'
        },
        { title: "연간 총예산", data: "TOTAL_BUDGET", className: "txtR clBg5 bold", render: (data) => data ? Number(data).toLocaleString() : '0' }
    ];

    // 가시성 필터링 준비
    const budgetKeys = groupNames.map(gn => `G_${gn}`);
    COLUMN_GROUPS.find(g => g.id === 'grpBudgetGroups').keys = budgetKeys;

    // 각 그룹별 금액/비율 통합 컬럼 추가
    groupNames.forEach(gn => {
        const key = `G_${gn}`;
        columns.push({ 
            title: `${gn}`, 
            data: key, 
            className: "txtR", 
            render: (data, row) => {
                const total = Number(row.TOTAL_BUDGET);
                const amount = Number(row[key]) || 0;
                if (!total || !amount) return '0<br><span class="fs8 cl2 italic">(0%)</span>';
                const ratio = ((amount / total) * 100).toFixed(1);
                return `<a href="javascript:void(0)" onclick="openGroupModal('${gn}', '${row.ORG_NM}', '${row.FSC_YEAR}')" class="cl3 bold" style="text-decoration:underline;">${amount.toLocaleString()}</a><br><span class="fs8 cl2 italic">(${ratio}%)</span>`;
            }
        });
    });

    // 선택 항목 합산 컬럼 (기존 연간 합산금액) 통합
    columns.push({ 
        title: "선택과목 합산 (비율)", 
        data: "SELECTED_TOTAL", 
        className: "txtR clBg3 clW bold", 
        render: (data, row) => {
            const selectedAmount = Number(row.SELECTED_AMOUNT);
            const total = Number(row.TOTAL_BUDGET);
            if (!selectedAmount || selectedAmount == '0') return '0<br><span class="fs8 clW italic opacity-7">(0%)</span>';
            const ratio = total > 0 ? ((selectedAmount / total) * 100).toFixed(1) : '0';
            return `<a href="javascript:void(0)" onclick="openAmountModal('${row.ORG_NM}', '${row.FSC_YEAR}')" class="clW bold" style="text-decoration:underline;">${selectedAmount.toLocaleString()}</a><br><span class="fs8 clW italic opacity-7">(${ratio}%)</span>`;
        }
    });

    // 가시성 적용
    columns.forEach(col => {
        if (HIDDEN_KEYS.has(col.data)) {
            col.className = (col.className + " hidden").trim();
        }
    });

    // hr_tbl 객체 갱신
    mytbl.hrDt.columns = columns;
    mytbl.hrDt.xhr.where.ACC_TYPE = accType;
    
    // 테이블 표시
    mytbl.show('myTbl');
    mytbl.xportBind();
    
    // 테이블 너비 조정
    const visibleCount = columns.filter(c => !c.className.includes('hidden')).length;
    const tblEl = document.getElementById('myTbl');
    if (visibleCount > 8) {
        tblEl.style.width = (visibleCount * 130) + 'px';
    } else {
        tblEl.style.width = '100%';
    }
}

// 표시 항목 선택 UI 구성
function initColumnToggle() {
    const btn = document.getElementById("showCol");
    const bg = document.querySelector(".showColBg");
    const list = document.getElementById("showColList");

    if (!btn) return;

    btn.onclick = () => {
        renderColumnToggleList();
        list.style.display = "block";
        bg.style.visibility = "visible";
    };

    bg.onclick = () => {
        list.style.display = "none";
        bg.style.visibility = "hidden";
    };
}

function renderColumnToggleList() {
    const list = document.getElementById("showColList");
    if (!list) return;

    list.innerHTML = "";
    
    // 1. 기본 정보 그룹 (고정)
    const basicGroup = COLUMN_GROUPS.find(g => g.id === 'grpBasic');
    const isBasicChecked = basicGroup.keys.every(k => !HIDDEN_KEYS.has(k)) ? "checked" : "";
    list.innerHTML += `
        <div class="col-toggle-section">
            <div class="col-toggle-header">기본 정보</div>
            <div class="col-toggle-item">
                <input type="checkbox" class="showColGrpToggle" data-keys="${basicGroup.keys.join(',')}" id="basicToggle" ${isBasicChecked}/>
                <label for="basicToggle">전체 표시</label>
            </div>
        </div>
    `;

    // 2. 예산 그룹별 개별 항목
    const groups = SERVER_GROUPS[CURRENT_TYPE] || {};
    let budgetHtml = `
        <div class="col-toggle-section">
            <div class="col-toggle-header">예산 항목별 (개별)</div>
            <div class="col-toggle-grid">
    `;
    
    Object.keys(groups).forEach(gn => {
        const key = `G_${gn}`;
        const isChecked = !HIDDEN_KEYS.has(key) ? "checked" : "";
        budgetHtml += `
            <div class="col-toggle-item">
                <input type="checkbox" class="showColSingleToggle" data-key="${key}" id="toggle_${key}" ${isChecked}/>
                <label for="toggle_${key}">${gn}</label>
            </div>
        `;
    });
    
    budgetHtml += `</div></div>`;
    list.innerHTML += budgetHtml;

    // 3. 선택 항목 (고정)
    const selectedKey = "SELECTED_TOTAL";
    const isSelectedChecked = !HIDDEN_KEYS.has(selectedKey) ? "checked" : "";
    list.innerHTML += `
        <div class="col-toggle-section">
            <div class="col-toggle-header">수동 선택</div>
            <div class="col-toggle-item">
                <input type="checkbox" class="showColSingleToggle" data-key="${selectedKey}" id="toggle_selected" ${isSelectedChecked}/>
                <label for="toggle_selected">선택과목 합산</label>
            </div>
        </div>
    `;

    // 이벤트 바인딩: 그룹 토글
    list.querySelectorAll(".showColGrpToggle").forEach(chk => {
        chk.onchange = (e) => {
            const keys = e.target.dataset.keys.split(',');
            if (e.target.checked) {
                keys.forEach(k => HIDDEN_KEYS.delete(k));
            } else {
                keys.forEach(k => HIDDEN_KEYS.add(k));
            }
            buildTable(CURRENT_TYPE);
        };
    });

    // 이벤트 바인딩: 개별 항목 토글
    list.querySelectorAll(".showColSingleToggle").forEach(chk => {
        chk.onchange = (e) => {
            const key = e.target.dataset.key;
            if (e.target.checked) {
                HIDDEN_KEYS.delete(key);
            } else {
                HIDDEN_KEYS.add(key);
            }
            buildTable(CURRENT_TYPE);
        };
    });
}

// 계정 항목 체크박스 렌더링
function renderAccountList(data) {
    const container = document.getElementById('accountList');
    container.innerHTML = '';
    
    if (data && data.length > 0) {
        data.forEach(item => {
            const div = document.createElement('label');
            div.className = 'account-item';
            div.innerHTML = `
                <input type="checkbox" name="acc_check" value="${item.ACC_NM}">
                <span>${item.ACC_NM}</span>
            `;
            container.appendChild(div);
        });
        
        container.querySelectorAll('input').forEach(input => {
            input.addEventListener('change', updateTable);
        });
    } else {
        container.innerHTML = '<div class="loading-text">계정 항목이 없습니다.</div>';
    }
}

// 그룹 선택 버튼 렌더링
function renderGroupButtons() {
    const container = document.getElementById('groupList');
    if (!container) return;
    container.innerHTML = '';

    const groups = SERVER_GROUPS[CURRENT_TYPE] || {};

    Object.keys(groups).forEach(groupName => {
        const btn = document.createElement('button');
        btn.className = 'group-btn';
        btn.innerText = groupName;
        btn.onclick = () => selectGroup(groupName, btn);
        container.appendChild(btn);
    });
}

// 그룹 선택 처리 (토글 가능)
function selectGroup(groupName, btn) {
    const groups = SERVER_GROUPS[CURRENT_TYPE] || {};
    let accountsInGroup = groups[groupName];

    // '기타' 동적 계산
    if (groupName === '기타') {
        const allKnown = [];
        Object.keys(groups).forEach(gn => { if (gn !== '기타') allKnown.push(...groups[gn]); });
        const allChecks = document.querySelectorAll('input[name="acc_check"]');
        accountsInGroup = Array.from(allChecks).map(c => c.value).filter(v => !allKnown.includes(v));
    }

    if (!accountsInGroup || accountsInGroup.length === 0) {
        alert('항목이 없습니다.'); return;
    }

    const isActive = btn.classList.contains('active');
    document.querySelectorAll('.group-btn').forEach(b => b.classList.remove('active'));

    const checks = document.querySelectorAll('input[name="acc_check"]');
    if (isActive) {
        checks.forEach(c => { if (accountsInGroup.includes(c.value)) c.checked = false; });
    } else {
        btn.classList.add('active');
        checks.forEach(c => { c.checked = accountsInGroup.includes(c.value); });
    }
    updateTable();
}

// 데이터 갱신
function updateTable() {
    const selected = Array.from(document.querySelectorAll('input[name="acc_check"]:checked')).map(c => c.value);
    
    mytbl.hrDt.xhr.where.ACCOUNTS = selected.join(',');
    mytbl.hrDt.xhr.where.FSC_YEAR = document.getElementById('S_FSC_YEAR').value;
    mytbl.hrDt.xhr.where.ORG_NM = document.getElementById('S_ORG_NM').value;
    mytbl.hrDt.xhr.where.HISTORY_YEAR = document.getElementById('S_HISTORY_YEAR').value;
    mytbl.hrDt.xhr.where.ACC_TYPE = document.getElementById('S_ACC_TYPE').value;
    mytbl.hrDt.xhr.where.INIT = 'N';
    
    mytbl.hrDt.xhr.page = 0;
    mytbl.show("myTbl");
}

// UI 이벤트 바인딩
document.querySelectorAll(".filter").forEach(f => {
    f.addEventListener("keyup", (e) => { if (e.keyCode === 13) updateTable(); });
    f.addEventListener("change", updateTable);
});

document.getElementById('S_ACC_TYPE').addEventListener('change', initPage);

document.getElementById('toggleAllAccounts').addEventListener('click', () => {
    const checks = document.querySelectorAll('input[name="acc_check"]');
    const allChecked = Array.from(checks).every(c => c.checked);
    checks.forEach(c => c.checked = !allChecked);
    document.querySelectorAll('.group-btn').forEach(b => b.classList.remove('active'));
    updateTable();
});

document.getElementById('resetSelection').addEventListener('click', () => {
    document.querySelectorAll('input[name="acc_check"]').forEach(c => c.checked = false);
    document.querySelectorAll('.group-btn').forEach(b => b.classList.remove('active'));
    updateTable();
});

// --- 상세 내역 모달 ---
function openEmpModal(orgNm) { showDetailModal(`${orgNm} - 직원 명단`, 'emp', { ORG_NM: orgNm }); }
function openHistoryModal(orgNm, fscYear) { showDetailModal(`${orgNm} - 신자수 기준 정보`, 'history', { ORG_NM: orgNm, FSC_YEAR: fscYear }); }
function openAmountModal(orgNm, fscYear) {
    const selected = Array.from(document.querySelectorAll('input[name="acc_check"]:checked')).map(c => c.value);
    showDetailModal(`${orgNm} - 선택과목 합산 상세`, 'budget', { ORG_NM: orgNm, FSC_YEAR: fscYear, ACCOUNTS: selected.join(',') });
}

function openGroupModal(groupName, orgNm, fscYear) {
    const groups = SERVER_GROUPS[CURRENT_TYPE] || {};
    let accountsInGroup = groups[groupName] || [];

    // '기타' 그룹은 명시되지 않은 모든 계정 포함
    if (groupName === '기타') {
        const allKnown = [];
        Object.keys(groups).forEach(gn => { if (gn !== '기타') allKnown.push(...groups[gn]); });
        const allChecks = document.querySelectorAll('input[name="acc_check"]');
        accountsInGroup = Array.from(allChecks).map(c => c.value).filter(v => !allKnown.includes(v));
    }

    if (accountsInGroup.length === 0) {
        alert('조회할 항목이 없습니다.');
        return;
    }

    showDetailModal(`${orgNm} - [${groupName}] 상세 내역`, 'budget', { 
        ORG_NM: orgNm, 
        FSC_YEAR: fscYear, 
        ACCOUNTS: accountsInGroup.join(',') 
    });
}

function showDetailModal(title, type, params) {
    const modal = document.getElementById('statDetailModal');
    const body = document.getElementById('detailModalBody');
    const titleEl = document.getElementById('detailModalTitle');
    const actionBtn = document.getElementById('detailActionBtn');

    titleEl.innerText = title;
    body.innerHTML = '<div class="pddL txtC">데이터 로드 중...</div>';
    actionBtn.style.display = 'none';
    modal.style.visibility = 'visible';
    modal.style.opacity = '1';

    let url = `${DIR_ROOT}/sys/statSalaryOhisDetails.php?key=${API_TOKEN}&type=${type}`;
    Object.keys(params).forEach(key => { url += `&${key}=${encodeURIComponent(params[key])}`; });

    fetch(url)
        .then(r => r.json())
        .then(res => {
            if (res.error) body.innerHTML = `<div class="pddL txtC clR">${res.message}</div>`;
            else if (type === 'emp') renderEmpList(res.data, body, actionBtn, params.ORG_NM);
            else if (type === 'history') renderHistoryInfo(res.data, body);
            else if (type === 'budget') renderBudgetList(res.data, body);
        })
        .catch(err => { body.innerHTML = `<div class="pddL txtC clR">오류: ${err.message}</div>`; });
}

function renderEmpList(data, container, actionBtn, orgNm) {
    if (!data || data.length === 0) { container.innerHTML = '정보가 없습니다.'; return; }
    let html = '<table class="detail-table"><thead><tr><th>이름</th><th>코드</th></tr></thead><tbody>';
    data.forEach(item => { html += `<tr class="detail-row"><td>${item.PSNL_NM}</td><td>${item.PSNL_CD}</td></tr>`; });
    html += '</tbody></table>';
    container.innerHTML = html;
    actionBtn.style.display = 'inline-block';
    actionBtn.onclick = () => { location.href = `${DIR_ROOT}/psnlTotal?ORG_NM=${encodeURIComponent(orgNm)}`; };
}

function renderHistoryInfo(data, container) {
    const info = data && data[0] ? data[0] : null;
    if (!info) { container.innerHTML = '기록이 없습니다.'; return; }
    container.innerHTML = `<div class="pddL fs7"><p><b>기준일자:</b> ${info.OH_DT}</p><p><b>인원수:</b> ${Number(info.PERSON_CNT).toLocaleString()} 명</p></div>`;
}

function renderBudgetList(data, container) {
    if (!data || data.length === 0) { container.innerHTML = '내역이 없습니다.'; return; }
    let total = 0;
    let html = '<table class="detail-table"><thead><tr><th>계정명</th><th class="amount-cell">금액</th></tr></thead><tbody>';
    data.forEach(item => {
        html += `<tr class="detail-row"><td>${item.ACC_NM}</td><td class="amount-cell">${Number(item.AMOUNT).toLocaleString()}</td></tr>`;
        total += Number(item.AMOUNT);
    });
    html += `</tbody><tfoot><tr class="clBg5 bold"><td>합계</td><td class="amount-cell">${total.toLocaleString()}</td></tr></tfoot></table>`;
    container.innerHTML = html;
}

function closeDetailModal() {
    const modal = document.getElementById('statDetailModal');
    modal.style.opacity = '0';
    setTimeout(() => { modal.style.visibility = 'hidden'; }, 300);
}
