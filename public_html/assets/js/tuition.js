// 데이터테이블 초기화
var mytbl = new hr_tbl({
    xhr: {
        url: './sys/tuitionList.php',
        key: API_TOKEN,
        where: {
            PSNL_NM: document.getElementById("PSNL_NM").value,
            FML_NM: document.getElementById("FML_NM").value,
            ORG_NM: document.getElementById("ORG_NM").value,
        },
        order: {
            column: '3', // 직원명 기준 정렬 기본값
            direction: 'asc',
        },
        page: 0,
        limit: 10,
    },
    columns: [
        { title: "idx", data: "FML_CD", className: "hidden" },
        { title: "소속", data: "ORG_NM", className: "tac" },
        { title: "입사일", data: "APP_DT", className: "tac" },
        { title: "직원명", data: "PSNL_NM", className: "tac" },
        { title: "자녀명", data: "FML_NM", className: "tac" },
        { title: "자녀생년월일", data: "FML_BIRTH", className: "tac" },
        { title: "지원시작학년", data: "START_GRADE", className: "tac", render: (val) => val || '-' },
        { title: "지원회차", data: "SUPPORT_CNT", className: "tac", render: (val) => val + ' 회' },
        { title: "잔여회차", data: "REMAIN_CNT", className: "tac clRed", render: (val) => val + ' 회' },
        { title: "지원금 누계", data: "TOTAL_AMT", className: "tar", render: (val) => Number(val).toLocaleString() + ' 원' }
    ],
});

mytbl.show('myTbl');
mytbl.xportBind();

// 행 클릭 시 상세 데이터 로드 및 모달 오픈
function trDataXHR(idx) {
    const url = `./sys/tuitionList.php?key=${API_TOKEN}&FML_CD=${idx}`;
    fetch(url)
    .then(response => response.json())
    .then(json => {
        if (json.data && json.data.length > 0) {
            let row = json.data[0];
            populateModal(row);
        }
    })
    .catch(error => console.error('Error fetching details:', error));
}

function populateModal(row) {
    document.getElementById('modal_FML_CD').value = row.FML_CD;
    document.getElementById('modal_PSNL_CD').value = row.PSNL_CD;
    document.getElementById('modal_ISSUE_CD').value = ''; // 초기화
    
    document.getElementById('info_psnlNm').innerText = row.PSNL_NM;
    document.getElementById('info_fmlNm').innerText = row.FML_NM;
    document.getElementById('info_fmlBirth').innerText = row.FML_BIRTH;

    // Reset Form
    resetDetailForm();

    drawHistory(row.ISSUE_DETAILS);
    
    let modal = document.getElementById('tuitionModal');
    modal.style.visibility = 'visible';
    modal.style.opacity = '1';
}

function resetDetailForm() {
    document.getElementById('modal_ISSUE_CD').value = '';
    document.getElementById('add_issueDt').value = new Date().toISOString().substring(0,10);
    document.getElementById('add_issueAmt').value = '';
    document.getElementById('add_schoolGrade').value = '';
    document.getElementById('add_memo').value = '';
    
    // 행 선택 하이라이트 제거
    document.querySelectorAll('#historyBody tr').forEach(tr => tr.style.background = '');
}

function closeModal() {
    let modal = document.getElementById('tuitionModal');
    modal.style.visibility = 'hidden';
    modal.style.opacity = '0';
}

function drawHistory(history) {
    let html = '';
    if (!history || history.length === 0) {
        html = '<tr><td colspan="5" class="tac">지급 내역이 없습니다.</td></tr>';
    } else {
        history.forEach(item => {
            if (!item.ISSUE_CD) return; 
            html += `<tr class="pointer" onclick="editDetail(this, ${JSON.stringify(item).replace(/"/g, '&quot;')})">
                <td class="tac" onclick="event.stopPropagation()"><input type="checkbox" class="hisCheck" value="${item.ISSUE_CD}"></td>
                <td class="tac">${item.ISSUE_DT}</td>
                <td class="tar">${Number(item.ISSUE_AMT).toLocaleString()}</td>
                <td class="tac">${item.SCHOOL_GRADE}</td>
                <td class="tac">${item.MEMO}</td>
            </tr>`;
        });
        if(html === '') html = '<tr><td colspan="5" class="tac">지급 내역이 없습니다.</td></tr>';
    }
    document.getElementById('historyBody').innerHTML = html;
}

// 기존 내역 클릭 시 입력 폼에 바인딩
function editDetail(tr, item) {
    document.getElementById('modal_ISSUE_CD').value = item.ISSUE_CD;
    document.getElementById('add_issueDt').value = item.ISSUE_DT;
    document.getElementById('add_issueAmt').value = item.ISSUE_AMT;
    document.getElementById('add_schoolGrade').value = item.SCHOOL_GRADE;
    document.getElementById('add_memo').value = item.MEMO;

    // 하이라이트 처리
    document.querySelectorAll('#historyBody tr').forEach(row => row.style.background = '');
    tr.style.background = '#e9f5ff';
}

function saveIssue() {
    const fmlCd = document.getElementById('modal_FML_CD').value;
    const psnlCd = document.getElementById('modal_PSNL_CD').value;
    const issueCd = document.getElementById('modal_ISSUE_CD').value;
    
    const issueDt = document.getElementById('add_issueDt').value;
    const issueAmt = document.getElementById('add_issueAmt').value;
    const schoolGrade = document.getElementById('add_schoolGrade').value;
    const memo = document.getElementById('add_memo').value;

    if(!issueDt || !issueAmt) {
        alert("지급일과 지급액은 필수입니다.");
        return;
    }

    let formData = new URLSearchParams();
    formData.append('key', API_TOKEN);
    formData.append('CRUD', issueCd ? 'U' : 'C'); // ISSUE_CD 있으면 수정, 없으면 신규
    if(issueCd) formData.append('ISSUE_CD', issueCd);
    
    formData.append('FML_CD', fmlCd);
    formData.append('PSNL_CD', psnlCd);
    formData.append('ISSUE_DT', issueDt);
    formData.append('ISSUE_AMT', issueAmt);
    formData.append('SCHOOL_GRADE', schoolGrade);
    formData.append('MEMO', memo);

    fetch('./sys/tuitionConfig.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(response => response.text())
    .then(text => {
        alert(issueCd ? '수정되었습니다.' : '지급내역이 추가되었습니다.');
        mytbl.show('myTbl'); 
        trDataXHR(fmlCd); // 모달 데이터 갱신
    })
    .catch(error => console.error('Error saving:', error));
}

// 선택 내역 일괄 삭제 기능
async function deleteBatch() {
    const checks = document.querySelectorAll('.hisCheck:checked');
    if (checks.length === 0) {
        alert('삭제할 항목을 선택해주세요.');
        return;
    }

    if (!confirm(`선택한 ${checks.length}건의 내역을 정말 삭제하시겠습니까?`)) return;

    for (let check of checks) {
        let formData = new URLSearchParams();
        formData.append('key', API_TOKEN);
        formData.append('CRUD', 'D');
        formData.append('ISSUE_CD', check.value);

        try {
            await fetch('./sys/tuitionConfig.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            });
        } catch (e) {
            console.error('Error during batch delete:', e);
        }
    }

    alert('삭제가 완료되었습니다.');
    const fmlCd = document.getElementById('modal_FML_CD').value;
    mytbl.show('myTbl');
    trDataXHR(fmlCd);
}

// 이벤트 리스너 바인딩
document.addEventListener("DOMContentLoaded", function() {
    // 하단 버튼 바인딩
    document.getElementById('modalEdtBtn').addEventListener('click', saveIssue);
    document.getElementById('modalDelBtn').addEventListener('click', deleteBatch);
    document.getElementById('modalNewBtn').addEventListener('click', resetDetailForm);

    // 전체 선택 체크박스
    document.getElementById('checkAll').addEventListener('change', function() {
        document.querySelectorAll('.hisCheck').forEach(chk => chk.checked = this.checked);
    });

    // 필터 이벤트 바인딩
    document.querySelectorAll(".filter").forEach(f => {
        ['change', 'keyup'].forEach(evt => {
            f.addEventListener(evt, (e) => {
                if(e.type === 'keyup' && e.keyCode !== 13) return;
                mytbl.hrDt.xhr.where[f.id] = f.value;
                mytbl.hrDt.xhr.page = 0;
                mytbl.show("myTbl");
            });
        });
    });
});

// 날짜 하이픈 자동 추가
if (typeof autoHypenDate === 'function') {
    document.querySelectorAll(".dateBox").forEach(dtBox => {
        dtBox.onkeyup = function (event) {
            this.value = autoHypenDate(this.value.trim());
        }
    });
}
