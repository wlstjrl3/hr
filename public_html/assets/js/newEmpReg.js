/* =========================================================
   newEmpReg.js  –  통합 인사정보 관리 스크립트
   ========================================================= */

let currentPsnlCd = '';
let currentPsnlNm = '';
let currentOrgCd  = '';
let currentOrgNm  = '';
let currentPosition = '';

let trsDataList = [];
let grdDataList = [];

// ── UI 제어 (잠금 해제 등) ──
function updateLockState() {
    const isLocked = !currentPsnlCd;
    const overlays = document.querySelectorAll('.lock-overlay');
    overlays.forEach(el => {
        if (isLocked) {
            el.classList.add('on');
            el.classList.remove('off');
        } else {
            el.classList.remove('on');
            el.classList.add('off');
        }
    });

    if (currentPsnlCd) {
        document.getElementById('empBanner').style.display = 'block';
        document.getElementById('bannerCd').textContent = currentPsnlCd;
        document.getElementById('bannerNm').textContent = currentPsnlNm;
        document.getElementById('bannerOrg').textContent = currentOrgNm || '-';
        document.getElementById('bannerPos').textContent = currentPosition || '-';
        
        // 엠티 테이블 메시지 업데이트
        const trsEmpty = document.querySelector('#trsBody .tbl-empty');
        if (trsEmpty && document.getElementById('trsBody').children.length === 1) {
            trsEmpty.textContent = '등록된 발령 이력이 없습니다. 신규 발령을 등록하세요.';
        }
        const grdEmpty = document.querySelector('#grdBody .tbl-empty');
        if (grdEmpty && document.getElementById('grdBody').children.length === 1) {
            grdEmpty.textContent = '등록된 급호봉 이력이 없습니다. 신규 승급을 등록하세요.';
        }
        const adjEmpty = document.querySelector('#adjBody .tbl-empty');
        if (adjEmpty && document.getElementById('adjBody').children.length === 1) {
            adjEmpty.textContent = '등록된 제수당 내역이 없습니다. 행 추가를 눌러 등록하세요.';
        }
        const fmlEmpty = document.querySelector('#fmlBody .tbl-empty');
        if (fmlEmpty && document.getElementById('fmlBody').children.length === 1) {
            fmlEmpty.textContent = '등록된 가족 정보가 없습니다. 행 추가를 눌러 등록하세요.';
        }
    } else {
        document.getElementById('empBanner').style.display = 'none';
        document.getElementById('trsBody').innerHTML = '<tr><td colspan="5" class="tbl-empty">직원을 선택하거나 기초정보를 저장하면 입력 가능합니다</td></tr>';
        document.getElementById('grdBody').innerHTML = '<tr><td colspan="4" class="tbl-empty">직원을 선택하거나 기초정보를 저장하면 입력 가능합니다</td></tr>';
        document.getElementById('adjBody').innerHTML = '<tr><td colspan="12" class="tbl-empty">직원을 선택하거나 기초정보를 저장하면 입력 가능합니다</td></tr>';
        document.getElementById('fmlBody').innerHTML = '<tr><td colspan="10" class="tbl-empty">직원을 선택하거나 기초정보를 저장하면 입력 가능합니다</td></tr>';
    }
}

// ── 데이터 포맷 ──
function bindDateBoxes(scope) {
    (scope || document).querySelectorAll('.dateBox').forEach(el => {
        el.onkeyup = function() { this.value = autoHypenDate(this.value.trim()); };
    });
}
bindDateBoxes();

document.querySelectorAll('.phoneNumBox').forEach(el => {
    el.onkeyup = function() { this.value = autoHypenPhone(this.value.trim()); };
});
document.querySelectorAll('.juminNumBox').forEach(el => {
    el.onkeyup = function() { this.value = autoHypenJumin(this.value.trim()); };
});

// ── 뱃지 업데이트 ──
function updateBadge(id, isSaved) {
    const badge = document.getElementById(id);
    if (!badge) return;
    if (isSaved) {
        badge.textContent = '저장됨';
        badge.className = 'save-badge badge-saved';
    } else {
        badge.textContent = '미저장';
        badge.className = 'save-badge badge-unsaved';
    }
}

// ── 신규 초기화 ──
document.getElementById('newEmpBtn').addEventListener('click', () => {
    if(confirm('작성 중인 내용을 초기화하고 신규 등록 모드로 전환하시겠습니까?')) {
        currentPsnlCd = '';
        currentPsnlNm = '';
        currentOrgCd = '';
        currentOrgNm = '';
        currentPosition = '';
        trsDataList = [];
        grdDataList = [];
        
        // 모든 input, select 초기화
        document.querySelectorAll('.card-body input:not([readonly])').forEach(el => el.value = '');
        document.querySelectorAll('.card-body select').forEach(el => el.selectedIndex = 0);
        
        document.getElementById('PSNL_CD').value = '';
        document.getElementById('PSNL_NM').value = '';
        document.getElementById('ORG_NM').value = '';
        document.getElementById('POSITION').value = '';
        
        document.getElementById('p1_PSNL_CD').value = '';
        document.getElementById('orgCd').value = '';
        
        updateBadge('badge1', false);
        updateLockState();
    }
});

// ── 직원 검색 팝업 ──
document.getElementById('srchPsnlBtn').addEventListener('click', openPsnlPopup);
document.getElementById('PSNL_NM').addEventListener('keyup', e => {
    if (e.keyCode === 13) openPsnlPopup();
});
function openPsnlPopup() {
    window.open(DIR_ROOT + '/components/psnlPopup.php', '직원검색', 'width=500,height=500');
}

// 팝업 콜백
function myTblRefresh() {
    const cd = document.getElementById('PSNL_CD') ? document.getElementById('PSNL_CD').value : '';
    if (!cd) return;
    loadAllDataByPsnlCd(cd);
}

// ── 전체 데이터 로드 ──
function loadAllDataByPsnlCd(psnlCd) {
    // 1. 기초정보
    fetch(DIR_ROOT + '/sys/psnlConfig.php?key=' + API_TOKEN + '&PSNL_CD=' + psnlCd + '&CRUD=R')
        .then(r => r.json()).then(json => {
            const d = json.data && json.data[0];
            if (!d) return;
            currentPsnlCd  = d.PSNL_CD;
            currentPsnlNm  = d.PSNL_NM;
            
            document.getElementById('PSNL_CD').value = d.PSNL_CD;
            document.getElementById('PSNL_NM').value = d.PSNL_NM;
            
            document.getElementById('p1_PSNL_CD').value   = d.PSNL_CD;
            document.getElementById('p1_PSNL_NM').value   = d.PSNL_NM;
            document.getElementById('p1_BAPT_NM').value   = d.BAPT_NM || '';
            document.getElementById('p1_PHONE_NUM').value = d.PHONE_NUM || '';
            document.getElementById('p1_PSNL_NUM').value  = d.PSNL_NUM || '';
            
            updateBadge('badge1', true);
            updateLockState();
        });

    // 2. 발령정보
    fetch(DIR_ROOT + '/sys/trsList.php?key=' + API_TOKEN + '&PSNL_CD=' + psnlCd + '&ORDER=TRS_DT desc&LIMIT=0,1000')
        .then(r => r.json()).then(json => {
            trsDataList = json.data || [];
            const tbody = document.getElementById('trsBody');
            tbody.innerHTML = '';
            
            if (trsDataList.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="tbl-empty">등록된 발령 이력이 없습니다</td></tr>';
                document.getElementById('trsMoreWrap').style.display = 'none';
                
                // 최신 배너 업데이트용
                currentOrgCd = ''; currentOrgNm = ''; currentPosition = '';
                document.getElementById('ORG_NM').value = '';
                document.getElementById('POSITION').value = '';
                if(currentPsnlCd) updateLockState();
                return;
            }
            
            // 최신 발령 정보 바인딩
            const top = trsDataList[0];
            currentOrgCd    = top.ORG_CD || '';
            currentOrgNm    = top.ORG_NM || '';
            currentPosition = top.POSITION || '';
            document.getElementById('ORG_NM').value    = currentOrgNm;
            document.getElementById('POSITION').value = currentPosition;
            if(currentPsnlCd) updateLockState();

            trsDataList.forEach((r, i) => {
                const tr = document.createElement('tr');
                tr.className = 'list-row';
                if(i >= 5) tr.classList.add('hidden-row', 'trs-hidden-row');
                tr.onclick = () => openTrsModal(i);
                
                const typeMap = {"1":"입사", "2":"퇴사", "3":"전보"};
                const tName = typeMap[r.TRS_TYPE] || r.TRS_TYPE || '';
                
                tr.innerHTML = `
                    <td>${escH(r.TRS_DT||'')}</td>
                    <td>${escH(tName)}</td>
                    <td>${escH(r.ORG_NM||'')}</td>
                    <td>${escH(r.POSITION||'')}</td>
                    <td>${escH(r.WORK_TYPE||'')}</td>
                `;
                tbody.appendChild(tr);
            });
            
            if (trsDataList.length > 5) {
                document.getElementById('trsHiddenCnt').textContent = trsDataList.length - 5;
                document.getElementById('trsMoreWrap').style.display = 'block';
            } else {
                document.getElementById('trsMoreWrap').style.display = 'none';
            }
        }).catch(() => {});

    // 3. 급호봉
    fetch(DIR_ROOT + '/sys/grdList.php?key=' + API_TOKEN + '&PSNL_CD=' + psnlCd + '&ORDER=ADVANCE_DT desc&LIMIT=0,1000')
        .then(r => r.json()).then(json => {
            grdDataList = json.data || [];
            const tbody = document.getElementById('grdBody');
            tbody.innerHTML = '';
            
            if (grdDataList.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="tbl-empty">등록된 급호봉 이력이 없습니다</td></tr>';
                document.getElementById('grdMoreWrap').style.display = 'none';
                return;
            }
            
            grdDataList.forEach((r, i) => {
                const tr = document.createElement('tr');
                tr.className = 'list-row';
                if(i >= 5) tr.classList.add('hidden-row', 'grd-hidden-row');
                tr.onclick = () => openGrdModal(i);
                
                tr.innerHTML = `
                    <td>${escH(r.ADVANCE_DT||'')}</td>
                    <td>${escH(r.GRD_GRADE||'')}</td>
                    <td>${escH(r.GRD_PAY||'')}</td>
                    <td style="text-align:left;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escH(r.GRD_DTL||'')}</td>
                `;
                tbody.appendChild(tr);
            });
            
            if (grdDataList.length > 5) {
                document.getElementById('grdHiddenCnt').textContent = grdDataList.length - 5;
                document.getElementById('grdMoreWrap').style.display = 'block';
            } else {
                document.getElementById('grdMoreWrap').style.display = 'none';
            }
        }).catch(() => {});

    // 4. 제수당
    fetch(DIR_ROOT + '/sys/adjList.php?key=' + API_TOKEN + '&PSNL_CD=' + psnlCd + '&limit=0&page=0')
        .then(r => r.json()).then(json => {
            renderAdjRows(json.data || []);
        }).catch(() => {});

    // 5. 가족정보
    fetch(DIR_ROOT + '/sys/fmlList.php?key=' + API_TOKEN + '&PSNL_CD=' + psnlCd + '&limit=0&page=0')
        .then(r => r.json()).then(json => {
            renderFmlRows(json.data || []);
        }).catch(() => {});
}

// ── [더보기] 로직 ──
document.getElementById('trsMoreBtn')?.addEventListener('click', () => {
    document.querySelectorAll('.trs-hidden-row').forEach(el => el.classList.remove('hidden-row'));
    document.getElementById('trsMoreWrap').style.display = 'none';
});
document.getElementById('grdMoreBtn')?.addEventListener('click', () => {
    document.querySelectorAll('.grd-hidden-row').forEach(el => el.classList.remove('hidden-row'));
    document.getElementById('grdMoreWrap').style.display = 'none';
});

// ── ① 기초정보 저장 ──
document.getElementById('saveBasicBtn').addEventListener('click', () => {
    const nm  = document.getElementById('p1_PSNL_NM').value.trim();
    const num = document.getElementById('p1_PSNL_NUM').value.replace(/[^0-9]/g, '');
    if (nm.length < 2)    { alert('성명은 필수입니다 (2자 이상).'); return; }
    if (num.length !== 13) { alert('주민번호 13자리를 확인하세요.'); return; }
    
    // 주민번호 뒷자리 규칙 검증 (1~4: 내국인, 5~8: 외국인)
    const genderDigit = parseInt(num.charAt(6), 10);
    if (genderDigit < 1 || genderDigit > 8) {
        if (!confirm('주민번호 형식이 올바르지 않은 것 같습니다(뒷자리 첫 번호 오류). 그래도 저장하시겠습니까?')) {
            return;
        }
    }

    const cd   = document.getElementById('p1_PSNL_CD').value;
    const bapt = document.getElementById('p1_BAPT_NM').value;
    const ph   = document.getElementById('p1_PHONE_NUM').value;
    const pnum = document.getElementById('p1_PSNL_NUM').value;

    let qs = '&PSNL_CD=' + encodeURIComponent(cd)
           + '&PSNL_NM=' + encodeURIComponent(nm)
           + '&BAPT_NM=' + encodeURIComponent(bapt)
           + '&PHONE_NUM=' + encodeURIComponent(ph)
           + '&PSNL_NUM=' + encodeURIComponent(pnum)
           + '&CRUD=C';

    fetch(DIR_ROOT + '/sys/psnlConfig.php?key=' + API_TOKEN + qs)
        .then(r => r.text()).then(txt => {
            if (txt !== '') { alert('오류: ' + txt); return; }
            
            // PSNL_CD 확인
            return fetch(DIR_ROOT + '/sys/psnlList.php?key=' + API_TOKEN + '&PSNL_NUM=' + encodeURIComponent(pnum) + '&limit=1&page=0')
                .then(r2 => r2.json()).then(json2 => {
                    const row = json2.data && json2.data[0];
                    if (row) {
                        currentPsnlCd = row.PSNL_CD;
                        currentPsnlNm = row.PSNL_NM;
                        document.getElementById('p1_PSNL_CD').value   = row.PSNL_CD;
                        document.getElementById('PSNL_CD').value   = row.PSNL_CD;
                        document.getElementById('PSNL_NM').value   = row.PSNL_NM;
                        updateBadge('badge1', true);
                        updateLockState();
                        alert('기초정보가 저장되었습니다.');
                    }
                });
        }).catch(e => alert('저장 오류: ' + e.message));
});

// ── ② 발령정보 모달 제어 및 저장 ──
function openTrsModal(idx) {
    if (!currentPsnlCd) { alert('기초정보를 먼저 저장하세요.'); return; }
    if (idx === -1) {
        // 신규
        document.getElementById('p2_TRS_CD').value = '';
        document.getElementById('orgCd').value = '';
        document.getElementById('orgNm').value = '';
        document.getElementById('p2_TRS_DTL').value = '';
        document.getElementById('p2_TRS_DT').value = '';
        document.getElementById('p2_APP_DT').value = '';
        document.getElementById('p2_BNF_DT').value = '';
        document.getElementById('p2_WORK_TYPE').selectedIndex = 0;
        document.getElementById('p2_POSITION').selectedIndex = 0;
        document.getElementById('p2_TRS_TYPE').selectedIndex = 0;
        document.getElementById('delTrsBtn').style.display = 'none';
    } else {
        const d = trsDataList[idx];
        document.getElementById('p2_TRS_CD').value   = d.TRS_CD || '';
        document.getElementById('orgCd').value   = d.ORG_CD || '';
        document.getElementById('orgNm').value   = d.ORG_NM || '';
        document.getElementById('p2_TRS_DTL').value  = d.TRS_DTL || '';
        document.getElementById('p2_TRS_DT').value   = d.TRS_DT || '';
        document.getElementById('p2_APP_DT').value   = d.APP_DT || '';
        document.getElementById('p2_BNF_DT').value   = d.BNF_DT || '';
        document.getElementById('p2_WORK_TYPE').value = d.WORK_TYPE || '계약직';
        document.getElementById('p2_POSITION').value  = d.POSITION  || '사무원';
        document.getElementById('p2_TRS_TYPE').value  = d.TRS_TYPE  || '1';
        document.getElementById('delTrsBtn').style.display = 'inline-block';
    }
    document.getElementById('trsModal').classList.add('on');
}
document.getElementById('newTrsBtn').addEventListener('click', () => openTrsModal(-1));

document.getElementById('saveTrsBtn').addEventListener('click', () => {
    const orgCd = document.getElementById('orgCd').value;
    const trsDt = document.getElementById('p2_TRS_DT').value;
    if (!orgCd) { alert('시행조직은 필수입니다.'); return; }
    if (trsDt.length < 3) { alert('발령일은 필수입니다.'); return; }

    let qs = '&TRS_CD='     + encodeURIComponent(document.getElementById('p2_TRS_CD').value)
           + '&PSNL_CD='    + encodeURIComponent(currentPsnlCd)
           + '&ORG_CD='     + encodeURIComponent(orgCd)
           + '&WORK_TYPE='  + encodeURIComponent(document.getElementById('p2_WORK_TYPE').value)
           + '&POSITION='   + encodeURIComponent(document.getElementById('p2_POSITION').value)
           + '&TRS_TYPE='   + encodeURIComponent(document.getElementById('p2_TRS_TYPE').value)
           + '&TRS_DTL='    + encodeURIComponent(document.getElementById('p2_TRS_DTL').value)
           + '&TRS_DT='     + encodeURIComponent(trsDt)
           + '&APP_DT='     + encodeURIComponent(document.getElementById('p2_APP_DT').value)
           + '&BNF_DT='     + encodeURIComponent(document.getElementById('p2_BNF_DT').value)
           + '&CRUD=C';

    fetch(DIR_ROOT + '/sys/trsConfig.php?key=' + API_TOKEN + qs)
        .then(r => r.text()).then(txt => {
            if (txt !== '') { alert('오류: ' + txt); return; }
            alert('발령정보가 저장되었습니다.');
            document.getElementById('trsModal').classList.remove('on');
            loadAllDataByPsnlCd(currentPsnlCd);
        }).catch(e => alert('저장 오류: ' + e.message));
});

document.getElementById('delTrsBtn').addEventListener('click', () => {
    const cd = document.getElementById('p2_TRS_CD').value;
    if (!cd || !confirm('이 발령 이력을 삭제하시겠습니까?')) return;
    fetch(DIR_ROOT + '/sys/trsConfig.php?key=' + API_TOKEN + '&TRS_CD=' + cd + '&CRUD=D')
        .then(r => r.text()).then(txt => {
            if (txt !== '') { alert('오류: ' + txt); return; }
            alert('삭제되었습니다.');
            document.getElementById('trsModal').classList.remove('on');
            loadAllDataByPsnlCd(currentPsnlCd);
        });
});

// 조직 검색
document.getElementById('orgSerchPop').addEventListener('click', () => {
    window.open(DIR_ROOT + '/components/orgPopup.php', '조직검색', 'width=320,height=500');
});
window.setOrg = function(cd, nm) {
    document.getElementById('orgCd').value = cd;
    document.getElementById('orgNm').value = nm;
};

// ── ③ 급호봉 모달 제어 및 저장 ──
function openGrdModal(idx) {
    if (!currentPsnlCd) { alert('기초정보를 먼저 저장하세요.'); return; }
    if (idx === -1) {
        document.getElementById('p3_GRD_CD').value = '';
        document.getElementById('p3_ADVANCE_DT').value = '';
        document.getElementById('p3_GRD_GRADE').value = '';
        document.getElementById('p3_GRD_PAY').value = '';
        document.getElementById('p3_GRD_DTL').value = '';
        document.getElementById('delGrdBtn').style.display = 'none';
    } else {
        const d = grdDataList[idx];
        document.getElementById('p3_GRD_CD').value      = d.GRD_CD     || '';
        document.getElementById('p3_ADVANCE_DT').value  = d.ADVANCE_DT || '';
        document.getElementById('p3_GRD_GRADE').value   = d.GRD_GRADE  || '';
        document.getElementById('p3_GRD_PAY').value     = d.GRD_PAY    || '';
        document.getElementById('p3_GRD_DTL').value     = d.GRD_DTL    || '';
        document.getElementById('delGrdBtn').style.display = 'inline-block';
    }
    document.getElementById('grdModal').classList.add('on');
}
document.getElementById('newGrdBtn').addEventListener('click', () => openGrdModal(-1));

document.getElementById('saveGrdBtn').addEventListener('click', () => {
    const advDt = document.getElementById('p3_ADVANCE_DT').value;
    const grade = document.getElementById('p3_GRD_GRADE').value;
    const pay   = document.getElementById('p3_GRD_PAY').value;
    if (advDt.length < 3) { alert('승급변동일은 필수입니다.'); return; }
    if (!grade) { alert('급은 필수입니다.'); return; }
    if (pay === '' || pay === undefined) { alert('호는 필수입니다.'); return; }

    let qs = '&GRD_CD='      + encodeURIComponent(document.getElementById('p3_GRD_CD').value)
           + '&PSNL_CD='     + encodeURIComponent(currentPsnlCd)
           + '&ADVANCE_DT='  + encodeURIComponent(advDt)
           + '&GRD_GRADE='   + encodeURIComponent(grade)
           + '&GRD_PAY='     + encodeURIComponent(pay)
           + '&GRD_DTL='     + encodeURIComponent(document.getElementById('p3_GRD_DTL').value)
           + '&CRUD=C';

    fetch(DIR_ROOT + '/sys/grdConfig.php?key=' + API_TOKEN + qs)
        .then(r => r.text()).then(txt => {
            if (txt !== '') { alert('오류: ' + txt); return; }
            alert('급호봉 정보가 저장되었습니다.');
            document.getElementById('grdModal').classList.remove('on');
            loadAllDataByPsnlCd(currentPsnlCd);
        }).catch(e => alert('저장 오류: ' + e.message));
});

document.getElementById('delGrdBtn').addEventListener('click', () => {
    const cd = document.getElementById('p3_GRD_CD').value;
    if (!cd || !confirm('이 급호봉 이력을 삭제하시겠습니까?')) return;
    fetch(DIR_ROOT + '/sys/grdConfig.php?key=' + API_TOKEN + '&GRD_CD=' + cd + '&CRUD=D')
        .then(r => r.text()).then(txt => {
            if (txt !== '') { alert('오류: ' + txt); return; }
            alert('삭제되었습니다.');
            document.getElementById('grdModal').classList.remove('on');
            loadAllDataByPsnlCd(currentPsnlCd);
        });
});

// ── ④ 제수당 인라인 테이블 ──
let adjRowIdx = 0;

function renderAdjRows(rows) {
    const tbody = document.getElementById('adjBody');
    tbody.innerHTML = '';
    if(!rows || rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="12" class="tbl-empty">등록된 제수당 내역이 없습니다. 행 추가를 눌러 등록하세요.</td></tr>';
        return;
    }
    rows.forEach(d => addAdjRow(d));
}

function addAdjRow(d) {
    if(!currentPsnlCd) { alert('기초정보를 먼저 저장하세요'); return; }
    
    const tbody = document.getElementById('adjBody');
    if (tbody.querySelector('.tbl-empty')) tbody.innerHTML = '';

    d = d || {};
    adjRowIdx++;
    const idx = adjRowIdx;
    const tr = document.createElement('tr');
    tr.id = 'adjRow_' + idx;
    tr.innerHTML = `
        <td>${idx}</td>
        <td><input class="adjNm" value="${escH(d.ADJ_NM||'')}"></td>
        <td><select class="adjType">
            <option value="">선택</option>
            <option${d.ADJ_TYPE==='직책'?' selected':''}>직책</option>
            <option${d.ADJ_TYPE==='장애'?' selected':''}>장애</option>
            <option${d.ADJ_TYPE==='자격'?' selected':''}>자격</option>
            <option${d.ADJ_TYPE==='조정'?' selected':''}>조정</option>
        </select></td>
        <td><input class="adjNum" value="${escH(d.ADJ_NUM||'')}"></td>
        <td><input class="adjLevel" value="${escH(d.ADJ_LEVEL||'')}"></td>
        <td><input class="adjGetDt dateBox" value="${escH(d.ADJ_GET_DT||'')}"></td>
        <td><input class="adjDtl" value="${escH(d.ADJ_DTL||'')}"></td>
        <td><input type="number" class="adjPay" value="${escH(d.ADJ_PAY||'')}"></td>
        <td><input class="adjSttDt dateBox" value="${escH(d.ADJ_STT_DT||'')}"></td>
        <td><input class="adjEndDt dateBox" value="${escH(d.ADJ_END_DT||'')}"></td>
        <td><button class="btn-save-row" onclick="saveAdjRow('${escH(d.ADJ_CD||'')}','${idx}')">저장</button></td>
        <td><button class="btn-del-row" onclick="delAdjRow('${escH(d.ADJ_CD||'')}','${idx}')">삭제</button></td>`;
    tbody.appendChild(tr);
    bindDateBoxes(tr);
}

document.getElementById('addAdjBtn').addEventListener('click', () => addAdjRow({}));

window.saveAdjRow = function(adjCd, idx) {
    const tr = document.getElementById('adjRow_' + idx);
    const nm = tr.querySelector('.adjNm').value.trim();
    if (nm.length < 2) { alert('명칭은 필수입니다.'); return; }
    const sttDt = tr.querySelector('.adjSttDt').value;
    if (sttDt.length < 3) { alert('수당시작일은 필수입니다.'); return; }

    let qs = '&ADJ_CD='      + encodeURIComponent(adjCd)
           + '&PSNL_CD='     + encodeURIComponent(currentPsnlCd)
           + '&ADJ_NM='      + encodeURIComponent(nm)
           + '&ADJ_TYPE='    + encodeURIComponent(tr.querySelector('.adjType').value)
           + '&ADJ_NUM='     + encodeURIComponent(tr.querySelector('.adjNum').value)
           + '&ADJ_LEVEL='   + encodeURIComponent(tr.querySelector('.adjLevel').value)
           + '&ADJ_GET_DT='  + encodeURIComponent(tr.querySelector('.adjGetDt').value)
           + '&ADJ_DTL='     + encodeURIComponent(tr.querySelector('.adjDtl').value)
           + '&ADJ_PAY='     + encodeURIComponent(tr.querySelector('.adjPay').value)
           + '&ADJ_STT_DT='  + encodeURIComponent(sttDt)
           + '&ADJ_END_DT='  + encodeURIComponent(tr.querySelector('.adjEndDt').value)
           + '&CRUD=C';

    fetch(DIR_ROOT + '/sys/adjConfig.php?key=' + API_TOKEN + qs)
        .then(r => r.text()).then(txt => {
            if (txt !== '') { alert('오류: ' + txt); return; }
            alert('제수당 행이 저장되었습니다.');
            loadAllDataByPsnlCd(currentPsnlCd); // 갱신
        }).catch(e => alert('저장 오류: ' + e.message));
};

window.delAdjRow = function(adjCd, idx) {
    if (!confirm('이 수당 항목을 삭제하시겠습니까?')) return;
    const tr = document.getElementById('adjRow_' + idx);
    if (adjCd) {
        fetch(DIR_ROOT + '/sys/adjConfig.php?key=' + API_TOKEN + '&ADJ_CD=' + adjCd + '&CRUD=D')
            .then(() => {
                tr.remove();
                if (document.getElementById('adjBody').children.length === 0) {
                    document.getElementById('adjBody').innerHTML = '<tr><td colspan="12" class="tbl-empty">등록된 제수당 내역이 없습니다. 행 추가를 눌러 등록하세요.</td></tr>';
                }
            }).catch(e => alert('삭제 오류: ' + e.message));
    } else {
        tr.remove();
        if (document.getElementById('adjBody').children.length === 0) {
            document.getElementById('adjBody').innerHTML = '<tr><td colspan="12" class="tbl-empty">등록된 제수당 내역이 없습니다. 행 추가를 눌러 등록하세요.</td></tr>';
        }
    }
};

// ── ⑤ 가족정보 인라인 테이블 ──
let fmlRowIdx = 0;

function renderFmlRows(rows) {
    const tbody = document.getElementById('fmlBody');
    tbody.innerHTML = '';
    if(!rows || rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="tbl-empty">등록된 가족 정보가 없습니다. 행 추가를 눌러 등록하세요.</td></tr>';
        return;
    }
    rows.forEach(d => addFmlRow(d));
}

function addFmlRow(d) {
    if(!currentPsnlCd) { alert('기초정보를 먼저 저장하세요'); return; }

    const tbody = document.getElementById('fmlBody');
    if (tbody.querySelector('.tbl-empty')) tbody.innerHTML = '';

    d = d || {};
    fmlRowIdx++;
    const idx = fmlRowIdx;
    const tr = document.createElement('tr');
    tr.id = 'fmlRow_' + idx;
    tr.innerHTML = `
        <td>${idx}</td>
        <td><input class="fmlNm" value="${escH(d.FML_NM||'')}"></td>
        <td><select class="fmlRelation">
            <option${d.FML_RELATION==='자녀'?' selected':''}>자녀</option>
            <option${d.FML_RELATION==='배우자'?' selected':''}>배우자</option>
            <option${d.FML_RELATION==='부모'?' selected':''}>부모</option>
            <option${d.FML_RELATION==='형제'?' selected':''}>형제</option>
            <option${d.FML_RELATION==='조부모'?' selected':''}>조부모</option>
        </select></td>
        <td><input class="fmlBirth dateBox" value="${escH(d.FML_BIRTH||'')}"></td>
        <td><input class="fmlDtl" value="${escH(d.FML_DTL||'')}"></td>
        <td><input type="number" class="fmlPay" value="${escH(d.FML_PAY||'')}"></td>
        <td><input class="fmlSttDt dateBox" value="${escH(d.FML_STT_DT||'')}"></td>
        <td><input class="fmlEndDt dateBox" value="${escH(d.FML_END_DT||'')}"></td>
        <td><button class="btn-save-row" onclick="saveFmlRow('${escH(d.FML_CD||'')}','${idx}')">저장</button></td>
        <td><button class="btn-del-row" onclick="delFmlRow('${escH(d.FML_CD||'')}','${idx}')">삭제</button></td>`;
    tbody.appendChild(tr);
    bindDateBoxes(tr);
}

document.getElementById('addFmlBtn').addEventListener('click', () => addFmlRow({}));

window.saveFmlRow = function(fmlCd, idx) {
    const tr = document.getElementById('fmlRow_' + idx);
    const nm = tr.querySelector('.fmlNm').value.trim();
    const bth = tr.querySelector('.fmlBirth').value;
    if (nm.length < 2) { alert('가족성명은 필수입니다.'); return; }
    if (bth.length < 3) { alert('생년월일은 필수입니다.'); return; }

    let qs = '&FML_CD='       + encodeURIComponent(fmlCd)
           + '&PSNL_CD='      + encodeURIComponent(currentPsnlCd)
           + '&FML_NM='       + encodeURIComponent(nm)
           + '&FML_RELATION=' + encodeURIComponent(tr.querySelector('.fmlRelation').value)
           + '&FML_BIRTH='    + encodeURIComponent(bth)
           + '&FML_DTL='      + encodeURIComponent(tr.querySelector('.fmlDtl').value)
           + '&FML_PAY='      + encodeURIComponent(tr.querySelector('.fmlPay').value)
           + '&FML_STT_DT='   + encodeURIComponent(tr.querySelector('.fmlSttDt').value)
           + '&FML_END_DT='   + encodeURIComponent(tr.querySelector('.fmlEndDt').value)
           + '&CRUD=C';

    fetch(DIR_ROOT + '/sys/fmlConfig.php?key=' + API_TOKEN + qs)
        .then(r => r.text()).then(txt => {
            if (txt !== '') { alert('오류: ' + txt); return; }
            alert('가족정보 행이 저장되었습니다.');
            loadAllDataByPsnlCd(currentPsnlCd); // 갱신
        }).catch(e => alert('저장 오류: ' + e.message));
};

window.delFmlRow = function(fmlCd, idx) {
    if (!confirm('이 가족 항목을 삭제하시겠습니까?')) return;
    const tr = document.getElementById('fmlRow_' + idx);
    if (fmlCd) {
        fetch(DIR_ROOT + '/sys/fmlConfig.php?key=' + API_TOKEN + '&FML_CD=' + fmlCd + '&CRUD=D')
            .then(() => {
                tr.remove();
                if (document.getElementById('fmlBody').children.length === 0) {
                    document.getElementById('fmlBody').innerHTML = '<tr><td colspan="10" class="tbl-empty">등록된 가족 정보가 없습니다. 행 추가를 눌러 등록하세요.</td></tr>';
                }
            }).catch(e => alert('삭제 오류: ' + e.message));
    } else {
        tr.remove();
        if (document.getElementById('fmlBody').children.length === 0) {
            document.getElementById('fmlBody').innerHTML = '<tr><td colspan="10" class="tbl-empty">등록된 가족 정보가 없습니다. 행 추가를 눌러 등록하세요.</td></tr>';
        }
    }
};

// ── 유틸 ──
function escH(s) {
    return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// 초기화
updateLockState();
(function() {
    const params = new URLSearchParams(window.location.search);
    const pcd = params.get('PSNL_CD');
    if (pcd) {
        loadAllDataByPsnlCd(pcd);
    }
})();
