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
let opiDataList = [];
let currentGrdTab = 'grd'; // 'grd' | 'ptt'
let currentTuiFmlCd = '';  // 현재 선택된 자녀 FML_CD
let tuitionDataList = [];

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
        
        const trsEmpty = document.querySelector('#trsBody .tbl-empty');
        if (trsEmpty && document.getElementById('trsBody').children.length === 1)
            trsEmpty.textContent = '등록된 발령 이력이 없습니다. 신규 발령을 등록하세요.';
        const grdEmpty = document.querySelector('#grdBody .tbl-empty');
        if (grdEmpty && document.getElementById('grdBody').children.length === 1)
            grdEmpty.textContent = '등록된 급호봉 이력이 없습니다. 신규 승급을 등록하세요.';
        const adjEmpty = document.querySelector('#adjBody .tbl-empty');
        if (adjEmpty && document.getElementById('adjBody').children.length === 1)
            adjEmpty.textContent = '등록된 제수당 내역이 없습니다. 행 추가를 눌러 등록하세요.';
        const fmlEmpty = document.querySelector('#fmlBody .tbl-empty');
        if (fmlEmpty && document.getElementById('fmlBody').children.length === 1)
            fmlEmpty.textContent = '등록된 가족 정보가 없습니다. 행 추가를 눌러 등록하세요.';
        const pttEmpty = document.querySelector('#pttBody .tbl-empty');
        if (pttEmpty && document.getElementById('pttBody').children.length === 1)
            pttEmpty.textContent = '등록된 최저임금 내역이 없습니다. 행 추가를 눌러 등록하세요.';
        const opiEmpty = document.querySelector('#opiBody .tbl-empty');
        if (opiEmpty && document.getElementById('opiBody').children.length === 1)
            opiEmpty.textContent = '등록된 평가 내역이 없습니다. 신규 등록을 눌러 등록하세요.';
    } else {
        document.getElementById('empBanner').style.display = 'none';
        document.getElementById('trsBody').innerHTML = '<tr><td colspan="5" class="tbl-empty">직원을 선택하거나 기초정보를 저장하면 입력 가능합니다</td></tr>';
        document.getElementById('grdBody').innerHTML = '<tr><td colspan="4" class="tbl-empty">직원을 선택하거나 기초정보를 저장하면 입력 가능합니다</td></tr>';
        document.getElementById('pttBody').innerHTML = '<tr><td colspan="9" class="tbl-empty">직원을 선택하거나 기초정보를 저장하면 입력 가능합니다</td></tr>';
        document.getElementById('adjBody').innerHTML = '<tr><td colspan="12" class="tbl-empty">직원을 선택하거나 기초정보를 저장하면 입력 가능합니다</td></tr>';
        document.getElementById('fmlBody').innerHTML = '<tr><td colspan="10" class="tbl-empty">직원을 선택하거나 기초정보를 저장하면 입력 가능합니다</td></tr>';
        document.getElementById('opiBody').innerHTML = '<tr><td colspan="6" class="tbl-empty">직원을 선택하거나 기초정보를 저장하면 입력 가능합니다</td></tr>';
        document.getElementById('tuiBody').innerHTML = '<tr><td colspan="6" class="tbl-empty">자녀를 선택하면 지급 이력이 표시됩니다</td></tr>';
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
        opiDataList = [];
        tuitionDataList = [];
        currentTuiFmlCd = '';
        
        // 모든 input, select 초기화
        document.querySelectorAll('.card-body input:not([readonly])').forEach(el => el.value = '');
        document.querySelectorAll('.card-body select').forEach(el => el.selectedIndex = 0);
        
        document.getElementById('PSNL_CD').value = '';
        document.getElementById('PSNL_NM').value = '';
        document.getElementById('ORG_NM').value = '';
        document.getElementById('POSITION').value = '';
        
        document.getElementById('p1_PSNL_CD').value = '';
        document.getElementById('orgCd').value = '';

        // 자녀 선택 초기화
        const sel = document.getElementById('tuiChildSelect');
        sel.innerHTML = '<option value="">— 가족정보에서 자녀를 먼저 등록하세요 —</option>';
        document.getElementById('tuiRemainBadge').style.display = 'none';
        
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
            // 가족정보 로드 후 자녀 선택 드롭다운 갱신
            setTimeout(() => refreshTuiChildSelect(), 300);
        }).catch(() => {});

    // 6. 최저임금 (PTT)
    fetch(DIR_ROOT + '/sys/pttList.php?key=' + API_TOKEN + '&PSNL_CD=' + psnlCd + '&LIMIT=0,1000')
        .then(r => r.json()).then(json => {
            renderPttRows(json.data || []);
        }).catch(() => {});

    // 7. 상벌/직무평가 (OPI)
    fetch(DIR_ROOT + '/sys/opiList.php?key=' + API_TOKEN + '&PSNL_CD=' + psnlCd + '&ORDER=OPI_DT desc&LIMIT=0,1000')
        .then(r => r.json()).then(json => {
            opiDataList = json.data || [];
            renderOpiRows(opiDataList);
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

        // 입사(Hire) 기록을 찾아 기본값으로 세팅
        const hireRecord = [...trsDataList].reverse().find(r => String(r.TRS_TYPE) === '1');
        if (hireRecord) {
            document.getElementById('orgCd').value = hireRecord.ORG_CD || '';
            document.getElementById('orgNm').value = hireRecord.ORG_NM || '';
            document.getElementById('p2_WORK_TYPE').value = hireRecord.WORK_TYPE || '계약직';
            document.getElementById('p2_POSITION').value = hireRecord.POSITION || '사무원';
            document.getElementById('p2_TRS_TYPE').value = '2'; // 이 조직은 입사 후 다음 발령으로 퇴사가 더 빈번하므로 2(퇴사) 세팅
        } else {
            document.getElementById('orgCd').value = '';
            document.getElementById('orgNm').value = '';
            document.getElementById('p2_WORK_TYPE').selectedIndex = 0;
            document.getElementById('p2_POSITION').selectedIndex = 0;
            document.getElementById('p2_TRS_TYPE').selectedIndex = 0;
        }

        document.getElementById('p2_TRS_DTL').value = '';
        document.getElementById('p2_TRS_DT').value = '';
        document.getElementById('p2_APP_DT').value = '';
        document.getElementById('p2_BNF_DT').value = '';
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
document.getElementById('orgNm').addEventListener('keyup', e => {
    if (e.keyCode === 13) document.getElementById('orgSerchPop').click();
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
    const rel = tr.querySelector('.fmlRelation').value;
    if (nm.length < 2) { alert('가족성명은 필수입니다.'); return; }
    if (bth.length < 3) { alert('생년월일은 필수입니다.'); return; }

    // [추가] 가족수당 자동 계산 로직 (자녀, 20세 미만)
    if (rel === '자녀' && bth.length >= 10) {
        const birthYear = parseInt(bth.substring(0, 4));
        if (!isNaN(birthYear)) {
            const today = new Date();
            const limitDate = new Date(birthYear + 19, 11, 31); // 19세 되는 해의 12월 31일
            
            if (today <= limitDate) {
                const payVal = tr.querySelector('.fmlPay').value;
                const sttVal = tr.querySelector('.fmlSttDt').value;
                
                if (!payVal || !sttVal) {
                    if (confirm('가족관계가 자녀이고 20세 미만입니다.\n가족수당 2만원을 자동 설정하시겠습니까?\n(지급기간: 입사일 ~ ' + (birthYear + 19) + '-12-31)')) {
                        // 입사일 찾기 (최초 입사 발령일)
                        // TRS_TYPE이 숫자 1이거나 문자 '1'일 수 있으므로 String으로 변환하여 체크
                        const hireRecord = [...trsDataList].reverse().find(r => String(r.TRS_TYPE) === '1');
                        const hireDate = hireRecord ? hireRecord.TRS_DT : '';
                        
                        if (hireDate) {
                            tr.querySelector('.fmlPay').value = 20000;
                            tr.querySelector('.fmlSttDt').value = hireDate;
                            tr.querySelector('.fmlEndDt').value = (birthYear + 19) + '-12-31';
                        } else {
                            alert('입사일 정보를 찾을 수 없습니다. 발령정보에서 입사 기록을 먼저 등록해주세요.');
                        }
                    }
                }
            }
        }
    }

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

// ── ③ 탭 전환 (급호봉 ↔ 최저임금) ──
window.switchGrdTab = function(tab) {
    currentGrdTab = tab;
    document.getElementById('panelGrd').classList.toggle('on', tab === 'grd');
    document.getElementById('panelPtt').classList.toggle('on', tab === 'ptt');
    document.getElementById('tabGrd').classList.toggle('active', tab === 'grd');
    document.getElementById('tabPtt').classList.toggle('active', tab === 'ptt');
    document.getElementById('newGrdBtn').style.display  = tab === 'grd' ? '' : 'none';
    document.getElementById('addPttBtn').style.display  = tab === 'ptt' ? '' : 'none';
};

// ── ③ 최저임금 (PTT) 인라인 테이블 ──
let pttRowIdx = 0;

function renderPttRows(rows) {
    const tbody = document.getElementById('pttBody');
    tbody.innerHTML = '';
    if (!rows || rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="tbl-empty">등록된 최저임금 내역이 없습니다. 행 추가를 눌러 등록하세요.</td></tr>';
        return;
    }
    rows.forEach(d => addPttRow(d));
}

function addPttRow(d) {
    if (!currentPsnlCd) { alert('기초정보를 먼저 저장하세요'); return; }
    const tbody = document.getElementById('pttBody');
    if (tbody.querySelector('.tbl-empty')) tbody.innerHTML = '';
    d = d || {};
    pttRowIdx++;
    const idx = pttRowIdx;
    const tr = document.createElement('tr');
    tr.id = 'pttRow_' + idx;
    tr.innerHTML = `
        <td>${idx}</td>
        <td><input class="pttYear" value="${escH(d.PTT_YEAR||'')}" placeholder="2024" maxlength="4"></td>
        <td><input type="number" class="pttDay" value="${escH(d.PTT_DAY||'')}" placeholder="5"></td>
        <td><input type="number" class="pttHour" value="${escH(d.PTT_HOUR||'')}" placeholder="40"></td>
        <td><input type="number" class="pttAddHour" value="${escH(d.PTT_ADDHOUR||'')}" placeholder="0"></td>
        <td><input class="pttAdj" value="${escH(d.PTT_ADJ||'')}" placeholder="추가사유"></td>
        <td><input type="number" class="pttAdjPay" value="${escH(d.PTT_ADJPAY||'')}" placeholder="0"></td>
        <td><button class="btn-save-row" onclick="savePttRow('${escH(d.PTT_CD||'')}','${idx}')">저장</button></td>
        <td><button class="btn-del-row" onclick="delPttRow('${escH(d.PTT_CD||'')}','${idx}')">삭제</button></td>`;
    tbody.appendChild(tr);
}

document.getElementById('addPttBtn').addEventListener('click', () => {
    if (!currentPsnlCd) { alert('기초정보를 먼저 저장하세요'); return; }
    switchGrdTab('ptt');
    addPttRow({});
});

window.savePttRow = function(pttCd, idx) {
    const tr = document.getElementById('pttRow_' + idx);
    const year = tr.querySelector('.pttYear').value.trim();
    const day  = tr.querySelector('.pttDay').value.trim();
    const hour = tr.querySelector('.pttHour').value.trim();
    if (year.length < 4) { alert('기준년도 4자리를 입력하세요.'); return; }
    if (!day)  { alert('주근무일수는 필수입니다.'); return; }
    if (!hour) { alert('주근무시간은 필수입니다.'); return; }
    if (parseFloat(hour) > 80) { alert('최대 근무시간(80)을 초과하였습니다.'); return; }

    let qs = '&PTT_CD='      + encodeURIComponent(pttCd)
           + '&PSNL_CD='     + encodeURIComponent(currentPsnlCd)
           + '&PTT_YEAR='    + encodeURIComponent(year)
           + '&PTT_DAY='     + encodeURIComponent(day)
           + '&PTT_HOUR='    + encodeURIComponent(hour)
           + '&PTT_ADDHOUR=' + encodeURIComponent(tr.querySelector('.pttAddHour').value)
           + '&PTT_ADJ='     + encodeURIComponent(tr.querySelector('.pttAdj').value)
           + '&PTT_ADJPAY='  + encodeURIComponent(tr.querySelector('.pttAdjPay').value)
           + '&CRUD=C';

    fetch(DIR_ROOT + '/sys/pttConfig.php?key=' + API_TOKEN + qs)
        .then(r => r.text()).then(txt => {
            alert('최저임금 정보가 저장되었습니다.');
            loadAllDataByPsnlCd(currentPsnlCd);
            setTimeout(() => switchGrdTab('ptt'), 100);
        }).catch(e => alert('저장 오류: ' + e.message));
};

window.delPttRow = function(pttCd, idx) {
    if (!confirm('이 최저임금 항목을 삭제하시겠습니까?')) return;
    const tr = document.getElementById('pttRow_' + idx);
    if (pttCd) {
        fetch(DIR_ROOT + '/sys/pttConfig.php?key=' + API_TOKEN + '&PTT_CD=' + pttCd + '&CRUD=D')
            .then(() => {
                tr.remove();
                if (document.getElementById('pttBody').children.length === 0) {
                    document.getElementById('pttBody').innerHTML = '<tr><td colspan="9" class="tbl-empty">등록된 최저임금 내역이 없습니다.</td></tr>';
                }
            }).catch(e => alert('삭제 오류: ' + e.message));
    } else {
        tr.remove();
        if (document.getElementById('pttBody').children.length === 0) {
            document.getElementById('pttBody').innerHTML = '<tr><td colspan="9" class="tbl-empty">등록된 최저임금 내역이 없습니다.</td></tr>';
        }
    }
};

// ── ⑥ 상벌/직무평가 (OPI) 모달 방식 ──
const OPI_TYPE_MAP = {'1':'긍정','2':'부정','3':'포상','4':'징계'};

function renderOpiRows(rows) {
    const tbody = document.getElementById('opiBody');
    tbody.innerHTML = '';
    if (!rows || rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="tbl-empty">등록된 평가 내역이 없습니다. 신규 등록을 눌러 등록하세요.</td></tr>';
        document.getElementById('opiMoreWrap').style.display = 'none';
        return;
    }
    rows.forEach((r, i) => {
        const tr = document.createElement('tr');
        tr.className = 'list-row';
        if (i >= 5) tr.classList.add('hidden-row', 'opi-hidden-row');
        const dtlShort = (r.OPI_DTL || '').replace(/<br>/g,' ').substring(0, 40);
        tr.innerHTML = `
            <td>${escH(r.OPI_DT||'')}</td>
            <td>${escH(r.OPI_PERSON||'')}</td>
            <td>${escH(OPI_TYPE_MAP[r.OPI_TYPE]||r.OPI_TYPE||'')}</td>
            <td style="text-align:left;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escH(dtlShort)}</td>
            <td><button class="btn-save-row" onclick="openOpiModal(${i})">수정</button></td>
            <td><button class="btn-del-row" onclick="deleteOpi('${escH(r.OPI_CD||'')}')">삭제</button></td>`;
        tbody.appendChild(tr);
    });
    if (rows.length > 5) {
        document.getElementById('opiHiddenCnt').textContent = rows.length - 5;
        document.getElementById('opiMoreWrap').style.display = 'block';
    } else {
        document.getElementById('opiMoreWrap').style.display = 'none';
    }
}

document.getElementById('opiMoreBtn')?.addEventListener('click', () => {
    document.querySelectorAll('.opi-hidden-row').forEach(el => el.classList.remove('hidden-row'));
    document.getElementById('opiMoreWrap').style.display = 'none';
});

window.openOpiModal = function(idx) {
    if (!currentPsnlCd) { alert('기초정보를 먼저 저장하세요.'); return; }
    if (idx === -1) {
        document.getElementById('p6_OPI_CD').value     = '';
        document.getElementById('p6_OPI_TYPE').value   = '1';
        document.getElementById('p6_OPI_DT').value     = '';
        document.getElementById('p6_OPI_PERSON').value = '';
        document.getElementById('p6_OPI_DTL').value    = '';
        document.getElementById('delOpiBtn').style.display = 'none';
    } else {
        const d = opiDataList[idx];
        document.getElementById('p6_OPI_CD').value     = d.OPI_CD     || '';
        document.getElementById('p6_OPI_TYPE').value   = d.OPI_TYPE   || '1';
        document.getElementById('p6_OPI_DT').value     = d.OPI_DT     || '';
        document.getElementById('p6_OPI_PERSON').value = d.OPI_PERSON || '';
        document.getElementById('p6_OPI_DTL').value    = (d.OPI_DTL||'').replace(/<br>/g,'\n');
        document.getElementById('delOpiBtn').style.display = 'inline-block';
    }
    bindDateBoxes(document.getElementById('opiModal'));
    document.getElementById('opiModal').classList.add('on');
};

document.getElementById('newOpiBtn').addEventListener('click', () => openOpiModal(-1));

document.getElementById('saveOpiBtn').addEventListener('click', () => {
    const opiDt     = document.getElementById('p6_OPI_DT').value;
    const opiPerson = document.getElementById('p6_OPI_PERSON').value.trim();
    if (opiDt.length < 3)     { alert('평가일시는 필수입니다.'); return; }
    if (opiPerson.length < 2) { alert('평가자는 필수입니다.'); return; }

    const opiDtl = document.getElementById('p6_OPI_DTL').value.replace(/(?:\r\n|\r|\n)/g,'<br>');
    let qs = '&OPI_CD='     + encodeURIComponent(document.getElementById('p6_OPI_CD').value)
           + '&PSNL_CD='    + encodeURIComponent(currentPsnlCd)
           + '&OPI_TYPE='   + encodeURIComponent(document.getElementById('p6_OPI_TYPE').value)
           + '&OPI_DT='     + encodeURIComponent(opiDt)
           + '&OPI_PERSON=' + encodeURIComponent(opiPerson)
           + '&OPI_DTL='    + encodeURIComponent(opiDtl)
           + '&CRUD=C';

    fetch(DIR_ROOT + '/sys/opiConfig.php?key=' + API_TOKEN + qs)
        .then(r => r.text()).then(txt => {
            if (txt !== '') { alert('오류: ' + txt); return; }
            alert('평가 정보가 저장되었습니다.');
            document.getElementById('opiModal').classList.remove('on');
            loadAllDataByPsnlCd(currentPsnlCd);
        }).catch(e => alert('저장 오류: ' + e.message));
});

document.getElementById('delOpiBtn').addEventListener('click', () => {
    const cd = document.getElementById('p6_OPI_CD').value;
    if (!cd || !confirm('이 평가 이력을 삭제하시겠습니까?')) return;
    fetch(DIR_ROOT + '/sys/opiConfig.php?key=' + API_TOKEN + '&OPI_CD=' + cd + '&CRUD=D')
        .then(r => r.text()).then(txt => {
            if (txt !== '') { alert('오류: ' + txt); return; }
            alert('삭제되었습니다.');
            document.getElementById('opiModal').classList.remove('on');
            loadAllDataByPsnlCd(currentPsnlCd);
        });
});

window.deleteOpi = function(opiCd) {
    if (!opiCd || !confirm('이 평가 이력을 삭제하시겠습니까?')) return;
    fetch(DIR_ROOT + '/sys/opiConfig.php?key=' + API_TOKEN + '&OPI_CD=' + opiCd + '&CRUD=D')
        .then(r => r.text()).then(() => loadAllDataByPsnlCd(currentPsnlCd));
};

// ── ⑦ 자녀 학비 보조금 (Tuition) ──

// 가족 테이블에서 자녀만 추출하여 드롭다운 갱신
function refreshTuiChildSelect() {
    const sel = document.getElementById('tuiChildSelect');
    const prevFmlCd = sel.value;
    sel.innerHTML = '<option value="">— 자녀를 선택하세요 —</option>';

    const fmlRows = document.querySelectorAll('#fmlBody tr:not(.tbl-empty)');
    let hasChild = false;
    fmlRows.forEach(tr => {
        const relationEl = tr.querySelector('.fmlRelation');
        const nmEl = tr.querySelector('.fmlNm');
        if (!relationEl || !nmEl) return;
        if (relationEl.value === '자녀') {
            // fmlCd는 저장 버튼 onclick에서 첫 인자로 파악하거나 데이터로부터
            // 저장된 행은 btn-save-row onclick="saveFmlRow('FML_CD','idx')" 형태
            const saveBtn = tr.querySelector('.btn-save-row');
            if (!saveBtn) return;
            const match = saveBtn.getAttribute('onclick').match(/saveFmlRow\('([^']*)'/);
            if (!match) return;
            const fmlCd = match[1];
            if (!fmlCd) return; // 미저장 자녀는 제외
            hasChild = true;
            const opt = document.createElement('option');
            opt.value = fmlCd;
            opt.textContent = nmEl.value || '(이름없음)';
            if (fmlCd === prevFmlCd) opt.selected = true;
            sel.appendChild(opt);
        }
    });

    if (!hasChild) {
        sel.innerHTML = '<option value="">— 가족정보에서 자녀를 먼저 등록하세요 —</option>';
        document.getElementById('tuiRemainBadge').style.display = 'none';
        document.getElementById('tuiBody').innerHTML = '<tr><td colspan="6" class="tbl-empty">가족정보에 자녀를 먼저 등록하세요.</td></tr>';
        currentTuiFmlCd = '';
        return;
    }

    // 이전 선택값 유지 or 첫번째 자녀 자동 선택
    if (!sel.value) sel.selectedIndex = 1;
    const newFmlCd = sel.value;
    if (newFmlCd !== currentTuiFmlCd) {
        currentTuiFmlCd = newFmlCd;
        loadTuitionData(newFmlCd);
    }
}

// 자녀 드롭다운 변경 이벤트
document.getElementById('tuiChildSelect').addEventListener('change', function() {
    currentTuiFmlCd = this.value;
    if (!currentTuiFmlCd) {
        document.getElementById('tuiBody').innerHTML = '<tr><td colspan="6" class="tbl-empty">자녀를 선택하면 지급 이력이 표시됩니다</td></tr>';
        document.getElementById('tuiRemainBadge').style.display = 'none';
        return;
    }
    loadTuitionData(currentTuiFmlCd);
});

function loadTuitionData(fmlCd) {
    if (!fmlCd) return;
    fetch(DIR_ROOT + '/sys/tuitionList.php?key=' + API_TOKEN + '&FML_CD=' + encodeURIComponent(fmlCd))
        .then(r => r.json()).then(json => {
            const row = json.data && json.data[0];
            if (!row) {
                tuitionDataList = [];
                document.getElementById('tuiBody').innerHTML = '<tr><td colspan="6" class="tbl-empty">지급 내역이 없습니다. 지급 등록을 눌러 등록하세요.</td></tr>';
                updateTuiBadge(0);
                return;
            }
            tuitionDataList = row.ISSUE_DETAILS || [];
            renderTuitionRows(tuitionDataList);
            updateTuiBadge(parseInt(row.SUPPORT_CNT)||0);
        }).catch(() => {});
}

function updateTuiBadge(cnt) {
    const badge = document.getElementById('tuiRemainBadge');
    const remain = 8 - cnt;
    badge.style.display = 'inline-block';
    badge.textContent = `총 ${cnt}회 지급 / 잔여 ${remain}회`;
    badge.className = 'tui-remain-badge' + (remain <= 0 ? ' full' : '');
    // 8회 초과 시 신규 버튼 비활성화
    document.getElementById('newTuiBtn').disabled = remain <= 0;
    document.getElementById('newTuiBtn').title = remain <= 0 ? '최대 8회 지급 한도를 초과하였습니다.' : '';
}

function renderTuitionRows(issues) {
    const tbody = document.getElementById('tuiBody');
    tbody.innerHTML = '';
    if (!issues || issues.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="tbl-empty">지급 내역이 없습니다.</td></tr>';
        return;
    }
    issues.forEach((item, i) => {
        if (!item.ISSUE_CD) return;
        const tr = document.createElement('tr');
        tr.className = 'list-row';
        tr.innerHTML = `
            <td>${escH(item.ISSUE_DT||'')}</td>
            <td style="text-align:right;">${Number(item.ISSUE_AMT||0).toLocaleString()}</td>
            <td>${escH(item.SCHOOL_GRADE||'')}</td>
            <td style="text-align:left;">${escH(item.MEMO||'')}</td>
            <td><button class="btn-save-row" onclick="openTuiModal(${i})">수정</button></td>
            <td><button class="btn-del-row" onclick="deleteTuition('${escH(item.ISSUE_CD||'')}')">삭제</button></td>`;
        tbody.appendChild(tr);
    });
}

window.openTuiModal = function(idx) {
    if (!currentPsnlCd) { alert('기초정보를 먼저 저장하세요.'); return; }
    if (!currentTuiFmlCd) { alert('자녀를 먼저 선택하세요.'); return; }

    // 잔여 회차 확인 (신규일 때)
    const badge = document.getElementById('tuiRemainBadge');
    if (idx === -1) {
        const remain = parseInt((badge.textContent.match(/잔여 (\d+)회/) || [])[1] || 8);
        if (remain <= 0) { alert('최대 8회 지급 한도를 초과하였습니다.'); return; }
        document.getElementById('p7_ISSUE_CD').value   = '';
        document.getElementById('p7_FML_CD').value     = currentTuiFmlCd;
        // 자녀 이름
        const selOpt = document.getElementById('tuiChildSelect').selectedOptions[0];
        document.getElementById('p7_FML_NM').value     = selOpt ? selOpt.textContent : '';
        document.getElementById('p7_ISSUE_DT').value   = new Date().toISOString().substring(0,10);
        document.getElementById('p7_ISSUE_AMT').value  = '';
        document.getElementById('p7_SCHOOL_GRADE').value = '';
        document.getElementById('p7_MEMO').value       = '';
        document.getElementById('delTuiBtn').style.display = 'none';
    } else {
        const d = tuitionDataList[idx];
        document.getElementById('p7_ISSUE_CD').value   = d.ISSUE_CD    || '';
        document.getElementById('p7_FML_CD').value     = currentTuiFmlCd;
        const selOpt = document.getElementById('tuiChildSelect').selectedOptions[0];
        document.getElementById('p7_FML_NM').value     = selOpt ? selOpt.textContent : '';
        document.getElementById('p7_ISSUE_DT').value   = d.ISSUE_DT    || '';
        document.getElementById('p7_ISSUE_AMT').value  = d.ISSUE_AMT   || '';
        document.getElementById('p7_SCHOOL_GRADE').value = d.SCHOOL_GRADE || '';
        document.getElementById('p7_MEMO').value       = d.MEMO        || '';
        document.getElementById('delTuiBtn').style.display = 'inline-block';
    }
    bindDateBoxes(document.getElementById('tuiModal'));
    document.getElementById('tuiModal').classList.add('on');
};

document.getElementById('newTuiBtn').addEventListener('click', () => openTuiModal(-1));

document.getElementById('saveTuiBtn').addEventListener('click', () => {
    const issueDt  = document.getElementById('p7_ISSUE_DT').value;
    const issueAmt = document.getElementById('p7_ISSUE_AMT').value;
    if (issueDt.length < 3) { alert('지급일은 필수입니다.'); return; }
    if (!issueAmt)           { alert('지급액은 필수입니다.'); return; }

    const formData = new URLSearchParams();
    formData.append('key', API_TOKEN);
    const issueCd = document.getElementById('p7_ISSUE_CD').value;
    formData.append('CRUD', issueCd ? 'U' : 'C');
    if (issueCd) formData.append('ISSUE_CD', issueCd);
    formData.append('FML_CD',       document.getElementById('p7_FML_CD').value);
    formData.append('PSNL_CD',      currentPsnlCd);
    formData.append('ISSUE_DT',     issueDt);
    formData.append('ISSUE_AMT',    issueAmt);
    formData.append('SCHOOL_GRADE', document.getElementById('p7_SCHOOL_GRADE').value);
    formData.append('MEMO',         document.getElementById('p7_MEMO').value);

    fetch(DIR_ROOT + '/sys/tuitionConfig.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    }).then(r => r.text()).then(() => {
        alert(issueCd ? '수정되었습니다.' : '지급 내역이 추가되었습니다.');
        document.getElementById('tuiModal').classList.remove('on');
        loadTuitionData(currentTuiFmlCd);
        // 배지 갱신을 위해 전체 reload
        fetch(DIR_ROOT + '/sys/tuitionList.php?key=' + API_TOKEN + '&FML_CD=' + encodeURIComponent(currentTuiFmlCd))
            .then(r => r.json()).then(json => {
                const row = json.data && json.data[0];
                if (row) updateTuiBadge(parseInt(row.SUPPORT_CNT)||0);
            });
    }).catch(e => alert('저장 오류: ' + e.message));
});

document.getElementById('delTuiBtn').addEventListener('click', () => {
    const cd = document.getElementById('p7_ISSUE_CD').value;
    if (!cd || !confirm('이 지급 내역을 삭제하시겠습니까?')) return;
    const formData = new URLSearchParams();
    formData.append('key', API_TOKEN);
    formData.append('CRUD', 'D');
    formData.append('ISSUE_CD', cd);
    fetch(DIR_ROOT + '/sys/tuitionConfig.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    }).then(() => {
        alert('삭제되었습니다.');
        document.getElementById('tuiModal').classList.remove('on');
        loadTuitionData(currentTuiFmlCd);
    });
});

window.deleteTuition = function(issueCd) {
    if (!issueCd || !confirm('이 지급 내역을 삭제하시겠습니까?')) return;
    const formData = new URLSearchParams();
    formData.append('key', API_TOKEN);
    formData.append('CRUD', 'D');
    formData.append('ISSUE_CD', issueCd);
    fetch(DIR_ROOT + '/sys/tuitionConfig.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    }).then(() => loadTuitionData(currentTuiFmlCd));
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
