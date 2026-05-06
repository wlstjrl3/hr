//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    xhr: {
        url: DIR_ROOT + '/sys/psnlTotal.php',
        columXHR: '',
        key: API_TOKEN, //api 호출할 보안 개인인증키
        where: {
            TRS_TYPE: '1', //filter 값 변동 전 초기조건값으로 재직구분이 '재직'인 데이터만
            ORG_NM: document.getElementById("ORG_NM").value,
            PSNL_NM: document.getElementById("PSNL_NM").value,
            BAPT_NM: document.getElementById("BAPT_NM").value,
            POSITION: document.getElementById("POSITION").value,
            PHONE_NUM: document.getElementById("PHONE_NUM").value,
            USE_KOREAN_AGE: document.getElementById("USE_KOREAN_AGE") ? document.getElementById("USE_KOREAN_AGE").value : 'N',
        },
        order: {
            column: '1',
            direction: 'asc',
        },
        page: 0, //표시되는 페이지에서 1이 빠진 값이다 즉 page:0 = 1페이지
        limit: 10, //만약 리미트가 0이라면 리미트 없이 전체 조회하는 것으로 처리 excel down등에서 0 처리해야 함!
    },
    columns: [
        //반드시 첫열이 key값이되는 열이 와야한다. 숨김여부는 class로 추가 지정
        { title: "idx", data: "PSNL_CD", className: "hidden" }
        , { title: "소속조직", data: "ORG_NM", className: "" }
        , { title: "성명", data: "PSNL_NM", className: "" }
        , { title: "세례명", data: "BAPT_NM", className: "" }
        , { title: "연령", data: "AGE", className: "" }
        , { title: "직책", data: "POSITION", className: "" }
        , { title: "고용형태", data: "WORK_TYPE", className: "" }
        , { title: "연락처", data: "PHONE_NUM", className: "hidden" }
        , { title: "주민번호", data: "PSNL_NUM", className: "hidden" }
        , { title: "재직구분", data: "TRS_TYPE", className: "hidden" }
        , { title: "복리후생일", data: "BNF_DT", className: "" }
        , { 
            title: "입/퇴사일", data: "TRS_DT", className: "", render: function (data, row) {
                if (row.TRS_TYPE === '퇴사' && data) {
                    return data + "(퇴)";
                }
                return data;
            }
        }
        , { title: "임용일", data: "APP_DT", className: "hidden" }
        , { title: "경과(근속)", data: "TRS_ELAPSE", className: "" }
        , { title: "승급일", data: "ADVANCE_DT", className: "" }
        , { title: "분기", data: "ADVANCE_RNG", className: "hidden" }
        , { 
            title: "급(Lv)", data: "GRD_GRADE", className: "", render: function (data, row) {
                if (row.WORK_TYPE && row.WORK_TYPE.includes('계약직') && (row.GRD_PAY == 0 || row.GRD_PAY == '0')) {
                    return "Lv " + data;
                }
                return data;
            }
        }
        , { 
            title: "호", data: "GRD_PAY", className: "", render: function (data, row) {
                if (row.WORK_TYPE && row.WORK_TYPE.includes('계약직') && (data == 0 || data == '0')) {
                    return "";
                }
                return data;
            }
        }
        , { title: "기본급", data: "NORMAL_PAY", className: "" }
        , { title: "법정수당", data: "LEGAL_PAY", className: "" }
        , { title: "신자수", data: "PERSON_CNT", className: "hidden" }
        , { title: "직책수당", data: "ADJUST_PAY1", className: "" }
        , { title: "가족수당", data: "FAMILY_PAY", className: "" }
        , { title: "자격수당", data: "ADJUST_PAY2", className: "" }
        , { title: "장애인수당", data: "ADJUST_PAY3", className: "" }
        , { title: "제수당", data: "ADJUST_PAY4", className: "" }
        , { title: "예상급여", data: "EXPECT_PAY", className: "" }
        , { title: "내선번호", data: "ORG_IN_TEL", className: "hidden" }
    ],
});

//행을 클릭했을때 fetch로 다시 끌어올 데이터
async function trDataXHR(idx) {
    //이전 정보 초기화 INIT
    const cards = ["cardBasic", "cardFml", "cardAdj", "cardOpi", "cardTui", "cardPtt"];
    cards.forEach(c => {
        let el = document.getElementById(c);
        if(el) el.style.display = "none";
    });
    
    const bodies = ["mdBdOrgInTel", "psnlSummaryBody", "fmlTblBody", "adjTblBody", "opiTblBody", "tuitionTblBody", "pttCardsBody"];
    bodies.forEach(b => {
        const el = document.getElementById(b);
        if (el) el.innerHTML = "";
    });

    try {
        // 1. 기본 정보 조회
        const res1 = await fetch(`${DIR_ROOT}/sys/psnlTotal.php?key=${API_TOKEN}&PSNL_CD=${idx}&CRUD=R`).then(r => r.json());
        const data1 = res1.data;
        if (!data1 || data1.length === 0) return;

        const row = data1[0];
        // UI 업데이트
        const goEditBtn = document.getElementById("goEditBtn");
        if (goEditBtn) {
            // 최저임금 대상자(가사사용인 등 PTT 데이터가 있는 경우)는 newEmpReg가 없으면 수정버튼 숨김,
            // 단 PSNL_INFO는 있으니 newEmpReg 연동은 허용
            goEditBtn.style.display = "inline-block";
            goEditBtn.onclick = () => { location.href = DIR_ROOT + "/newEmpReg?PSNL_CD=" + idx; };
        }

        document.getElementById("mdBdOrgInTel").innerHTML = "본당 내선번호 : " + (row.ORG_IN_TEL || "-") + " / 전화번호 : " + (row.ORG_OUT_TEL || "-");

        let basicStr = `
            <div style="display:flex; gap:24px; align-items:center;">
                <!-- 왼쪽 프로필 아바타 영역 -->
                <div style="width:75px; height:75px; background:#e2e8f0; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:32px; color:#94a3b8; flex-shrink:0; cursor:pointer; position:relative; overflow:hidden; border:2px solid #fff; box-shadow:0 2px 8px rgba(0,0,0,0.1);" onclick="openPhotoModal('${idx}')" title="클릭하여 사진 등록">
                    ${row.HAS_PHOTO === 'Y' ? `<img id="profileAvatarImg" src="${DIR_ROOT}/assets/photos/${idx}.jpg?t=${new Date().getTime()}" style="width:100%; height:100%; object-fit:cover;">` : `<img id="profileAvatarImg" style="display:none;">`}
                    <span style="display:${row.HAS_PHOTO === 'Y' ? 'none' : 'flex'}; align-items:center; justify-content:center; width:100%; height:100%;">👤</span>
                </div>
                <!-- 오른쪽 정보 영역 -->
                <div style="flex:1;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                        <span style="font-size:20px; font-weight:800; color:#1e293b;">${row.PSNL_NM || "-"}</span>
                        ${row.BAPT_NM ? `<span style="font-size:13px; color:#64748b; background:#f1f5f9; padding:2px 8px; border-radius:12px;">${row.BAPT_NM}</span>` : ""}
                        <span style="font-size:13px; font-weight:600; color:#3b82f6; background:#eff6ff; padding:2px 8px; border-radius:12px; border:1px solid #bfdbfe;">${row.ORG_NM || "-"} / ${row.POSITION || "-"}</span>
                    </div>
                    
                    <div style="display:grid; grid-template-columns:1fr 1fr; row-gap:10px; column-gap:20px; font-size:13.5px; color:#475569;">
                        <div style="display:flex; align-items:center;">
                            <span style="font-weight:600; width:70px; color:#64748b; font-size:12.5px;">주민번호</span>
                            <span style="color:#1e293b;">${row.PSNL_NUM || "-"}</span>
                        </div>
                        <div style="display:flex; align-items:center;">
                            <span style="font-weight:600; width:70px; color:#64748b; font-size:12.5px;">연락처</span>
                            <span style="color:#1e293b;">${row.PHONE_NUM || "-"}</span>
                        </div>
                        <div style="display:flex; align-items:center;">
                            <span style="font-weight:600; width:70px; color:#64748b; font-size:12.5px;">채용구분</span>
                            <span style="color:#1e293b;">${row.WORK_TYPE || "-"}</span>
                        </div>
                        <div style="display:flex; align-items:center;">
                            <span style="font-weight:600; width:70px; color:#64748b; font-size:12.5px;">발령상태</span>
                            <span style="color:#1e293b;">${row.TRS_TYPE || "-"} <span style="color:#94a3b8;font-size:12px;margin-left:4px;">${row.TRS_DT ? '('+row.TRS_DT+')' : ''}</span></span>
                        </div>
                        <div style="display:flex; align-items:center;">
                            <span style="font-weight:600; width:70px; color:#64748b; font-size:12.5px;">${row.EXPECT_PAY ? '최저임금' : '급호봉'}</span>
                            <span style="color:#1e293b;">${row.EXPECT_PAY ? row.EXPECT_PAY : (row.GRD_GRADE ? row.GRD_GRADE + "급 " + row.GRD_PAY + "호" : "-")}</span>
                        </div>
                        <div style="display:flex; align-items:center;">
                            <span style="font-weight:600; width:70px; color:#64748b; font-size:12.5px;">최근승급</span>
                            <span style="color:#1e293b;">${row.ADVANCE_DT || "-"} <span style="color:#94a3b8;font-size:12px;margin-left:4px;">${row.ADVANCE_RNG ? '('+row.ADVANCE_RNG+')' : ''}</span></span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.getElementById("psnlSummaryBody").innerHTML = basicStr;
        document.getElementById("cardBasic").style.display = "block";


        // 2. 가족 정보 조회
        const res4 = await fetch(`${DIR_ROOT}/sys/fmlList.php?key=${API_TOKEN}&PSNL_CD=${idx}&CRUD=R`).then(r => r.json());
        if (res4.data && res4.data.length > 0) {
            let tmpStr = "";
            res4.data.forEach(f => {
                tmpStr += `<tr><td>${f.FML_NM}</td><td>${f.FML_RELATION}</td><td>${f.FML_BIRTH}</td><td>${f.FML_DTL}</td></tr>`;
            });
            document.getElementById("fmlTblBody").innerHTML = tmpStr;
            document.getElementById("cardFml").style.display = "block";
        }

        // 3. 제수당 정보 조회
        const res5 = await fetch(`${DIR_ROOT}/sys/adjList.php?key=${API_TOKEN}&PSNL_CD=${idx}&CRUD=R`).then(r => r.json());
        if (res5.data && res5.data.length > 0) {
            let tmpStr = "";
            res5.data.forEach(a => {
                let p = a.ADJ_PAY ? Number(a.ADJ_PAY).toLocaleString() + "원" : "-";
                tmpStr += `<tr><td>${a.ADJ_TYPE}</td><td>${a.ADJ_NM}</td><td>${a.ADJ_LEVEL}</td><td>${p}</td></tr>`;
            });
            document.getElementById("adjTblBody").innerHTML = tmpStr;
            document.getElementById("cardAdj").style.display = "block";
        }

        // 4. 상벌/평가 정보 조회
        const res6 = await fetch(`${DIR_ROOT}/sys/opiList.php?key=${API_TOKEN}&PSNL_CD=${idx}&CRUD=R`).then(r => r.json());
        if (res6.data && res6.data.length > 0) {
            let tmpStr = "";
            const opiTypes = { 1: "긍정", 2: "부정", 3: "포상", 4: "징계" };
            res6.data.forEach(o => {
                tmpStr += `<tr><td>${opiTypes[o.OPI_TYPE] || ""}</td><td>${o.OPI_DT}</td><td>${o.OPI_PERSON}</td><td style="text-align:left;padding-left:10px;">${o.OPI_DTL}</td></tr>`;
            });
            document.getElementById("opiTblBody").innerHTML = tmpStr;
            document.getElementById("cardOpi").style.display = "block";
        }

        // 5. 자녀 학비 보조 정보 조회
        const res7 = await fetch(`${DIR_ROOT}/sys/tuitionList.php?key=${API_TOKEN}&PSNL_CD=${idx}`).then(r => r.json());
        if (res7.data && res7.data.length > 0) {
            // 학비보조 데이터가 존재하는 자녀만 필터링
            const validTuitionData = res7.data.filter(t => Number(t.SUPPORT_CNT) > 0);
            
            if (validTuitionData.length > 0) {
                let tmpStr = "";
                validTuitionData.forEach(t => {
                    tmpStr += `<tr><td>${t.FML_NM}</td><td>${t.FML_BIRTH}</td><td>${t.START_GRADE || '-'}</td><td>${t.SUPPORT_CNT}회</td><td style="color:#dc2626;font-weight:bold;">${t.REMAIN_CNT}회</td><td style="text-align:right;padding-right:10px;">${Number(t.TOTAL_AMT).toLocaleString()}원</td></tr>`;
                });
                document.getElementById("tuitionTblBody").innerHTML = tmpStr;
                document.getElementById("cardTui").style.display = "block";
            }
        }
        // 6. 최저임금 근무조건 이력 조회 (PTT)
        const res8 = await fetch(`${DIR_ROOT}/sys/pttList.php?key=${API_TOKEN}&PSNL_CD=${idx}&ORDER=PTT_YEAR DESC`).then(r => r.json());
        if (res8.data && res8.data.length > 0) {
            let pttHtml = `<div id="pttListWrapper" style="display:flex; flex-direction:column; gap:8px;">`;
            res8.data.forEach((p, i) => {
                const isHidden = i >= 5 ? 'display:none;' : '';
                const hideClass = i >= 5 ? 'ptt-extra-row' : '';
                
                const adjPayNote = Number(p.PTT_ADJPAY || 0) > 0
                    ? `<span style="font-size:11px;color:#7c3aed;"> +${Number(p.PTT_ADJPAY).toLocaleString()}원 조정</span>`
                    : '';
                
                const totalAddPay = Number(p.PTT_TOTALADDPAY);
                const addPaySection = (!isNaN(totalAddPay) && totalAddPay > 0) 
                    ? `<div>추가수당: <span style="color:#dc2626;"><b>${totalAddPay.toLocaleString()}원</b></span></div>` 
                    : '';

                pttHtml += `
                <div class="${hideClass}" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px 15px; ${isHidden}">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <span style="font-size:15px; font-weight:800; color:#475569;">${p.PTT_YEAR}년 <small style="font-weight:normal; color:#64748b; margin-left:5px;">(${p.POSITION || '-'})</small></span>
                        <span style="font-size:12px; color:#64748b;">주 <b>${p.PTT_DAY}일 ${p.PTT_HOUR}시간</b>${adjPayNote}</span>
                    </div>
                    <div style="font-size:13px; color:#1e293b; display:flex; gap:15px; align-items:center;">
                        ${addPaySection}
                        <div style="font-size:11px; color:#94a3b8;">(내역: ${p.PTT_TOTALPAY} 기본 + ${Number(p.PTT_ADDPAY || 0).toLocaleString()} 연장)</div>
                    </div>
                </div>`;
            });
            
            if (res8.data.length > 5) {
                pttHtml += `
                <button id="btnPttMore" class="btn btn-ghost btn-sm" style="width:100%; margin-top:5px; border:1px dashed #cbd5e1;" onclick="showAllPtt()">
                    전체 이력 보기 (${res8.data.length}건) ▾
                </button>`;
            }
            
            pttHtml += `</div>`;
            document.getElementById("pttCardsBody").innerHTML = pttHtml;
            document.getElementById("cardPtt").style.display = "block";
        }
    } catch (e) {
        console.error("Data load failed:", e);
    }
}

//검색 필터링을 위한 코드
document.querySelectorAll(".filter").forEach((f, key) => {
    f.addEventListener("change", () => {
        if (f.type === 'checkbox') {
            mytbl.hrDt.xhr.where[f.id] = f.checked ? 'Y' : 'N';
        } else {
            mytbl.hrDt.xhr.where[f.id] = f.value;
        }
        mytbl.hrDt.xhr.page = 0; //필터가 바뀌면 페이지 수도 바뀌므로 첫장으로 돌려보낸다.
        mytbl.show("myTbl");
    });
});
//날짜 형식 자동 하이픈 추가를 위한 코드
document.querySelectorAll(".dateBox").forEach(dtBox => {
    dtBox.onkeyup = function (event) {
        event = event || window.event;
        var _val = this.value.trim();
        this.value = autoHypenDate(_val);
    }
});
document.querySelectorAll(".shortDateBox").forEach(sdtBox => {
    sdtBox.onkeyup = function (event) {
        event = event || window.event;
        var _val = this.value.trim();
        this.value = autoHypenShortDate(_val);
    }
});
//주민번호 형식 자동 하이픈 추가를 위한 코드
document.querySelectorAll(".juminNumBox").forEach(jmBox => {
    jmBox.onkeyup = function (event) {
        event = event || window.event;
        var _val = this.value.trim();
        this.value = autoHypenJumin(_val);
    }
});
//전화번호 형식 자동 하이픈 추가를 위한 코드
document.querySelectorAll(".phoneNumBox").forEach(phBox => {
    phBox.onkeyup = function (event) {
        event = event || window.event;
        var _val = this.value.trim();
        this.value = autoHypenPhone(_val);
    }
});

// [수정 부분] 나이 필터에 따른 생년월일 자동 설정 (나이와 생년월일 역순 매칭 로직)
let ageTimer;
document.querySelectorAll("#AGE_MIN, #AGE_MAX").forEach(ageInput => {
    ageInput.addEventListener("input", () => {
        clearTimeout(ageTimer);

        ageTimer = setTimeout(() => {
            const minAgeVal = document.getElementById("AGE_MIN").value; // 예: 60세 이상 조회 시 60 입력
            const maxAgeVal = document.getElementById("AGE_MAX").value; // 예: 33세 이하 조회 시 33 입력
            const currentYear = new Date().getFullYear();

            let finalBirthFrom = '';
            let finalBirthTo = '';

            let useKorean = document.getElementById("USE_KOREAN_AGE") ? (document.getElementById("USE_KOREAN_AGE").value === 'Y') : false;
            let offset = useKorean ? 1 : 0;

            if (maxAgeVal !== "") {
                finalBirthFrom = (currentYear - parseInt(maxAgeVal) + offset) + "-01-01";
            }

            if (minAgeVal !== "") {
                finalBirthTo = (currentYear - parseInt(minAgeVal) + offset) + "-12-31";
            }

            document.getElementById("PSNL_BIRTH_From").value = finalBirthFrom;
            document.getElementById("PSNL_BIRTH_To").value = finalBirthTo;

            mytbl.hrDt.xhr.where["PSNL_BIRTH_From"] = finalBirthFrom;
            mytbl.hrDt.xhr.where["PSNL_BIRTH_To"] = finalBirthTo;
            mytbl.hrDt.xhr.where["AGE_MIN"] = minAgeVal;
            mytbl.hrDt.xhr.where["AGE_MAX"] = maxAgeVal;

            mytbl.hrDt.xhr.page = 0;
            mytbl.show("myTbl");
        }, 300);
    });
});

//빠른 세팅 버튼 구성 > dateFormat.js 파일 참조
//const today = new Date; //오늘
document.querySelectorAll(".quikSetBtn").forEach((q, key) => {
    q.addEventListener("click", () => {
        document.getElementById("PSNL_BIRTH_From").value = "";
        document.getElementById("PSNL_BIRTH_To").value = "";
        document.getElementById("AGE_MIN").value = "";
        document.getElementById("AGE_MAX").value = "";
        mytbl.hrDt.xhr.where["AGE_MIN"] = "";
        mytbl.hrDt.xhr.where["AGE_MAX"] = "";
        document.getElementById("TRS_DT_From").value = "";
        document.getElementById("TRS_DT_To").value = "";
        if (q.id == "setRetire") {
            document.getElementById("PSNL_BIRTH_From").value = (new Date).getFullYear() - 60 + "-01-01";
            document.getElementById("PSNL_BIRTH_To").value = (new Date).getFullYear() - 60 + "-12-31";
        } else if (q.id == "set10Yr") {
            document.getElementById("TRS_DT_From").value = dateFormat(dateCalc(dateCalc(new Date, "m", 0), "y", -10));
            document.getElementById("TRS_DT_To").value = dateFormat(dateCalc(dateCalc(new Date, "m", 6), "y", -10));
        } else if (q.id == "set20Yr") {
            document.getElementById("TRS_DT_From").value = dateFormat(dateCalc(dateCalc(new Date, "m", 0), "y", -20));
            document.getElementById("TRS_DT_To").value = dateFormat(dateCalc(dateCalc(new Date, "m", 6), "y", -20));
        } else if (q.id == "set30Yr") {
            document.getElementById("TRS_DT_From").value = dateFormat(dateCalc(dateCalc(new Date, "m", 0), "y", -30));
            document.getElementById("TRS_DT_To").value = dateFormat(dateCalc(dateCalc(new Date, "m", 6), "y", -30));
        }
        document.querySelectorAll(".filter").forEach((f, key) => {
            if (f.type === 'checkbox') {
                mytbl.hrDt.xhr.where[f.id] = f.checked ? 'Y' : 'N';
            } else {
                mytbl.hrDt.xhr.where[f.id] = f.value;
            }
        });
        mytbl.hrDt.xhr.page = 0; //필터가 바뀌면 페이지 수도 바뀌므로 첫장으로 돌려보낸다.
        mytbl.show("myTbl");
    });
});

//표시 항목 변경 팝업 구성
document.getElementById("showCol").addEventListener("click", () => {
    showColList.style.display = "block";
    document.querySelector(".showColBg").style.visibility = "visible";
});
document.querySelector(".showColBg").addEventListener("click", () => {
    showColList.style.display = "none";
    document.querySelector(".showColBg").style.visibility = "hidden";
});
const showColList = document.getElementById("showColList");

const colGroups = [
    { title: "재직상태", id: "grpTrsInfo", keys: ["TRS_TYPE", "TRS_DT", "APP_DT", "TRS_ELAPSE"], checked: "checked" },
    { title: "승급정보", id: "grpGrdInfo", keys: ["ADVANCE_DT", "ADVANCE_RNG", "GRD_GRADE", "GRD_PAY"], checked: "checked" },
    { title: "기본급", id: "grpPayInfo", keys: ["NORMAL_PAY", "LEGAL_PAY"], checked: "checked" },
    { title: "각종수당", id: "grpAdjPayInfo", keys: ["ADJUST_PAY1", "FAMILY_PAY", "ADJUST_PAY2", "ADJUST_PAY3", "ADJUST_PAY4"], checked: "checked" },
    { title: "개인정보", id: "grpPsnlInfo", keys: ["PHONE_NUM", "PSNL_NUM"], checked: "" },
    { title: "연령", id: "AGE", keys: ["AGE"], checked: "checked" },
    { title: "예상급여", id: "EXPECT_PAY", keys: ["EXPECT_PAY"], checked: "checked" },
    { title: "신자수", id: "PERSON_CNT", keys: ["PERSON_CNT"], checked: "" },
    { title: "내선번호", id: "ORG_IN_TEL", keys: ["ORG_IN_TEL"], checked: "" }
];

colGroups.forEach(group => {
    showColList.innerHTML += `
    <div>
        <input type="checkbox" class="showColGrpToggle" data-keys="${group.keys.join(',')}" id="${group.id}Toggle" ${group.checked}/>
        <label for="${group.id}Toggle">${group.title}</label>
    </div>
    `;
});

// 원래의 className 값을 보존하기 위해 초기화 시 백업
for (let i = 0; i < mytbl.hrDt.columns.length; i++) {
    const col = mytbl.hrDt.columns[i];
    // 표시 항목 변경 시 원복할 클래스에서 'hidden'을 제외하고 저장합니다.
    col.originalClassName = col.className.replace(/\bhidden\b/g, "").trim();
}

document.querySelectorAll(".showColGrpToggle").forEach(st => {
    st.addEventListener("click", (tmp) => {
        let keys = tmp.currentTarget.dataset.keys.split(',');
        let isChecked = tmp.currentTarget.checked;

        for (let i = 0; i < mytbl.hrDt.columns.length; i++) {
            const col = mytbl.hrDt.columns[i];
            if (keys.includes(col.data)) {
                if (isChecked == false) {
                    col.className = "hidden";
                } else {
                    col.className = col.originalClassName;
                }
            }
        }
        mytbl.show("myTbl");

        // 보이는 컬럼 수 계산하여 테이블 너비 조정
        let visibleColCnt = 0;
        for (let i = 0; i < mytbl.hrDt.columns.length; i++) {
            if (mytbl.hrDt.columns[i].className !== "hidden") {
                visibleColCnt++;
            }
        }

        if (visibleColCnt > 10) {
            document.getElementById("myTbl").style.width = (visibleColCnt * 100) + "px";
        } else {
            document.getElementById("myTbl").style.width = "100%";
        }
    });
});

//뒤로가기로 돌아왔을때 이전 검색 정보 필터
window.onload = function () {
    //파라미터 중 셀렉트 파타미터값이 존재한다면 기초 세팅한다.
    const url = window.location.href; // 현재 URL을 가져온다.
    const params = new URLSearchParams(new URL(url).search); // URLSearchParams 객체 생성
    const workTypeParam = params.get("WORK_TYPE");
    if (workTypeParam) {
        const selWorkType = document.getElementById("WORK_TYPE");
        let optionFound = false;
        for (let i = 0; i < selWorkType.options.length; i++) {
            if (selWorkType.options[i].value === workTypeParam) {
                optionFound = true;
                break;
            }
        }
        if (!optionFound) {
            const opt = document.createElement("option");
            opt.value = workTypeParam;
            opt.text = workTypeParam;
            opt.style.display = "none";
            selWorkType.add(opt);
        }
        selWorkType.value = workTypeParam;
        mytbl.hrDt.xhr.where["WORK_TYPE"] = workTypeParam;
    } else {
        document.getElementById("WORK_TYPE").value = "";
    }
    document.getElementById("TRS_TYPE").value = params.get("TRS_TYPE") !== null ? params.get("TRS_TYPE") : "1";
    
    if (params.get("USE_KOREAN_AGE")) {
        let sel = document.getElementById("USE_KOREAN_AGE");
        if (sel) {
            sel.value = params.get("USE_KOREAN_AGE");
            mytbl.hrDt.xhr.where["USE_KOREAN_AGE"] = sel.value;
        }
    }
    
    if (params.get("POSITION")) document.getElementById("POSITION").value = params.get("POSITION");
    if (params.get("AGE_MIN")) {
        document.getElementById("AGE_MIN").value = params.get("AGE_MIN");
        mytbl.hrDt.xhr.where["AGE_MIN"] = params.get("AGE_MIN");
    }
    if (params.get("AGE_MAX")) {
        document.getElementById("AGE_MAX").value = params.get("AGE_MAX");
        mytbl.hrDt.xhr.where["AGE_MAX"] = params.get("AGE_MAX");
    }
    if (params.get("PSNL_BIRTH_From")) document.getElementById("PSNL_BIRTH_From").value = params.get("PSNL_BIRTH_From");
    if (params.get("PSNL_BIRTH_To")) document.getElementById("PSNL_BIRTH_To").value = params.get("PSNL_BIRTH_To");
    if (params.get("TRS_DT_From")) document.getElementById("TRS_DT_From").value = params.get("TRS_DT_From");
    if (params.get("TRS_DT_To")) document.getElementById("TRS_DT_To").value = params.get("TRS_DT_To");

    if (params.get("STAT_BASE_DATE")) {
        mytbl.hrDt.xhr.where["STAT_BASE_DATE"] = params.get("STAT_BASE_DATE");
    }
    if (params.get("GENDER")) {
        mytbl.hrDt.xhr.where["GENDER"] = params.get("GENDER");
    }
    if (params.get("GRD_GRADE")) {
        document.getElementById("GRD_GRADE_From").value = params.get("GRD_GRADE");
        document.getElementById("GRD_GRADE_To").value = params.get("GRD_GRADE");
    }
    if (params.get("GRD_GRADE_From")) document.getElementById("GRD_GRADE_From").value = params.get("GRD_GRADE_From");
    if (params.get("GRD_GRADE_To")) document.getElementById("GRD_GRADE_To").value = params.get("GRD_GRADE_To");

    if (params.get("GRD_PAY")) {
        document.getElementById("GRD_PAY_From").value = params.get("GRD_PAY");
        document.getElementById("GRD_PAY_To").value = params.get("GRD_PAY");
    }
    if (params.get("GRD_PAY_From")) document.getElementById("GRD_PAY_From").value = params.get("GRD_PAY_From");
    if (params.get("GRD_PAY_To")) document.getElementById("GRD_PAY_To").value = params.get("GRD_PAY_To");
    if (params.get("HAS_PAY")) {
        mytbl.hrDt.xhr.where["HAS_PAY"] = params.get("HAS_PAY");
    }

    if (params.get("STAT_MODE")) {
        mytbl.hrDt.xhr.where["STAT_MODE"] = params.get("STAT_MODE");
        mytbl.hrDt.xhr.where["STAT_TARGET"] = params.get("STAT_TARGET");
        mytbl.hrDt.xhr.where["STAT_ORG_CD"] = params.get("STAT_ORG_CD");
        mytbl.hrDt.xhr.where["STAT_CAT"] = params.get("STAT_CAT");
    }

    if (params.get("USE_FIRST_TRS")) {
        mytbl.hrDt.xhr.where["USE_FIRST_TRS"] = params.get("USE_FIRST_TRS");
    }

    //파라미터 기초세팅 종료
    setTimeout(function () { //뒤로가기에 값이 모두 바인딩 될때까지 딜레이가 존재하여 timeout을 추가함.
        document.querySelectorAll(".filter").forEach((f, key) => {
            if (f.id === 'WORK_TYPE' && workTypeParam) {
                // Keep the param value
            } else {
                if (f.type === 'checkbox') {
                    mytbl.hrDt.xhr.where[f.id] = f.checked ? 'Y' : 'N';
                } else {
                    mytbl.hrDt.xhr.where[f.id] = f.value;
                }
            }
        });
        mytbl.show('myTbl');
        mytbl.xportBind();
    }, 50);
}

// --- 사진 등록 기능 ---
let currentPhotoPsnlCd = "";
let currentPhotoFile = null;
let currentPhotoBase64 = null;

function openPhotoModal(idx) {
    currentPhotoPsnlCd = idx;
    currentPhotoFile = null;
    currentPhotoBase64 = null;
    
    document.getElementById("photoFileInput").value = "";
    document.getElementById("photoPreviewImg").style.display = "none";
    document.getElementById("photoPreviewPlaceholder").style.display = "inline-block";
    
    // 기존 이미지가 있다면 DOM에서 확인 후 미리보기에 표시
    const avatarImg = document.getElementById("profileAvatarImg");
    if (avatarImg && avatarImg.src && avatarImg.src.indexOf("assets/photos") !== -1 && avatarImg.style.display !== "none") {
        document.getElementById("photoPreviewImg").src = avatarImg.src;
        document.getElementById("photoPreviewImg").style.display = "block";
        document.getElementById("photoPreviewPlaceholder").style.display = "none";
    }

    document.getElementById("photoModal").style.display = "flex";
}

document.getElementById("photoFileInput").addEventListener("change", function(e) {
    if(e.target.files && e.target.files[0]) {
        const file = e.target.files[0];
        currentPhotoFile = file;
        currentPhotoBase64 = null;
        
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById("photoPreviewImg").src = ev.target.result;
            document.getElementById("photoPreviewImg").style.display = "block";
            document.getElementById("photoPreviewPlaceholder").style.display = "none";
        };
        reader.readAsDataURL(file);
    }
});

document.addEventListener("paste", function(e) {
    if (document.getElementById("photoModal").style.display === "none" || !document.getElementById("photoModal").style.display) return;
    
    const items = (e.clipboardData || e.originalEvent.clipboardData).items;
    for (let index in items) {
        const item = items[index];
        if (item.kind === 'file' && item.type.indexOf('image/') !== -1) {
            const blob = item.getAsFile();
            const reader = new FileReader();
            reader.onload = function(event) {
                currentPhotoBase64 = event.target.result;
                currentPhotoFile = null;
                document.getElementById("photoPreviewImg").src = currentPhotoBase64;
                document.getElementById("photoPreviewImg").style.display = "block";
                document.getElementById("photoPreviewPlaceholder").style.display = "none";
            };
            reader.readAsDataURL(blob);
        }
    }
});

document.getElementById("savePhotoBtn").addEventListener("click", async function() {
    if (!currentPhotoPsnlCd) return;
    if (!currentPhotoFile && !currentPhotoBase64) {
        alert("업로드할 이미지를 선택하거나 붙여넣어주세요.");
        return;
    }
    
    const formData = new FormData();
    formData.append("key", API_TOKEN);
    formData.append("PSNL_CD", currentPhotoPsnlCd);
    
    if (currentPhotoFile) {
        formData.append("photoFile", currentPhotoFile);
    } else if (currentPhotoBase64) {
        formData.append("photoBase64", currentPhotoBase64);
    }
    
    try {
        const res = await fetch(`${DIR_ROOT}/sys/uploadPhoto.php`, {
            method: 'POST',
            body: formData
        }).then(r => r.json());
        
        if (res.success) {
            alert("사진이 등록되었습니다.");
            document.getElementById("photoModal").style.display = "none";
            trDataXHR(currentPhotoPsnlCd); // 프로필 다시 로드하여 이미지 갱신
        } else {
            alert("업로드 실패: " + res.message);
        }
    } catch(e) {
        console.error(e);
        alert("업로드 중 오류가 발생했습니다.");
    }
});

function showAllPtt() {
    document.querySelectorAll('.ptt-extra-row').forEach(el => el.style.display = 'block');
    const btn = document.getElementById('btnPttMore');
    if (btn) btn.style.display = 'none';
}
