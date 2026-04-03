let currentData = [];

document.addEventListener("DOMContentLoaded", function() {
    // Add event listeners for filters
    document.querySelectorAll('.filter').forEach(function(el) {
        ['keyup', 'change'].forEach(function(evt) {
            el.addEventListener(evt, function(e) {
                if(e.type === 'keyup' && e.keyCode !== 13) {
                    return;
                }
                loadList(e.type === 'keyup' && e.keyCode === 13);
            });
        });
    });

    // Initial load
    loadList();
});

function loadList(isEnterTyped = false) {
    let psnlNm = document.getElementById('PSNL_NM').value;
    let fmlNm = document.getElementById('FML_NM').value;
    let orgNm = document.getElementById('ORG_NM').value;
    
    let apiUrl = './sys/tuitionList.php?key=' + API_TOKEN;
    if(psnlNm) apiUrl += '&PSNL_NM=' + encodeURIComponent(psnlNm);
    if(fmlNm) apiUrl += '&FML_NM=' + encodeURIComponent(fmlNm);
    if(orgNm) apiUrl += '&ORG_NM=' + encodeURIComponent(orgNm);

    fetch(apiUrl)
    .then(response => response.json())
    .then(res => {
        currentData = res.data || [];
        document.getElementById('totalCntTxt').innerText = currentData.length;
        drawTable();

        if (isEnterTyped && currentData.length === 0 && psnlNm.trim() !== '') {
            if (confirm(psnlNm + " 직원의 등록된 자녀 정보가 없습니다. 가족정보 관리로 이동하여 자녀를 등록하시겠습니까?")) {
                location.href = './fmlList.php?PSNL_NM=' + encodeURIComponent(psnlNm);
            }
        }
    })
    .catch(error => console.error('Error fetching list:', error));
}

function drawTable() {
    let html = '';
    if (currentData.length === 0) {
        html = '<tr><td colspan="10" class="tac">검색 결과가 없습니다. (가족정보에 자녀가 등록되어있는지 확인하세요)</td></tr>';
    } else {
        currentData.forEach((row, idx) => {
            let rowClass = (idx % 2 == 0) ? 'even' : 'odd';
            html += `<tr class="${rowClass}">
                <td class="openCol"><a class="colBtn">＋</a></td>
                <td class="tac" data-label="소속"><p class="sharp">${row.ORG_NM || '-'}</p></td>
                <td class="tac" data-label="직원명"><p class="sharp">${row.PSNL_NM}</p></td>
                <td class="tac" data-label="자녀명"><p class="sharp">${row.FML_NM}</p></td>
                <td class="tac" data-label="자녀생년월일"><p class="sharp">${row.FML_BIRTH}</p></td>
                <td class="tac" data-label="지원시작학년"><p class="sharp">${row.START_GRADE || '-'}</p></td>
                <td class="tac" data-label="지원회차"><p class="sharp">${row.SUPPORT_CNT} 회</p></td>
                <td class="tac clRed" data-label="잔여회차"><p class="sharp">${row.REMAIN_CNT} 회</p></td>
                <td class="tar" data-label="지원금 누계"><p class="sharp">${Number(row.TOTAL_AMT).toLocaleString()} 원</p></td>
                <td class="tac" data-label="관리">
                    <button type="button" class="pddS clBg2 clW rndCorner pointer fs8" onclick="openModal(${idx})">지급관리</button>
                </td>
            </tr>`;
        });
    }
    document.getElementById('listBody').innerHTML = html;
}

let activeIdx = -1;

function openModal(idx) {
    activeIdx = idx;
    let row = currentData[idx];
    
    document.getElementById('modal_FML_CD').value = row.FML_CD;
    document.getElementById('modal_PSNL_CD').value = row.PSNL_CD;
    
    document.getElementById('info_psnlNm').innerText = row.PSNL_NM;
    document.getElementById('info_fmlNm').innerText = row.FML_NM;
    document.getElementById('info_fmlBirth').innerText = row.FML_BIRTH;

    // Reset Form
    document.getElementById('add_issueDt').value = new Date().toISOString().substring(0,10);
    document.getElementById('add_issueAmt').value = '';
    document.getElementById('add_schoolGrade').value = '';
    document.getElementById('add_memo').value = '';

    drawHistory(row.ISSUE_DETAILS);
    
    let modal = document.getElementById('tuitionModal');
    modal.style.visibility = 'visible';
    modal.style.opacity = '1';
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
            if (!item.ISSUE_CD) return; // ignore Empty JSON object from group_concat if no matches
            html += `<tr>
                <td class="tac">${item.ISSUE_DT}</td>
                <td class="tar">${Number(item.ISSUE_AMT).toLocaleString()}</td>
                <td class="tac">${item.SCHOOL_GRADE}</td>
                <td class="tac">${item.MEMO}</td>
                <td class="tac">
                    <button type="button" class="btn s_btn red" onclick="deleteIssue(${item.ISSUE_CD})">삭제</button>
                </td>
            </tr>`;
        });
        if(html === '') html = '<tr><td colspan="5" class="tac">지급 내역이 없습니다.</td></tr>';
    }
    document.getElementById('historyBody').innerHTML = html;
}

function saveIssue() {
    let fmlCd = document.getElementById('modal_FML_CD').value;
    let psnlCd = document.getElementById('modal_PSNL_CD').value;
    let issueDt = document.getElementById('add_issueDt').value;
    let issueAmt = document.getElementById('add_issueAmt').value;
    let schoolGrade = document.getElementById('add_schoolGrade').value;
    let memo = document.getElementById('add_memo').value;

    if(currentData[activeIdx] && currentData[activeIdx].REMAIN_CNT <= 0) {
        if(!confirm("잔여회차가 0회입니다. 계속 진행하시겠습니까?")) return false;
    }

    let formData = new URLSearchParams();
    formData.append('key', API_TOKEN);
    formData.append('CRUD', 'C');
    formData.append('FML_CD', fmlCd);
    formData.append('PSNL_CD', psnlCd);
    formData.append('ISSUE_DT', issueDt);
    formData.append('ISSUE_AMT', issueAmt);
    formData.append('SCHOOL_GRADE', schoolGrade);
    formData.append('MEMO', memo);

    fetch('./sys/tuitionConfig.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData.toString()
    })
    .then(response => response.text())
    .then(text => {
        alert('지급내역이 추가되었습니다.');
        
        // Reload history for this employee
        let reloadUrl = './sys/tuitionList.php?key=' + API_TOKEN + '&PSNL_CD=' + psnlCd;
        fetch(reloadUrl)
        .then(res => res.json())
        .then(json => {
            loadList();
            setTimeout(() => {
                let newRow = currentData.find(r => r.FML_CD == fmlCd);
                if(newRow){
                    drawHistory(newRow.ISSUE_DETAILS);
                    activeIdx = currentData.indexOf(newRow); // update index just in case
                }
            }, 500);
        });

        // Reset input
        document.getElementById('add_issueAmt').value = '';
        document.getElementById('add_schoolGrade').value = '';
        document.getElementById('add_memo').value = '';
    })
    .catch(error => console.error('Error saving:', error));
}

function deleteIssue(issueCd) {
    if (!confirm('정말 이 지급내역을 삭제하시겠습니까?')) return;
    
    let formData = new URLSearchParams();
    formData.append('key', API_TOKEN);
    formData.append('CRUD', 'D');
    formData.append('ISSUE_CD', issueCd);

    fetch('./sys/tuitionConfig.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData.toString()
    })
    .then(response => response.text())
    .then(text => {
        let psnlCd = document.getElementById('modal_PSNL_CD').value;
        let fmlCd = document.getElementById('modal_FML_CD').value;
        alert('삭제되었습니다.');
        loadList();
        setTimeout(() => {
            let newRow = currentData.find(r => r.FML_CD == fmlCd);
            if(newRow) {
                drawHistory(newRow.ISSUE_DETAILS);
            } else {
                drawHistory([]);
            }
        }, 500);
    })
    .catch(error => console.error('Error deleting:', error));
}
