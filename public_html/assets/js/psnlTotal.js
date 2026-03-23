//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    xhr: {
        url: DIR_ROOT + '/sys/psnlTotal.php',
        columXHR: '',
        key: psnlKey.value, //api 호출할 보안 개인인증키
        where: {
            TRS_TYPE: '1', //filter 값 변동 전 초기조건값으로 재직구분이 '재직'인 데이터만
            ORG_NM: document.getElementById("ORG_NM").value,
            PSNL_NM: document.getElementById("PSNL_NM").value,
            BAPT_NM: document.getElementById("BAPT_NM").value,
            POSITION: document.getElementById("POSITION").value,
            PHONE_NUM: document.getElementById("PHONE_NUM").value,
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
    const ids = ["mdBdOrgNm", "mdBdPsnlNm", "mdBdBaptNm", "mdBdPsnlNum", "mdBdPhoneNum", "mdBdPosition", "mdBdTrsType", "mdBdTrsDt", "mdBdWorkType", "mdBdGrdPay", "mdBdAdvDt", "mdBdAdvRng", "fmlTbl", "adjTbl", "opiTbl"];
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = "";
    });

    try {
        // 1. 기본 정보 조회
        const res1 = await fetch(`${DIR_ROOT}/sys/psnlTotal.php?key=${psnlKey.value}&PSNL_CD=${idx}&CRUD=R`).then(r => r.json());
        const data1 = res1.data;
        if (!data1) return;

        const row = data1[0];
        // UI 업데이트
        if (row.POSITION == "가사사용인") {
            document.querySelectorAll(".modalFooter button").forEach(ftBtn => ftBtn.style.display = "none");
            document.getElementById("goPsnlListBtn").style.display = "inline-block";
            document.getElementById("goTrsListBtn").style.display = "inline-block";
            document.getElementById("goPttListBtn").style.display = "inline-block";
        } else {
            document.querySelectorAll(".modalFooter button").forEach(ftBtn => ftBtn.style.display = "inline-block");
            document.getElementById("goPttListBtn").style.display = "none";
        }

        document.getElementById("mdBdOrgNm").innerHTML = row.ORG_NM;
        document.getElementById("mdBdPsnlNm").innerHTML = row.PSNL_NM;
        document.getElementById("mdBdBaptNm").innerHTML = row.BAPT_NM;
        document.getElementById("mdBdPsnlNum").innerHTML = row.PSNL_NUM;
        document.getElementById("mdBdPhoneNum").innerHTML = row.PHONE_NUM;
        document.getElementById("mdBdTrsType").innerHTML = row.TRS_TYPE;
        document.getElementById("mdBdPosition").innerHTML = row.POSITION;
        document.getElementById("mdBdTrsDt").innerHTML = row.TRS_DT + (row.TRS_TYPE === '퇴사' ? "(퇴)" : "");
        document.getElementById("mdBdWorkType").innerHTML = row.WORK_TYPE;
        if (row.GRD_GRADE) document.getElementById("mdBdGrdPay").innerHTML = row.GRD_GRADE + "급 " + row.GRD_PAY + "호";
        document.getElementById("mdBdAdvDt").innerHTML = row.ADVANCE_DT;
        document.getElementById("mdBdAdvRng").innerHTML = row.ADVANCE_RNG;
        document.getElementById("mdBdOrgInTel").innerHTML = "본당 내선번호 : " + row.ORG_IN_TEL + " / 전화번호 : " + row.ORG_OUT_TEL;

        // 버튼 이벤트 바인딩
        document.getElementById("goPsnlListBtn").onclick = () => { location.href = DIR_ROOT + "/psnlList?PSNL_NM=" + row.PSNL_NM + "&BAPT_NM=" + row.BAPT_NM + "&ORG_NM=" + row.ORG_NM + "&ORG_CD=" + row.ORG_CD };
        document.getElementById("goTrsListBtn").onclick = () => { location.href = DIR_ROOT + "/trsList?PSNL_CD=" + idx + "&PSNL_NM=" + row.PSNL_NM + "&POSITION=" + row.POSITION + "&ORG_NM=" + row.ORG_NM + "&ORG_CD=" + row.ORG_CD };
        document.getElementById("goGrdListBtn").onclick = () => { location.href = DIR_ROOT + "/grdList?PSNL_CD=" + idx + "&PSNL_NM=" + row.PSNL_NM + "&POSITION=" + row.POSITION + "&ORG_NM=" + row.ORG_NM + "&ORG_CD=" + row.ORG_CD };
        document.getElementById("goFmlListBtn").onclick = () => { location.href = DIR_ROOT + "/fmlList?PSNL_CD=" + idx + "&PSNL_NM=" + row.PSNL_NM + "&POSITION=" + row.POSITION + "&ORG_NM=" + row.ORG_NM + "&ORG_CD=" + row.ORG_CD };
        document.getElementById("goAdjListBtn").onclick = () => { location.href = DIR_ROOT + "/adjList?PSNL_CD=" + idx + "&PSNL_NM=" + row.PSNL_NM + "&POSITION=" + row.POSITION + "&ORG_NM=" + row.ORG_NM + "&ORG_CD=" + row.ORG_CD };
        document.getElementById("goInsListBtn").onclick = () => { location.href = DIR_ROOT + "/insList?PSNL_CD=" + idx + "&PSNL_NM=" + row.PSNL_NM + "&POSITION=" + row.POSITION + "&ORG_NM=" + row.ORG_NM + "&ORG_CD=" + row.ORG_CD };
        document.getElementById("goOpiListBtn").onclick = () => { location.href = DIR_ROOT + "/opiList?PSNL_CD=" + idx + "&PSNL_NM=" + row.PSNL_NM + "&POSITION=" + row.POSITION + "&ORG_NM=" + row.ORG_NM + "&ORG_CD=" + row.ORG_CD };
        document.getElementById("goMPayListBtn").onclick = () => { location.href = DIR_ROOT + "/mpayList?PSNL_CD=" + idx + "&PSNL_NM=" + row.PSNL_NM + "&POSITION=" + row.POSITION + "&ORG_NM=" + row.ORG_NM + "&ORG_CD=" + row.ORG_CD + "&MPAY_YEAR=" + (row.ADVANCE_DT ? row.ADVANCE_DT.substr(0, 4) : "") };
        document.getElementById("goPttListBtn").onclick = () => { location.href = DIR_ROOT + "/pttList?PSNL_CD=" + idx + "&PSNL_NM=" + row.PSNL_NM + "&POSITION=" + row.POSITION + "&ORG_NM=" + row.ORG_NM + "&ORG_CD=" + row.ORG_CD };

        // 2. 가족 정보 조회
        const res4 = await fetch(`${DIR_ROOT}/sys/fmlList.php?key=${psnlKey.value}&PSNL_CD=${idx}&CRUD=R`).then(r => r.json());
        if (res4.data) {
            let tmpStr = `<ul class="clBg5"><li class="th"><span>가족성명</span></li><li class="th"><span>관계</span></li><li class="th"><span>생년월일</span></li><li class="th"><span>상세정보</span></li><li class="clearB"></li></ul>`;
            res4.data.forEach(f => {
                tmpStr += `<ul class="clBgW"><li class="td"><span>${f.FML_NM}</span></li><li class="td"><span>${f.FML_RELATION}</span></li><li class="td"><span>${f.FML_BIRTH}</span></li><li class="td"><span>${f.FML_DTL}</span></li><li class="clearB"></li></ul>`;
            });
            document.getElementById("fmlTbl").innerHTML = tmpStr;
        }

        // 3. 제수당 정보 조회
        const res5 = await fetch(`${DIR_ROOT}/sys/adjList.php?key=${psnlKey.value}&PSNL_CD=${idx}&CRUD=R`).then(r => r.json());
        if (res5.data) {
            let tmpStr = `<ul class="clBg5"><li class="th"><span>수당타입</span></li><li class="th"><span>명칭</span></li><li class="th"><span>등급</span></li><li class="th"><span>수당금액</span></li><li class="clearB"></li></ul>`;
            res5.data.forEach(a => {
                tmpStr += `<ul class="clBgW"><li class="td"><span>${a.ADJ_TYPE}</span></li><li class="td"><span>${a.ADJ_NM}</span></li><li class="td"><span>${a.ADJ_LEVEL}</span></li><li class="td"><span>${a.ADJ_PAY}</span></li><li class="clearB"></li></ul>`;
            });
            document.getElementById("adjTbl").innerHTML = tmpStr;
        }

        // 4. 상벌/평가 정보 조회
        const res6 = await fetch(`${DIR_ROOT}/sys/opiList.php?key=${psnlKey.value}&PSNL_CD=${idx}&CRUD=R`).then(r => r.json());
        if (res6.data) {
            let tmpStr = `<ul class="clBg5"><li class="th"><span>타입</span></li><li class="th"><span>날짜</span></li><li class="th"><span>평가자</span></li><li class="th"><span>내용</span></li><li class="clearB"></li></ul>`;
            const opiTypes = { 1: "긍정", 2: "부정", 3: "포상", 4: "징계" };
            res6.data.forEach(o => {
                tmpStr += `<ul class="clBgW"><li class="td"><span>${opiTypes[o.OPI_TYPE] || ""}</span></li><li class="td"><span>${o.OPI_DT}</span></li><li class="td"><span>${o.OPI_PERSON}</span></li><li class="td"><span>${o.OPI_DTL}</span></li><li class="clearB"></li></ul>`;
            });
            document.getElementById("opiTbl").innerHTML = tmpStr;
        }
    } catch (e) {
        console.error("Data load failed:", e);
    }
}

//검색 필터링을 위한 코드
document.querySelectorAll(".filter").forEach((f, key) => {
    f.addEventListener("change", () => {
        mytbl.hrDt.xhr.where[f.id] = f.value;
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

            // 나이가 많을수록 생년월일은 작아짐 (과거)
            // 최대 나이(MAX)가 33이면, 생년월일 From은 (현재년도 - 33)년생부터가 됨.
            if (maxAgeVal !== "") {
                finalBirthFrom = (currentYear - parseInt(maxAgeVal)) + "-01-01";
            }

            // 나이가 적을수록 생년월일은 커짐 (최근)
            // 최소 나이(MIN)가 60이면, 생년월일 To는 (현재년도 - 60)년생까지가 됨.
            if (minAgeVal !== "") {
                finalBirthTo = (currentYear - parseInt(minAgeVal)) + "-12-31";
            }

            document.getElementById("PSNL_BIRTH_From").value = finalBirthFrom;
            document.getElementById("PSNL_BIRTH_To").value = finalBirthTo;

            mytbl.hrDt.xhr.where["PSNL_BIRTH_From"] = finalBirthFrom;
            mytbl.hrDt.xhr.where["PSNL_BIRTH_To"] = finalBirthTo;

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
            mytbl.hrDt.xhr.where[f.id] = f.value;
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
    
    if (params.get("POSITION")) document.getElementById("POSITION").value = params.get("POSITION");
    if (params.get("AGE_MIN")) document.getElementById("AGE_MIN").value = params.get("AGE_MIN");
    if (params.get("AGE_MAX")) document.getElementById("AGE_MAX").value = params.get("AGE_MAX");
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
                mytbl.hrDt.xhr.where[f.id] = f.value;
            }
        });
        mytbl.show('myTbl');
        mytbl.xportBind();
    }, 50);
}
