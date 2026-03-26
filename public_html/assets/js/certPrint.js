// 1. 데이터 테이블 초기화
var mytbl = new hr_tbl({
    xhr: {
        url: DIR_ROOT + '/sys/certList.php',
        key: psnlKey.value,
        where: {
            EMP_NM: document.getElementById("PSNL_NM").value,
        },
        order: { column: 4, direction: 'desc' },
        page: 0,
        limit: 10,
    },
    columns: [
        { title: "발급번호", data: "ISSUE_NO", className: "" }
        , { title: "종류", data: "CERT_TYPE", className: "" }
        , { title: "대상자", data: "PSNL_NM", className: "" }
        , { title: "소속", data: "ORG_NM", className: "" }
        , { title: "발급일자", data: "ISSUE_DT", className: "" }
        , { 
            title: "관리", 
            data: "ISSUE_NO", 
            render: function(data, type, row) {
                return `<button onclick="openPrintModal('${data}')" class="pddSS clBg2 clW rndCorner pointer">인쇄</button>
                        <button onclick="openEditModal('${data}')" class="pddSS clBg5 cl2 rndCorner pointer">수정</button>
                        <button onclick="deleteCert('${data}')" class="pddSS clBg1 clW rndCorner pointer">삭제</button>`;
            }
        }
    ],
});

// 삭제 처리 함수
function deleteCert(issueNo) {
    if(!issueNo) return;
    if(!confirm("해당 발급 내역을 삭제하시겠습니까? (출력된 원본과 번호 불일치 주의)")) return;

    fetch(DIR_ROOT + `/sys/certConfig.php?key=${psnlKey.value}&CRUD=D&ISSUE_NO=${issueNo}`)
        .then(res => res.json())
        .then(json => {
            if(json.result == "success") {
                mytbl.show('myTbl');
            } else {
                alert("삭제 실패: " + json.message);
            }
        });
}
mytbl.show('myTbl');
mytbl.xportBind();

// 2. 신규 발급 업무 처리
document.getElementById("newBtn").addEventListener("click", () => {
    if (document.getElementById("PSNL_CD").value.length < 1) {
        alert("먼저 사원 검색을 통해 대상 직원을 선택해주세요.");
        return false;
    }
    
    // 모달 필드 초기화
    document.getElementById("md_ISSUE_NO").value = "";
    document.getElementById("md_EMP_NO").value = document.getElementById("PSNL_CD").value;
    document.getElementById("mdTargetText").innerText = document.getElementById("ORG_NM").value + " " + document.getElementById("POSITION").value + " " + document.getElementById("PSNL_NM").value;
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

    if (!currAddr) {
        alert("주소를 입력해주세요.");
        return;
    }

    let params = `key=${psnlKey.value}&CRUD=C&ISSUE_NO=${issueNo}&EMP_NO=${empNo}&CERT_TYPE=${certType}&ORIGIN_ADDR=${encodeURIComponent(originAddr)}&CURR_ADDR=${encodeURIComponent(currAddr)}&ORG_ADDR=${encodeURIComponent(orgAddr)}`;
    
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
    fetch(DIR_ROOT + `/sys/certConfig.php?key=${psnlKey.value}&CRUD=R&ISSUE_NO=${issueNo}`)
        .then(res => res.json())
        .then(json => {
            const d = json.data;
            document.getElementById("md_ISSUE_NO").value = d.ISSUE_NO;
            document.getElementById("md_EMP_NO").value = d.EMP_NO;
            document.getElementById("mdTargetText").innerText = d.ORG_NM + " " + d.POSITION + " " + d.PSNL_NM;
            document.getElementById("md_CERT_TYPE").value = d.CERT_TYPE;
            document.getElementById("md_ORIGIN_ADDR").value = d.ORIGIN_ADDR;
            document.getElementById("md_CURR_ADDR").value = d.CURR_ADDR;
            document.getElementById("md_ORG_ADDR").value = d.ORG_ADDR;
            
            document.getElementById("certInputModal").style.visibility = "visible";
            document.getElementById("certInputModal").style.opacity = "1";
        });
}

// 5. 인쇄 모달 열기 및 데이터 매핑
function openPrintModal(issueNo) {
    fetch(DIR_ROOT + `/sys/certConfig.php?key=${psnlKey.value}&CRUD=R&ISSUE_NO=${issueNo}`)
        .then(res => res.json())
        .then(json => {
            const d = json.data;
            if(!d) return;

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
                if(!dateStr) return "";
                const bits = dateStr.split("-");
                return bits[0] + "년 " + parseInt(bits[1]) + "월 " + parseInt(bits[2]) + "일";
            };
            const formatDotDate = (dateStr) => {
                if(!dateStr) return "";
                return dateStr.replace(/-/g, '.');
            };

            const birthStr = (() => {
                if(d.PSNL_NUM && d.PSNL_NUM.length >= 6) {
                    let yy = d.PSNL_NUM.substring(0,2);
                    let mm = d.PSNL_NUM.substring(2,4);
                    let dd = d.PSNL_NUM.substring(4,6);
                    let yearPrefix = (parseInt(yy) > 30) ? "19" : "20"; 
                    return yearPrefix + yy + "년 " + parseInt(mm) + "월 " + parseInt(dd) + "일";
                }
                return "";
            })();

            // 3. 레이아웃별 데이터 주입
            if(d.CERT_TYPE === '경력') {
                layoutStandard.style.display = "none";
                layoutCareer.style.display = "block";

                document.getElementById("p_ISSUE_NO_C").innerText = d.ISSUE_NO.split('-').pop(); // 순번만
                document.getElementById("p_PSNL_NM_C").innerText = d.PSNL_NM;
                document.getElementById("p_BIRTH_DT_C").innerText = birthStr;
                document.getElementById("p_ADDR_C").innerText = d.CURR_ADDR || "";
                document.getElementById("p_ORIGIN_C").innerText = d.ORIGIN_ADDR || "미 기 재";
                
                document.getElementById("p_JOIN_DT_C").innerText = formatDotDate(d.JOIN_DT);
                document.getElementById("p_RETIRE_DT_C").innerText = formatDotDate(d.RETIRE_DT);
                document.getElementById("p_ORG_NM_C").innerHTML = `천주교 수원교구<br>${d.ORG_NM}`;
                document.getElementById("p_POS_C").innerText = d.POSITION;

                if(d.ISSUE_DT) {
                    const b = d.ISSUE_DT.split("-");
                    document.getElementById("p_ISSUE_DT_C").innerText = b[0] + "년 " + parseInt(b[1]) + "월 " + parseInt(b[2]) + "일";
                }
            } else {
                layoutStandard.style.display = "block";
                layoutCareer.style.display = "none";

                // 표준 레이아웃 (재직/퇴직)
                document.getElementById("p_ISSUE_NO").innerText = d.ISSUE_NO.split('-').pop();
                document.getElementById("p_PSNL_NM").innerText = d.PSNL_NM;
                document.getElementById("p_BIRTH_DT").innerText = birthStr;
                document.getElementById("p_ORIGIN_ADDR").innerText = d.ORIGIN_ADDR || "미 기 재";
                document.getElementById("p_TITLE").innerText = d.CERT_TYPE;
                
                // 퇴직 시 외곽 푸터 표시
                document.getElementById("p_OUTSIDE_FOOTER").style.display = (d.CERT_TYPE === '퇴직') ? "block" : "none";

                // 주소 분리
                const fullAddr = d.CURR_ADDR || "";
                if(fullAddr.length > 22) {
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

                if(d.CERT_TYPE === '재직') {
                    bodyHtml = `이 사람은 ${joinDtKr} 부터 현재 까지 본 천주<br>교 수원교구 ${d.ORG_NM} ${d.POSITION}으로 재직 중임을 증명함.`;
                } else {
                    bodyHtml = `위 사람은 ${joinDtKr} 입사하여 <br>${retireDtKr} 퇴직하였음을 증명함.`;
                }
                document.getElementById("p_BODY_CONTENT").innerHTML = bodyHtml;

                if(d.ISSUE_DT) {
                    const b = d.ISSUE_DT.split("-");
                    document.getElementById("p_ISSUE_DT").innerText = b[0] + ". " + parseInt(b[1]) + ". " + parseInt(b[2]) + ".";
                }
            }

            // 모달 표시
            document.getElementById("certPrintModal").style.visibility = "visible";
            document.getElementById("certPrintModal").style.opacity = "1";
        });
}

// 6. 모달 내부 삭제 버튼 연결
document.getElementById("modalDelBtn").addEventListener("click", () => {
    let issueNo = document.getElementById("md_ISSUE_NO").value;
    deleteCert(issueNo);
});

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

// 8. 직원 검색 팝업
document.getElementById("psnlSerchPop").addEventListener('click', () => {
    window.open(DIR_ROOT + '/components/psnlPopup.php', '사원 검색', 'width=500, height=500');
});

// 성명 검색창 엔터 키 이벤트 추가
document.getElementById("PSNL_NM").addEventListener("keydown", (e) => {
    if (e.keyCode === 13) {
        myTblRefresh();
    }
});

// 사원 검색 후 호출되는 글로벌 함수 (사원 팝업에서 사용)
function myTblRefresh() {
    mytbl.hrDt.xhr.where['EMP_NM'] = document.getElementById("PSNL_NM").value;
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