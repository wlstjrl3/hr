// 1. 데이터 테이블 초기화
var mytbl = new hr_tbl({
    xhr: {
        url: DIR_ROOT + '/sys/certList.php',
        key: API_TOKEN,
        where: {
            EMP_NM: document.getElementById("PSNL_NM").value,
        },
        order: { column: 0, direction: 'desc' },
        page: 0,
        limit: 10,
    },
    columns: [
        { title: "발급번호", data: "ISSUE_NO", className: "" }
        , { title: "종류", data: "CERT_TYPE", className: "" }
        , { title: "대상자", data: "PSNL_NM", className: "" }
        , { title: "소속", data: "ORG_NM", className: "" }
        , { title: "이메일", data: "ORG_EMAIL", className: "" }
        , { title: "직책", data: "POSITION", className: "hidden" }
        , { title: "입사일", data: "JOIN_DT", className: "hidden" }
        , { title: "발급일자", data: "ISSUE_DT", className: "" }
        , {
            title: "현재주소",
            data: "CURR_ADDR",
            className: "",
            render: function (data) {
                if (!data) return "";
                if (data.length > 15) return data.substring(0, 15) + "...";
                return data;
            }
        }
        , { title: "비고", data: "MEMO", className: "" }
        , {
            title: "관리",
            data: "ISSUE_NO",
            render: function (data, type, row) {
                return `<button onclick="openPrintModal('${data}')" style="padding:5px 9px;">인쇄</button>
                        <button onclick="deleteCert('${data}')" style="padding:5px 9px;">삭제</button>`;
            }
        }
    ],
});

// 삭제 처리 함수
function deleteCert(issueNo) {
    if (!issueNo) return;
    if (prompt("해당 발급 내역을 삭제하시려면 '삭제'라고 입력해주세요. \n(출력된 원본과 번호 불일치 주의)") !== "삭제") return;

    fetch(DIR_ROOT + `/sys/certConfig.php?key=${API_TOKEN}&CRUD=D&ISSUE_NO=${issueNo}`)
        .then(res => res.json())
        .then(json => {
            if (json.result == "success") {
                mytbl.show('myTbl');
            } else {
                alert("삭제 실패: " + json.message);
            }
        });
}
mytbl.show('myTbl');
mytbl.xportBind();

// 행 클릭 시 수정 모달 오픈
function trDataXHR(idx) {
    openEditModal(idx);
}

// 2. 신규 발급 업무 처리
document.getElementById("newBtn").addEventListener("click", () => {
    // 모달 필드 초기화
    document.getElementById("md_ISSUE_NO").value = "";
    document.getElementById("md_EMP_NO").value = "";
    document.getElementById("md_PSNL_CD").value = "";
    document.getElementById("md_ORG_NM").value = "";
    document.getElementById("md_POSITION").value = "";
    document.getElementById("md_PSNL_NM").value = "";
    document.getElementById("md_MEMO").value = "";

    document.getElementById("md_PSNL_NM_SEARCH").closest('.modalGrp').style.display = 'block';
    document.getElementById("md_CERT_TYPE").value = "재직";
    document.getElementById("md_ORIGIN_ADDR").value = "";
    document.getElementById("md_CURR_ADDR").value = "";
    document.getElementById("md_ORG_ADDR").value = "";

    // 모달 표시
    document.getElementById("certInputModal").style.visibility = "visible";
    document.getElementById("certInputModal").style.opacity = "1";
});

// 3. 발급 데이터 저장 (C/U)
document.getElementById("modalSaveBtn").addEventListener("click", () => {
    let issueNo = document.getElementById("md_ISSUE_NO").value;
    let empNo = document.getElementById("md_EMP_NO").value;
    let certType = document.getElementById("md_CERT_TYPE").value;
    let originAddr = document.getElementById("md_ORIGIN_ADDR").value;
    let currAddr = document.getElementById("md_CURR_ADDR").value;
    let orgAddr = document.getElementById("md_ORG_ADDR").value;
    let memo = document.getElementById("md_MEMO").value;

    if (!empNo) {
        alert("먼저 사원 검색을 통해 대상 직원을 선택해주세요.");
        return;
    }
    if (!currAddr) {
        alert("주소를 입력해주세요.");
        return;
    }

    let params = `key=${API_TOKEN}&CRUD=C&ISSUE_NO=${issueNo}&EMP_NO=${empNo}&CERT_TYPE=${certType}&ORIGIN_ADDR=${encodeURIComponent(originAddr)}&CURR_ADDR=${encodeURIComponent(currAddr)}&ORG_ADDR=${encodeURIComponent(orgAddr)}&MEMO=${encodeURIComponent(memo)}`;

    fetch(DIR_ROOT + "/sys/certConfig.php?" + params)
        .then(res => res.json())
        .then(json => {
            if (json.result == "success") {
                alert("기록되었습니다. 발급번호: " + json.ISSUE_NO);
                mytbl.show('myTbl');
                modalClose();
            } else {
                alert("저장 실패: " + json.message);
            }
        });
});

// 4. 발급 내역 수정 모달 열기
function openEditModal(issueNo) {
    fetch(DIR_ROOT + `/sys/certConfig.php?key=${API_TOKEN}&CRUD=R&ISSUE_NO=${issueNo}`)
        .then(res => res.json())
        .then(json => {
            const d = json.data;
            document.getElementById("md_ISSUE_NO").value = d.ISSUE_NO;
            document.getElementById("md_EMP_NO").value = d.EMP_NO;
            document.getElementById("md_PSNL_NM_SEARCH").closest('.modalGrp').style.display = 'none'; // 수정 시 검색 숨김

            document.getElementById("md_PSNL_CD").value = d.EMP_NO;
            document.getElementById("md_ORG_NM").value = d.ORG_NM;
            document.getElementById("md_POSITION").value = d.POSITION;
            document.getElementById("md_PSNL_NM").value = d.PSNL_NM;

            document.getElementById("md_CERT_TYPE").value = d.CERT_TYPE;
            document.getElementById("md_ORIGIN_ADDR").value = d.ORIGIN_ADDR;
            document.getElementById("md_CURR_ADDR").value = d.CURR_ADDR;
            document.getElementById("md_ORG_ADDR").value = d.ORG_ADDR;
            document.getElementById("md_MEMO").value = d.MEMO || "";

            document.getElementById("certInputModal").style.visibility = "visible";
            document.getElementById("certInputModal").style.opacity = "1";
        });
}

// 5. 인쇄 모달 열기 및 데이터 매핑
// 5. 증명서 템플릿에 데이터 매핑 (인쇄/메일 공통)
function renderCertificate(d) {
    if (!d) return;

    // 1. 레이아웃 분기 및 초기화
    const paper = document.getElementById("certPaper");
    const layoutStandard = document.getElementById("layout_standard");
    const layoutCareer = document.getElementById("layout_career");
    const logoCross = document.getElementById("certLogoCross");

    // 클래스 초기화 및 부여
    paper.className = "cert-paper";
    if (d.CERT_TYPE === '퇴직') paper.classList.add("type-retire");
    else if (d.CERT_TYPE === '경력') paper.classList.add("type-career");

    // 로고 농도 및 가시성
    logoCross.style.display = "block";
    if (d.CERT_TYPE === '퇴직') logoCross.style.opacity = "0.9";
    else if (d.CERT_TYPE === '경력') logoCross.style.opacity = "0.05";
    else logoCross.style.opacity = "0.12";

    logoCross.src = DIR_ROOT + "/assets/img/certs/cert_bg_cross.png";
    document.querySelectorAll(".official-seal").forEach(s => s.src = DIR_ROOT + "/assets/img/certs/official_seal.png");

    // 2. 날짜 포맷팅 함수들
    const formatKrDate = (dateStr) => {
        if (!dateStr) return "";
        const bits = dateStr.split("-");
        return bits[0] + "년 " + parseInt(bits[1]) + "월 " + parseInt(bits[2]) + "일";
    };
    const formatDotDate = (dateStr) => {
        if (!dateStr) return "";
        return dateStr.replace(/-/g, '.');
    };

    const birthStr = (() => {
        if (d.PSNL_NUM && d.PSNL_NUM.length >= 6) {
            let yy = d.PSNL_NUM.substring(0, 2);
            let mm = d.PSNL_NUM.substring(2, 4);
            let dd = d.PSNL_NUM.substring(4, 6);
            let yearPrefix = (parseInt(yy) > 50) ? "19" : "20";
            return yearPrefix + yy + "." + mm + "." + dd + ".";
        }
        return "";
    })();

    // 3. 레이아웃별 데이터 주입
    if (d.CERT_TYPE === '경력') {
        layoutStandard.style.display = "none";
        layoutCareer.style.display = "block";

        document.getElementById("p_ISSUE_NO_C").innerText = d.ISSUE_NO.split('-').pop(); // 순번만
        // 성명 자간 처리 (경력)
        const nmC = d.PSNL_NM || "";
        const nmCLen = nmC.length;
        let nmCSpacing = "0";
        if (nmCLen === 2) nmCSpacing = "32pt";
        else if (nmCLen === 3) nmCSpacing = "13pt";
        else if (nmCLen === 4) nmCSpacing = "6pt";
        document.getElementById("p_PSNL_NM_C").innerHTML = `<span style="letter-spacing:${nmCSpacing}; margin-right:-${nmCSpacing}; font-family:'NanumMyeongjo', serif; font-size:17pt;">${nmC}</span>`;
        document.getElementById("p_BIRTH_DT_C").innerHTML = `<span style="font-family:'NanumMyeongjo', serif; font-size:17pt;">${birthStr}</span>`;
        document.getElementById("p_ADDR_C").innerText = d.CURR_ADDR || "";
        document.getElementById("p_ORIGIN_C").innerText = d.ORIGIN_ADDR || "미 기 재";

        // 경력 히스토리 동적 생성
        let hHtml = "";
        if (d.history && d.history.length > 0) {
            d.history.forEach(h => {
                hHtml += `
                    <tr style="height:15mm;">
                        <td style="border:1px solid #777; font-family:sans-serif;">${formatDotDate(h.STT_DT)}</td>
                        <td style="border:1px solid #777; font-family:sans-serif;">${formatDotDate(h.END_DT || "")}</td>
                        <td style="border:1px solid #777;">천주교 수원교구<br>${h.ORG_NM} ${h.ORG_TYPE == '1' ? '성지' : '성당'}</td>
                        <td style="border:1px solid #777;">${h.POSITION}</td>
                    </tr>
                `;
            });
        }
        // 빈 행 추가 (최소 3행 보장)
        for (let i = (d.history ? d.history.length : 0); i < 4; i++) {
            hHtml += `<tr style="height:15mm;"><td style="border:1px solid #777;"></td><td style="border:1px solid #777;"></td><td style="border:1px solid #777;"></td><td style="border:1px solid #777;"></td></tr>`;
        }
        document.getElementById("p_CAREER_LIST").innerHTML = hHtml;

        if (d.ISSUE_DT) {
            const b = d.ISSUE_DT.split("-");
            document.getElementById("p_ISSUE_DT_C").innerText = b[0] + "년 " + parseInt(b[1]) + "월 " + parseInt(b[2]) + "일";
        }
    } else {
        layoutStandard.style.display = "block";
        layoutCareer.style.display = "none";

        // 표준 레이아웃 (재직/퇴직)
        document.getElementById("p_ISSUE_NO").innerText = d.ISSUE_NO.split('-').pop();
        // 성명 자간 처리
        const p_PSNL_NM = d.PSNL_NM || "";
        const nameLen = p_PSNL_NM.length;
        let nameSpacing = "0";
        if (nameLen === 2) nameSpacing = "32pt";
        else if (nameLen === 3) nameSpacing = "13pt";
        else if (nameLen === 4) nameSpacing = "6pt";

        document.getElementById("p_PSNL_NM").innerHTML = `<span style="letter-spacing:${nameSpacing}; margin-right:-${nameSpacing}; font-family:'NanumMyeongjo', serif; font-size:17pt;">${p_PSNL_NM}</span>`;
        document.getElementById("p_BIRTH_DT").innerHTML = `<span style="font-family:'NanumMyeongjo', serif; font-size:17pt;">${birthStr}</span>`;
        document.getElementById("p_ORIGIN_ADDR").innerText = d.ORIGIN_ADDR || "미 기 재";
        document.getElementById("p_TITLE").innerText = d.CERT_TYPE;


        // 주소 분리
        const fullAddr = d.CURR_ADDR || "";
        if (fullAddr.length > 22) {
            const idx = fullAddr.indexOf(' ', 15);
            document.getElementById("p_CURR_ADDR_1").innerText = (idx > 0) ? fullAddr.substring(0, idx) : fullAddr;
            document.getElementById("p_CURR_ADDR_2").innerText = (idx > 0) ? fullAddr.substring(idx).trim() : "";
        } else {
            document.getElementById("p_CURR_ADDR_1").innerText = fullAddr;
            document.getElementById("p_CURR_ADDR_2").innerText = "";
        }

        // 본문 문구
        let bodyHtml = "";
        const joinDtKr = formatKrDate(d.JOIN_DT);
        const retireDtKr = formatKrDate(d.RETIRE_DT);

        if (d.CERT_TYPE === '재직') {
            bodyHtml = `이 사람은 ${joinDtKr} 부터 현재 까지 본 천주교 수원교구 ${d.ORG_NM} ${d.ORG_TYPE == '1' ? '' : '성당'} ${d.POSITION}으로 재직 중임을 증명함.`;
        } else {
            bodyHtml = `위 사람은 ${joinDtKr} 입사하여 <br>${retireDtKr} 퇴직하였음을 증명함.`;
        }
        document.getElementById("p_BODY_CONTENT").innerHTML = bodyHtml;

        if (d.ISSUE_DT) {
            document.getElementById("p_ISSUE_DT").innerText = d.ISSUE_DT.replace(/-/g, '.') + ".";
        }
    }
}

// 6. 인쇄 모달 열기
function openPrintModal(issueNo) {
    fetch(DIR_ROOT + `/sys/certConfig.php?key=${API_TOKEN}&CRUD=R&ISSUE_NO=${issueNo}`)
        .then(res => res.json())
        .then(json => {
            const d = json.data;
            if (!d) return;

            renderCertificate(d);

            document.getElementById("certPrintModal").style.visibility = "visible";
            document.getElementById("certPrintModal").style.opacity = "1";
        });
}

// 7. 모달 내부 삭제 버튼 연결
document.getElementById("modalDelBtn").addEventListener("click", () => {
    let issueNo = document.getElementById("md_ISSUE_NO").value;
    deleteCert(issueNo);
});

// 8. 모달 내부 이메일 발송 버튼 연결
document.getElementById("modalEmailBtn").addEventListener("click", sendEmail);

// 9. 이메일 발송 처리
async function sendEmail() {
    const issueNo = document.getElementById("md_ISSUE_NO").value;
    if (!issueNo) {
        alert("먼저 증명서를 저장 또는 선택해주세요.");
        return;
    }

    try {
        // 1. 데이터 조회 (메일 주소 포함)
        const resp = await fetch(DIR_ROOT + `/sys/certConfig.php?key=${API_TOKEN}&CRUD=R&ISSUE_NO=${issueNo}`);
        const json = await resp.json();
        const d = json.data;

        // [수정]: 조직 이메일을 기본값으로 보여주되, 수동 입력이나 수정이 가능하도록 함
        let defaultEmail = d.CURR_ORG_EMAIL || "";
        let targetEmail = prompt("발송할 이메일 주소를 확인하거나 수동으로 입력해주세요.", defaultEmail);

        if (!targetEmail) return; // 입력을 취소하거나 빈값인 경우 중단

        if (!targetEmail.includes("@")) {
            alert("올바른 이메일 형식이 아닙니다.");
            return;
        }

        if (!confirm(`${targetEmail} 로 증명서 파일을 발송하시겠습니까?`)) return;

        // [캡처용 모달 활성화]
        const printModal = document.getElementById("certPrintModal");
        printModal.style.visibility = "visible";
        printModal.style.opacity = "1";
        printModal.style.zIndex = "10000";

        // 2. 증명서 렌더링 (캡처용)
        renderCertificate(d);
        
        // 브라우저 렌더링 대기 (이미지 로드 등 충분한 시간 부여)
        await new Promise(r => setTimeout(r, 800));

        // 3. html2canvas로 캡처
        const canvas = await html2canvas(document.getElementById("certPaper"), {
            scale: 1.5,           // 해상도 조절 (너무 높으면 전송 실패 가능성)
            useCORS: true,      // 외부 이미지 로드 허용
            backgroundColor: "#ffffff",
            scrollY: -window.scrollY // 스크롤 위치 보정
        });

        // [PDF 변환]
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const imgData = canvas.toDataURL("image/jpeg", 0.8); // PNG -> JPEG (용량 최적화)
        const imgProps = pdf.getImageProperties(imgData);
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
        
        pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
        const pdfBase64 = pdf.output('datauristring');

        if (!pdfBase64 || pdfBase64.length < 100) {
            throw new Error("PDF 생성에 실패했습니다. (데이터 누락)");
        }

        // 캡처 완료 후 모달 숨기기
        printModal.style.visibility = "hidden";
        printModal.style.opacity = "0";
        printModal.style.zIndex = "100";

        // 4. 백엔드로 전송
        const formData = new FormData();
        formData.append("key", API_TOKEN);
        formData.append("ISSUE_NO", issueNo);
        formData.append("EMAIL", targetEmail);
        formData.append("ORG_NM", d.CURR_ORG_NM);
        formData.append("PSNL_NM", d.PSNL_NM);
        formData.append("CERT_TYPE", d.CERT_TYPE);
        formData.append("PDF_DATA", pdfBase64); // IMAGE_DATA -> PDF_DATA

        const emailResp = await fetch(DIR_ROOT + "/sys/sendCertEmail.php", {
            method: "POST",
            body: formData
        });
        const emailResult = await emailResp.json();

        if (emailResult.result === "success") {
            alert("이메일 발송이 성공적으로 완료되었습니다.");
            mytbl.show('myTbl'); // 목록 즉시 새로고침
            modalClose();       // 입력 모달 닫기
        } else {
            alert("이메일 발송 실패: " + emailResult.message);
        }

    } catch (e) {
        console.error(e);
        alert("이메일 발송 중 오류가 발생했습니다: " + e.message);
        document.getElementById("certPrintModal").style.visibility = "hidden";
    }
}

// 7. 검색 필터링 이벤트 (hr_tbl.js 의존)
document.querySelectorAll(".filter").forEach(f => {
    f.addEventListener("change", () => {
        mytbl.hrDt.xhr.where = {
            EMP_NM: document.getElementById("PSNL_NM").value,
            CERT_TYPE: document.getElementById("CERT_TYPE_SEARCH").value,
            ISSUE_NO: document.getElementById("ISSUE_NO_SEARCH").value,
            ISSUE_DT_STT: document.getElementById("ISSUE_DT_STT").value,
            ISSUE_DT_END: document.getElementById("ISSUE_DT_END").value
        };
        mytbl.hrDt.xhr.page = 0;
        mytbl.show("myTbl");
    });
});

// 성명 검색창 엔터 시 테이블 필터링
document.getElementById("PSNL_NM").addEventListener("keydown", (e) => {
    if (e.keyCode === 13) {
        myTblRefresh();
    }
});

// [모달 내 검색] 성명 검색창 엔터 이벤트
document.getElementById("md_PSNL_NM_SEARCH").addEventListener("keydown", (e) => {
    if (e.keyCode === 13) {
        document.getElementById("md_PsnlSearchBtn").click();
    }
});

// [모달 내 검색] 버튼 클릭 이벤트
document.getElementById("md_PsnlSearchBtn").addEventListener("click", () => {
    const searchVal = document.getElementById("md_PSNL_NM_SEARCH").value;
    window.open(DIR_ROOT + `/components/psnlPopup.php?SEARCH_FROM=MODAL&VAL=${encodeURIComponent(searchVal)}`, '사원 검색', 'width=500, height=500');
});

// 8. 사원 검색 후 호출되는 글로벌 함수 (사원 팝업에서 사용)
function myTblRefresh() {
    // 모달 데이터는 hr_tbl.js / psnlPopup.js 에서 모달 내 md_ 필드들로 직접 바인딩되므로 
    // 여기서 메인 필터 값을 읽어와서 덮어쓰지 않습니다. (이중 바인딩 및 빈 값 초기화 방지)

    // 메인 대장 테이블만 새로고침 (상단 성명 검색 필터는 절대 건드리지 않음)
    mytbl.show("myTbl");
}

// 9. 날짜 입력 하이픈 자동 처리
document.querySelectorAll(".dateBox").forEach(dtBox => {
    dtBox.onkeyup = function (event) {
        this.value = autoHypenDate(this.value.trim());
    }
});

// 공통 모달 닫기
function modalClose() {
    document.querySelectorAll(".modalForm").forEach(m => {
        m.style.visibility = "hidden";
        m.style.opacity = "0";
    });
}

// ============================================================
// 수기 수정 패널 로직
// ============================================================

// 현재 열린 증명서 데이터를 보관 (초기화 시 복원용)
let _currentCertData = null;
let _isManualEdited = false;
let _manualEditLog = "";

// openPrintModal 래핑: 데이터 보관 및 패널 초기화
const _origOpenPrintModal = openPrintModal;
window.openPrintModal = function(issueNo) {
    fetch(DIR_ROOT + `/sys/certConfig.php?key=${API_TOKEN}&CRUD=R&ISSUE_NO=${issueNo}`)
        .then(res => res.json())
        .then(json => {
            const d = json.data;
            if (!d) return;
            _currentCertData = d;
            _isManualEdited = false;
            _manualEditLog = "";
            renderCertificate(d);
            manualPanel_init(d);
            document.getElementById("certPrintModal").style.visibility = "visible";
            document.getElementById("certPrintModal").style.opacity = "1";
        });
};

// 패널 초기화: 종류에 맞는 서브패널 표시 + 입력값 세팅
function manualPanel_init(d) {
    const isCareer = (d.CERT_TYPE === '경력');
    document.getElementById("manualPanel_standard").style.display = isCareer ? "none" : "block";
    document.getElementById("manualPanel_career").style.display  = isCareer ? "block" : "none";

    if (!isCareer) {
        // 재직/퇴직 - 인사 시스템 값을 기본값으로 채워넣기
        const formatKrDate = (dateStr) => {
            if (!dateStr) return "";
            const b = dateStr.split("-");
            return b[0] + "년 " + parseInt(b[1]) + "월 " + parseInt(b[2]) + "일";
        };
        document.getElementById("m_JOIN_DT").value    = formatKrDate(d.JOIN_DT);
        document.getElementById("m_RETIRE_DT").value  = formatKrDate(d.RETIRE_DT);

        // 재직이면 퇴직일 입력란 흐리게
        const retireLbl = document.getElementById("m_RETIRE_DT_label");
        const retireInput = document.getElementById("m_RETIRE_DT");
        if (d.CERT_TYPE === '재직') {
            retireLbl.style.opacity = "0.35";
            retireInput.style.opacity = "0.35";
            retireInput.placeholder = "재직 중 - 불필요";
        } else {
            retireLbl.style.opacity = "1";
            retireInput.style.opacity = "1";
            retireInput.placeholder = "예) 2024년 12월 31일";
        }

        const orgNm  = d.ORG_NM || "";
        const orgType = d.ORG_TYPE == '1' ? '' : '성당';
        const pos    = d.POSITION || "";
        document.getElementById("m_ORG_POSITION").value = orgNm ? `${orgNm} ${orgType} ${pos}`.trim() : pos;
        document.getElementById("m_BODY_TEXT").value = "";

    } else {
        // 경력 - 각 히스토리 행마다 수정 인풋 생성
        const formatDot = (s) => s ? s.replace(/-/g, '.') : "";
        const rows = d.history || [];
        const container = document.getElementById("manualCareerRows");
        container.innerHTML = "";
        rows.forEach((h, i) => {
            const row = document.createElement("div");
            row.style.cssText = "display:flex; gap:8px; align-items:center; margin-bottom:6px; flex-wrap:wrap;";
            row.innerHTML = `
                <span style="font-size:11px; color:#555; white-space:nowrap;">${i+1}행</span>
                <label style="font-size:11px; color:#666; white-space:nowrap;">부터</label>
                <input id="m_career_stt_${i}" value="${formatDot(h.STT_DT)}" style="width:130px; font-size:12px; padding:3px 6px; border:1px solid #ccc; border-radius:4px;" placeholder="YYYY.MM.DD">
                <label style="font-size:11px; color:#666; white-space:nowrap;">까지</label>
                <input id="m_career_end_${i}" value="${formatDot(h.END_DT || '')}" style="width:130px; font-size:12px; padding:3px 6px; border:1px solid #ccc; border-radius:4px;" placeholder="YYYY.MM.DD (재직중 빈칸)">
                <label style="font-size:11px; color:#666; white-space:nowrap;">부서</label>
                <input id="m_career_org_${i}" value="${h.ORG_NM || ''}" style="width:150px; font-size:12px; padding:3px 6px; border:1px solid #ccc; border-radius:4px;" placeholder="부서명">
                <label style="font-size:11px; color:#666; white-space:nowrap;">직위</label>
                <input id="m_career_pos_${i}" value="${h.POSITION || ''}" style="width:120px; font-size:12px; padding:3px 6px; border:1px solid #ccc; border-radius:4px;" placeholder="직위/직급">
            `;
            container.appendChild(row);
        });
    }
}

// 패널 펼치기/접기
document.getElementById("manualToggleBtn").addEventListener("click", () => {
    const body = document.getElementById("manualEditBody");
    const btn  = document.getElementById("manualToggleBtn");
    if (body.style.display === "none") {
        body.style.display = "block";
        btn.textContent = "접기 ▲";
    } else {
        body.style.display = "none";
        btn.textContent = "펼치기 ▼";
    }
});

// 적용 버튼: 수기 입력값을 반영하여 미리보기 갱신
document.getElementById("manualApplyBtn").addEventListener("click", () => {
    if (!_currentCertData) return;
    const d = JSON.parse(JSON.stringify(_currentCertData)); // 깊은 복사
    _isManualEdited = true;
    _manualEditLog = "[수기수정인쇄] ";

    if (d.CERT_TYPE === '경력') {
        _manualEditLog += "경력 기간/부서/직위 수정됨";
        // 경력: 각 행의 기간/부서/직위 덮어쓰기
        const rows = d.history || [];
        rows.forEach((h, i) => {
            const sttEl = document.getElementById(`m_career_stt_${i}`);
            const endEl = document.getElementById(`m_career_end_${i}`);
            const orgEl = document.getElementById(`m_career_org_${i}`);
            const posEl = document.getElementById(`m_career_pos_${i}`);
            if (sttEl) h.STT_DT = sttEl.value.replace(/\./g, '-'); // YYYY-MM-DD 형식 복원
            if (endEl) h.END_DT = endEl.value ? endEl.value.replace(/\./g, '-') : "";
            if (orgEl) h.ORG_NM = orgEl.value;
            if (posEl) h.POSITION = posEl.value;
        });
        renderCertificate(d);

    } else {
        // 재직/퇴직: 본문 직접 또는 항목별 조합
        const bodyText = document.getElementById("m_BODY_TEXT").value.trim();
        if (bodyText) {
            _manualEditLog += "본문 전체 직접입력 적용";
            // 본문 전체 직접 입력 우선 적용
            renderCertificate(d);
            document.getElementById("p_BODY_CONTENT").innerHTML = bodyText.replace(/\n/g, '<br>');
        } else {
            const joinDt   = document.getElementById("m_JOIN_DT").value.trim();
            const retireDt = document.getElementById("m_RETIRE_DT").value.trim();
            const orgPos   = document.getElementById("m_ORG_POSITION").value.trim();
            _manualEditLog += `입사:${joinDt||'빈칸'} / 퇴직:${retireDt||'빈칸'} / 소속:${orgPos||'빈칸'}`;

            renderCertificate(d); // 기본 렌더 먼저

            let bodyHtml = "";
            if (d.CERT_TYPE === '재직') {
                const orgPart = orgPos ? orgPos : `${d.ORG_NM || ""} ${d.ORG_TYPE == '1' ? '' : '성당'} ${d.POSITION || ""}`.trim();
                bodyHtml = `이 사람은 ${joinDt || "（　　）"} 부터 현재 까지 본 천주교 수원교구 ${orgPart}으로 재직 중임을 증명함.`;
            } else {
                const orgPart = orgPos ? orgPos : `${d.ORG_NM || ""} ${d.ORG_TYPE == '1' ? '' : '성당'} ${d.POSITION || ""}`.trim();
                bodyHtml = `위 사람은 ${joinDt || "（　　）"} 입사하여<br>${retireDt || "（　　）"} 퇴직하였음을 증명함.`;
                if (orgPart) bodyHtml += `<br>(${orgPart})`;
            }
            document.getElementById("p_BODY_CONTENT").innerHTML = bodyHtml;
        }
    }
});

// 초기화 버튼: 원본 인사 데이터로 복원
document.getElementById("manualResetBtn").addEventListener("click", () => {
    if (!_currentCertData) return;
    _isManualEdited = false;
    _manualEditLog = "";
    renderCertificate(_currentCertData);
    manualPanel_init(_currentCertData);
});

// 인쇄 버튼 클릭 시 처리 (수기수정 반영 여부 로깅)
function handlePrint() {
    if (_isManualEdited && _currentCertData) {
        let currentMemo = _currentCertData.MEMO || "";
        let timeStr = new Date().toISOString().substring(2, 10).replace(/-/g, ''); // YYMMDD
        let newMemo = currentMemo;
        if (currentMemo) {
            newMemo += " | " + timeStr + " " + _manualEditLog;
        } else {
            newMemo = timeStr + " " + _manualEditLog;
        }

        let params = new URLSearchParams({
            key: API_TOKEN,
            CRUD: 'C',
            ISSUE_NO: _currentCertData.ISSUE_NO,
            EMP_NO: _currentCertData.EMP_NO,
            CERT_TYPE: _currentCertData.CERT_TYPE,
            ORIGIN_ADDR: _currentCertData.ORIGIN_ADDR || '',
            CURR_ADDR: _currentCertData.CURR_ADDR || '',
            ORG_ADDR: _currentCertData.ORG_ADDR || '',
            MEMO: newMemo
        });

        fetch(DIR_ROOT + "/sys/certConfig.php", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params.toString()
        }).then(res => res.json()).then(json => {
            if (json.result == "success") {
                _currentCertData.MEMO = newMemo;
                mytbl.show('myTbl'); // 목록 갱신
            }
            window.print();
        }).catch(e => {
            console.error(e);
            window.print();
        });
    } else {
        window.print();
    }
}