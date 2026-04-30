<?php include('./components/header.php'); ?>
<style>
/* ── 페이지 레이아웃 ── */
#empRegPage { max-width:1300px; }
.page-title-bar{display:flex;align-items:center;justify-content:space-between;padding:10px 0 14px;}
.page-title-bar h4{margin:0;font-size:20px;color:#1e3a5f;font-weight:700;letter-spacing:-.3px;}
.page-title-bar h4 span{font-size:13px;font-weight:400;color:#6b7280;margin-left:10px;}

/* ── 카드 공통 ── */
.info-card{background:#fff;border-radius:10px;box-shadow:0 2px 14px rgba(0,0,0,.07);margin-bottom:18px;overflow:hidden;transition:box-shadow .2s;}
.info-card:hover{box-shadow:0 4px 22px rgba(0,0,0,.12);}
.card-hd{display:flex;align-items:center;justify-content:space-between;padding:11px 18px;color:#fff;font-size:13.5px;font-weight:600;}
.card-hd .card-hd-actions{display:flex;gap:6px;align-items:center;}
.card-hd--search{background:linear-gradient(135deg,#334155,#475569);}
.card-hd--basic{background:linear-gradient(135deg,#1e40af,#3b82f6);}
.card-hd--trs{background:linear-gradient(135deg,#065f46,#10b981);}
.card-hd--grd{background:linear-gradient(135deg,#6d28d9,#8b5cf6);}
.card-hd--adj{background:linear-gradient(135deg,#c2410c,#f97316);}
.card-hd--fml{background:linear-gradient(135deg,#0e7490,#22d3ee);}
.card-body{padding:16px 18px;}

/* ── 2열 그리드 ── */
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
@media(max-width:900px){.two-col{grid-template-columns:1fr;}}

/* ── 폼 공통 ── */
.f-row{display:flex;align-items:center;margin-bottom:9px;}
.f-label{width:115px;min-width:115px;font-size:12.5px;color:#4b5563;font-weight:600;}
.f-ctrl{flex:1;}
.f-ctrl input,.f-ctrl select{
    width:100%;padding:7px 10px;border:1px solid #e5e7eb;border-radius:7px;
    font-size:13px;box-sizing:border-box;background:#fff;color:#111;
    transition:border .15s,box-shadow .15s;
}
.f-ctrl input:focus,.f-ctrl select:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.12);}
.f-ctrl input[readonly]{background:#f3f4f6;color:#6b7280;}
.f-ctrl .f-inline{display:flex;gap:6px;}
.f-ctrl .f-inline input{flex:1;}
.req{color:#ef4444;margin-left:1px;}

/* ── 저장 배지 ── */
.save-badge{font-size:11px;padding:2px 9px;border-radius:20px;font-weight:500;white-space:nowrap;}
.badge-saved{background:rgba(255,255,255,.25);color:#fff;}
.badge-unsaved{background:rgba(255,255,255,.2);color:#fde68a;}

/* ── 잠금 오버레이 ── */
.lock-wrap{position:relative;}
.lock-overlay{
    display:none;position:absolute;inset:0;z-index:10;
    background:rgba(248,250,252,.88);border-radius:6px;
    display:flex;align-items:center;justify-content:center;
    font-size:13px;color:#6b7280;gap:7px;backdrop-filter:blur(2px);
}
.lock-overlay.on{display:flex;}
.lock-overlay.off{display:none;}

/* ── 버튼 ── */
.btn{padding:6px 16px;border:none;border-radius:7px;cursor:pointer;font-size:13px;font-weight:600;transition:opacity .15s,transform .1s;}
.btn:active{transform:scale(.97);}
.btn-primary{background:#2563eb;color:#fff;}
.btn-primary:hover{opacity:.88;}
.btn-success{background:#059669;color:#fff;}
.btn-success:hover{opacity:.88;}
.btn-sm{padding:4px 11px;font-size:12px;border-radius:6px;}
.btn-ghost{background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;}
.btn-ghost:hover{background:#e2e8f0;}
.btn-danger{background:#dc2626;color:#fff;}
.btn-danger:hover{opacity:.85;}
.btn-save-row{background:#1d4ed8;color:#fff;padding:3px 10px;font-size:12px;border:none;border-radius:5px;cursor:pointer;}
.btn-del-row{background:#dc2626;color:#fff;padding:3px 8px;font-size:12px;border:none;border-radius:5px;cursor:pointer;}

/* ── 직원 요약 배너 ── */
#empBanner{display:none;background:linear-gradient(90deg,#eff6ff,#dbeafe);border:1px solid #bfdbfe;border-radius:8px;padding:10px 16px;margin-bottom:6px;font-size:13.5px;color:#1e40af;font-weight:600;}
#empBanner span{font-weight:400;color:#374151;margin-left:6px;}

/* ── 인라인 테이블 ── */
.inline-tbl{width:100%;border-collapse:collapse;font-size:12.5px;}
.inline-tbl th{background:#f8fafc;color:#475569;font-weight:600;padding:8px 6px;border-bottom:2px solid #e5e7eb;text-align:center;white-space:nowrap;}
.inline-tbl td{padding:5px 5px;border-bottom:1px solid #f1f5f9;vertical-align:middle;text-align:center;}
.inline-tbl tr:hover td{background:#f8fafc;}
.inline-tbl td input,.inline-tbl td select{
    width:100%;padding:4px 6px;border:1px solid #e5e7eb;border-radius:5px;
    font-size:12px;box-sizing:border-box;
}
.inline-tbl td input:focus,.inline-tbl td select:focus{border-color:#3b82f6;outline:none;}
.tbl-empty{text-align:center;color:#9ca3af;padding:20px;font-size:13px;}

/* ── 구분선 ── */
.section-divider{height:1px;background:linear-gradient(90deg,#e5e7eb,transparent);margin:4px 0 12px;}

/* ── 저장전체 고정 바 ── */
#saveAllBar{position:sticky;bottom:0;background:#fff;border-top:1px solid #e5e7eb;padding:10px 18px;text-align:right;z-index:50;display:flex;align-items:center;justify-content:space-between;box-shadow:0 -4px 14px rgba(0,0,0,.06);}
#saveAllBar .bar-info{font-size:12.5px;color:#6b7280;}
/* ── 커스텀 모달 ── */
.custom-modal-wrap{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;}
.custom-modal-wrap.on{display:flex;}
.custom-modal-bg{position:absolute;inset:0;background:rgba(0,0,0,.4);backdrop-filter:blur(3px);}
.custom-modal-box{position:relative;background:#fff;border-radius:12px;width:100%;max-width:550px;box-shadow:0 10px 40px rgba(0,0,0,.2);overflow:hidden;animation:modalUp .2s ease-out;}
@keyframes modalUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
.custom-modal-hd{padding:14px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;font-weight:700;font-size:15px;display:flex;justify-content:space-between;align-items:center;}
.custom-modal-hd a{cursor:pointer;color:#94a3b8;font-size:18px;}
.custom-modal-hd a:hover{color:#333;}
.custom-modal-bd{padding:20px;max-height:80vh;overflow-y:auto;}
.custom-modal-ft{padding:14px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;text-align:right;}
.hidden-row{display:none;}
.list-row{cursor:pointer;}
.list-row:hover td{background:#f1f5f9 !important;}
</style>

<div class="container" id="empRegPage">

    <!-- 타이틀 바 -->
    <div class="page-title-bar">
        <h4>통합 인사정보 관리 <span>신규 직원 등록 및 기존 직원 정보 통합 수정</span></h4>
    </div>

    <!-- 직원 배너 -->
    <div id="empBanner">👤 현재 직원 :
        <span id="bannerNm">-</span> &nbsp;|&nbsp; 코드: <span id="bannerCd">-</span>
        &nbsp;|&nbsp; 소속: <span id="bannerOrg">-</span>
        &nbsp;|&nbsp; 직책: <span id="bannerPos">-</span>
    </div>

    <!-- ① 검색 카드 -->
    <div class="info-card">
        <div class="card-hd card-hd--search">
            <span>🔍 직원 검색</span>
            <span style="font-size:12px;opacity:.8;">기존 직원 선택 시 모든 정보가 자동 로드됩니다 / 빈 상태에서 입력하면 신규 등록</span>
        </div>
        <div class="card-body" style="padding:14px 18px;">
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <div class="f-row" style="margin:0;gap:8px;flex:1;min-width:260px;">
                    <div class="f-label">직원코드</div>
                    <div class="f-ctrl"><input id="PSNL_CD" readonly placeholder="자동" style="background:#f3f4f6;width:90px;"></div>
                </div>
                <div class="f-row" style="margin:0;gap:8px;flex:2;min-width:260px;">
                    <div class="f-label">성명</div>
                    <div class="f-ctrl f-inline">
                        <input id="PSNL_NM" placeholder="성명 입력 후 검색 버튼 또는 엔터">
                        <button class="btn btn-ghost btn-sm" id="srchPsnlBtn">직원 검색</button>
                    </div>
                </div>
                <input type="hidden" id="ORG_NM">
                <input type="hidden" id="POSITION">
                <button class="btn btn-ghost btn-sm" id="newEmpBtn" style="white-space:nowrap;">✚ 신규 초기화</button>
            </div>
        </div>
    </div>

    <!-- ② 기초정보 + 발령정보 2열 -->
    <div class="two-col">

        <!-- 기초정보 -->
        <div class="info-card">
            <div class="card-hd card-hd--basic">
                <span>① 기초정보 (PSNL_INFO)</span>
                <div class="card-hd-actions">
                    <span class="save-badge badge-unsaved" id="badge1">미저장</span>
                    <button class="btn btn-sm" id="saveBasicBtn" style="background:rgba(255,255,255,.2);color:#fff;">저장</button>
                </div>
            </div>
            <div class="card-body">
                <div class="f-row"><div class="f-label">일련번호</div><div class="f-ctrl"><input id="p1_PSNL_CD" readonly placeholder="저장 후 자동부여"></div></div>
                <div class="f-row"><div class="f-label">성명<span class="req">*</span></div><div class="f-ctrl"><input id="p1_PSNL_NM" placeholder="필수 입력"></div></div>
                <div class="f-row"><div class="f-label">세례명</div><div class="f-ctrl"><input id="p1_BAPT_NM"></div></div>
                <div class="f-row"><div class="f-label">연락처</div><div class="f-ctrl"><input id="p1_PHONE_NUM" class="phoneNumBox" placeholder="010-0000-0000"></div></div>
                <div class="f-row"><div class="f-label">주민번호<span class="req">*</span></div><div class="f-ctrl"><input id="p1_PSNL_NUM" class="juminNumBox" placeholder="000000-0000000"></div></div>
            </div>
        </div>

        <!-- 발령정보 -->
        <div class="info-card">
            <div class="card-hd card-hd--trs">
                <span>② 발령정보 이력 (PSNL_TRANSFER)</span>
                <div class="card-hd-actions">
                    <button class="btn btn-sm" id="newTrsBtn" style="background:rgba(255,255,255,.2);color:#fff;">+ 신규 발령</button>
                </div>
            </div>
            <div class="card-body lock-wrap" style="padding:12px 14px;">
                <div class="lock-overlay off" id="lock2">🔒 기초정보를 먼저 저장하세요</div>
                <div style="overflow-x:auto;">
                    <table class="inline-tbl" id="trsTbl">
                        <thead><tr><th>발령일</th><th>인사구분</th><th>시행조직</th><th>직책</th><th>재직구분</th></tr></thead>
                        <tbody id="trsBody"><tr><td colspan="5" class="tbl-empty">직원을 선택하거나 기초정보를 저장하세요</td></tr></tbody>
                    </table>
                </div>
                <div style="text-align:center;margin-top:10px;display:none;" id="trsMoreWrap">
                    <button class="btn btn-ghost btn-sm" id="trsMoreBtn">👇 과거 이력 더보기 (<span id="trsHiddenCnt">0</span>건)</button>
                </div>
            </div>
        </div>

    </div><!-- /.two-col -->

    <!-- ③ 급호봉 -->
    <div class="info-card">
        <div class="card-hd card-hd--grd">
            <span>③ 급호봉 이력 (GRADE_HISTORY)</span>
            <div class="card-hd-actions">
                <button class="btn btn-sm" id="newGrdBtn" style="background:rgba(255,255,255,.2);color:#fff;">+ 신규 승급</button>
            </div>
        </div>
        <div class="card-body lock-wrap" style="padding:12px 14px;">
            <div class="lock-overlay off" id="lock3">🔒 기초정보를 먼저 저장하세요</div>
            <div style="overflow-x:auto;">
                <table class="inline-tbl" id="grdTbl">
                    <thead><tr><th>승급일</th><th>급</th><th>호</th><th>상세메모</th></tr></thead>
                    <tbody id="grdBody"><tr><td colspan="4" class="tbl-empty">직원을 선택하거나 기초정보를 저장하세요</td></tr></tbody>
                </table>
            </div>
            <div style="text-align:center;margin-top:10px;display:none;" id="grdMoreWrap">
                <button class="btn btn-ghost btn-sm" id="grdMoreBtn">👇 과거 이력 더보기 (<span id="grdHiddenCnt">0</span>건)</button>
            </div>
        </div>
    </div>

    <!-- ④ 제수당 -->
    <div class="info-card">
        <div class="card-hd card-hd--adj">
            <span>④ 제수당 정보 (PSNL_ADJUST)</span>
            <div class="card-hd-actions">
                <button class="btn btn-sm" id="addAdjBtn" style="background:rgba(255,255,255,.2);color:#fff;">+ 행 추가</button>
            </div>
        </div>
        <div class="card-body lock-wrap" style="padding:12px 14px;">
            <div class="lock-overlay off" id="lock4">🔒 기초정보를 먼저 저장하세요</div>
            <div style="overflow-x:auto;">
                <table class="inline-tbl" id="adjTbl" style="min-width:900px;">
                    <thead><tr>
                        <th style="width:28px;">#</th>
                        <th style="min-width:100px;">명칭<span class="req">*</span></th>
                        <th style="width:80px;">수당종류</th>
                        <th style="width:60px;">번호</th>
                        <th style="width:55px;">등급</th>
                        <th style="width:105px;">발급/취득일</th>
                        <th style="min-width:90px;">상세</th>
                        <th style="width:80px;">수당금액</th>
                        <th style="width:105px;">시작일<span class="req">*</span></th>
                        <th style="width:105px;">종료일</th>
                        <th style="width:52px;">저장</th>
                        <th style="width:44px;">삭제</th>
                    </tr></thead>
                    <tbody id="adjBody"><tr><td colspan="12" class="tbl-empty">직원을 선택하거나 기초정보를 저장하면 입력 가능합니다</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ⑤ 가족정보 -->
    <div class="info-card">
        <div class="card-hd card-hd--fml">
            <span>⑤ 가족정보 (PSNL_FAMILY)</span>
            <div class="card-hd-actions">
                <button class="btn btn-sm" id="addFmlBtn" style="background:rgba(255,255,255,.2);color:#fff;">+ 행 추가</button>
            </div>
        </div>
        <div class="card-body lock-wrap" style="padding:12px 14px;">
            <div class="lock-overlay off" id="lock5">🔒 기초정보를 먼저 저장하세요</div>
            <div style="overflow-x:auto;">
                <table class="inline-tbl" id="fmlTbl" style="min-width:820px;">
                    <thead><tr>
                        <th style="width:28px;">#</th>
                        <th style="min-width:90px;">가족성명<span class="req">*</span></th>
                        <th style="width:80px;">가족관계</th>
                        <th style="width:105px;">생년월일<span class="req">*</span></th>
                        <th style="min-width:90px;">상세</th>
                        <th style="width:80px;">가족수당</th>
                        <th style="width:105px;">시작일</th>
                        <th style="width:105px;">종료일</th>
                        <th style="width:52px;">저장</th>
                        <th style="width:44px;">삭제</th>
                    </tr></thead>
                    <tbody id="fmlBody"><tr><td colspan="10" class="tbl-empty">직원을 선택하거나 기초정보를 저장하면 입력 가능합니다</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 모달: 발령정보 -->
    <div class="custom-modal-wrap" id="trsModal">
        <div class="custom-modal-bg" onclick="document.getElementById('trsModal').classList.remove('on')"></div>
        <div class="custom-modal-box">
            <div class="custom-modal-hd">
                <span>발령정보 편집</span>
                <a onclick="document.getElementById('trsModal').classList.remove('on')">✖</a>
            </div>
            <div class="custom-modal-bd">
                <div class="f-row"><div class="f-label">일련번호</div><div class="f-ctrl"><input id="p2_TRS_CD" readonly placeholder="저장 후 자동부여"></div></div>
                <div class="f-row">
                    <div class="f-label">시행조직<span class="req">*</span></div>
                    <div class="f-ctrl f-inline">
                        <input id="orgCd" readonly style="width:80px;background:#f3f4f6;" placeholder="코드">
                        <input id="orgNm" placeholder="조직명" style="flex:1;">
                        <button class="btn btn-ghost btn-sm" id="orgSerchPop">검색</button>
                    </div>
                </div>
                <div class="f-row"><div class="f-label">재직구분</div><div class="f-ctrl"><select id="p2_WORK_TYPE"><option>계약직</option><option>무기계약직</option><option>정규직</option><option>기능직</option></select></div></div>
                <div class="f-row"><div class="f-label">직책</div><div class="f-ctrl"><select id="p2_POSITION"><option>가사사용인</option><option>경비직</option><option>관리원</option><option>관리장</option><option>사무원</option><option>사무장</option></select></div></div>
                <div class="f-row"><div class="f-label">인사구분</div><div class="f-ctrl"><select id="p2_TRS_TYPE"><option value="1">입사</option><option value="2">퇴사</option><option value="3">전보</option></select></div></div>
                <div class="f-row"><div class="f-label">인사상세</div><div class="f-ctrl"><input id="p2_TRS_DTL"></div></div>
                <div class="f-row"><div class="f-label">발령일<span class="req">*</span></div><div class="f-ctrl"><input id="p2_TRS_DT" class="dateBox" placeholder="YYYY-MM-DD"></div></div>
                <div class="f-row"><div class="f-label">임용일</div><div class="f-ctrl"><input id="p2_APP_DT" class="dateBox" placeholder="YYYY-MM-DD"></div></div>
                <div class="f-row"><div class="f-label">복리후생기준일</div><div class="f-ctrl"><input id="p2_BNF_DT" class="dateBox" placeholder="YYYY-MM-DD"></div></div>
            </div>
            <div class="custom-modal-ft">
                <button class="btn btn-danger" id="delTrsBtn" style="float:left;display:none;">삭제</button>
                <button class="btn btn-primary" id="saveTrsBtn">저장</button>
            </div>
        </div>
    </div>

    <!-- 모달: 급호봉정보 -->
    <div class="custom-modal-wrap" id="grdModal">
        <div class="custom-modal-bg" onclick="document.getElementById('grdModal').classList.remove('on')"></div>
        <div class="custom-modal-box">
            <div class="custom-modal-hd">
                <span>급호봉 편집</span>
                <a onclick="document.getElementById('grdModal').classList.remove('on')">✖</a>
            </div>
            <div class="custom-modal-bd">
                <div class="f-row"><div class="f-label">일련번호</div><div class="f-ctrl"><input id="p3_GRD_CD" readonly placeholder="저장 후 자동부여"></div></div>
                <div class="f-row"><div class="f-label">승급변동일<span class="req">*</span></div><div class="f-ctrl"><input id="p3_ADVANCE_DT" class="dateBox" placeholder="YYYY-MM-DD"></div></div>
                <div class="f-row"><div class="f-label">급(레벨)<span class="req">*</span></div><div class="f-ctrl"><input id="p3_GRD_GRADE" placeholder="숫자"></div></div>
                <div class="f-row"><div class="f-label">호<span class="req">*</span></div><div class="f-ctrl"><input id="p3_GRD_PAY" placeholder="숫자"></div></div>
                <div class="f-row"><div class="f-label">메모</div><div class="f-ctrl"><input id="p3_GRD_DTL"></div></div>
            </div>
            <div class="custom-modal-ft">
                <button class="btn btn-danger" id="delGrdBtn" style="float:left;display:none;">삭제</button>
                <button class="btn btn-primary" id="saveGrdBtn">저장</button>
            </div>
        </div>
    </div>

    <br>
</div><!-- /#empRegPage -->

<link href="<?php echo DIR_ROOT; ?>/assets/css/hr_tbl.css?ver=1775259319" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/searchArea.css?ver=1775259319" rel="stylesheet" />
<script src="<?php echo DIR_ROOT; ?>/assets/js/dateForm.js"></script>
<script src="<?php echo DIR_ROOT; ?>/assets/js/phoneForm.js"></script>
<script src="<?php echo DIR_ROOT; ?>/assets/js/juminForm.js"></script>
<script src="<?php echo DIR_ROOT; ?>/assets/js/newEmpReg.js"></script>
<?php include('components/footer.php'); ?>
