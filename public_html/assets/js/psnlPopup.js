// URL 파라미터에서 값 추출 (모달에서 넘어온 경우 포함)
const urlParams = new URLSearchParams(window.location.search);
const modalVal = urlParams.get('VAL') || "";
const searchFrom = urlParams.get('SEARCH_FROM') || "";

// 부모 창의 #PSNL_NM 값 또는 URL에서 넘어온 VAL 중 우선순위 결정
const parentValue = (searchFrom === 'MODAL') 
    ? modalVal 
    : (opener && opener.document.getElementById('PSNL_NM') ? opener.document.getElementById('PSNL_NM').value : "");

const psnlNmLocal = document.querySelector('#PSNL_NM');
if(psnlNmLocal) psnlNmLocal.value = parentValue;

//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    tblType: "psnlPopup",
    xhr: {
        url: DIR_ROOT + '/sys/psnlPopSearch.php',
        columXHR: '',
        where: {
            PSNL_NM: parentValue,
        },
        order: {
            column: '0',
            direction: 'desc',
        },
        page: 0, 
        limit: 5, 
    },
    columns: [
        { title: "직원코드", data: "PSNL_CD", className: "" }
        , { title: "조직명", data: "ORG_NM", className: "" }
        , { title: "성명", data: "PSNL_NM", className: "" }
        , { title: "세례명", data: "BAPT_NM", className: "" }
        , { title: "직책", data: "POSITION", className: "" }
    ],
});
mytbl.show('myTbl');

//검색된 데이터가 하나라면 즉시 바인딩 한다.(로딩 시간이 걸리기에 지연로딩 처리)
window.onload = function () {
    const psnlNmInput = document.getElementById("PSNL_NM");
    if (psnlNmInput) psnlNmInput.focus();
    
    setTimeout(() => {
        let table = document.querySelector(".hr_tbl");
        if (!table) return;
        
        let tbody = table.querySelector("tbody");
        if (!tbody) return;
        
        let rows = tbody.querySelectorAll("tr");
        if (rows.length !== 1) return; // 단일 검색 결과일 때만 실행

        let firstRowCells = rows[0].children;
        if (!firstRowCells || firstRowCells.length < 6) return;

        // "데이터가 없습니다." 메시지인 경우 제외
        if (firstRowCells[0].innerText.includes("데이터가 없습니다") || (firstRowCells[1] && firstRowCells[1].innerText === "데이터가 없습니다.")) return;

        // [0]반응형버튼 [1]직원코드 [2]조직명 [3]성명 [4]세례명 [5]직책
        if (opener && opener.document) {
            const empNo = firstRowCells[1].innerText;
            const orgNm = firstRowCells[2].innerText;
            const psnlNm = firstRowCells[3].innerText;
            const pos = firstRowCells[5].innerText;

            const setVal = (id, val) => {
                let el = opener.document.getElementById(id);
                if(el) el.value = val;
            };

            // 1. 범용 페이지(fmlList 등) 연동 필드 바인딩
            setVal('PSNL_CD', empNo);
            setVal('ORG_NM', orgNm);
            setVal('POSITION', pos);
            if(searchFrom !== 'MODAL') { // 모달에서 온 게 아닐 때만 메인 성명 갱신
                setVal('PSNL_NM', psnlNm);
            }

            // 2. 증명서 모달(certPrint) 전용 필드 바인딩
            setVal('md_EMP_NO', empNo);
            setVal('md_PSNL_CD', empNo);
            setVal('md_ORG_NM', orgNm);
            setVal('md_POSITION', pos);
            setVal('md_PSNL_NM', psnlNm);

            if (typeof opener.myTblRefresh === 'function') opener.myTblRefresh();
            window.close();
        }
    }, 800);
};

//검색 필터링을 위한 코드
document.querySelectorAll(".filter").forEach((f, key) => {
    f.addEventListener("change", () => {
        mytbl.hrDt.xhr.where[f.id] = f.value;
        mytbl.hrDt.xhr.page = 0; //필터가 바뀌면 페이지 수도 바뀌므로 첫장으로 돌려보낸다.
        mytbl.show("myTbl");
        setTimeout(() => {
            let table = document.querySelector(".hr_tbl");
            if (!table || !table.children[1]) return;

            let tmp = table.children[1].children;
            if (tmp.length == 1 && tmp[0].children[1] && tmp[0].children[1].innerText != "데이터가 없습니다.") {
                const empNo = tmp[0].children[1].innerText;
                const orgNm = tmp[0].children[2].innerText;
                const psnlNm = tmp[0].children[3].innerText;
                const pos = tmp[0].children[5].innerText;

                const setVal = (id, val) => {
                    let el = opener.document.getElementById(id);
                    if(el) el.value = val;
                };

                // 1. 범용 페이지(fmlList 등) 연동 필드 바인딩
                setVal('PSNL_CD', empNo);
                setVal('ORG_NM', orgNm);
                setVal('POSITION', pos);
                if(searchFrom !== 'MODAL') {
                    setVal('PSNL_NM', psnlNm);
                }

                // 2. 증명서 모달(certPrint) 전용 필드 바인딩
                setVal('md_EMP_NO', empNo);
                setVal('md_PSNL_CD', empNo);
                setVal('md_ORG_NM', orgNm);
                setVal('md_POSITION', pos);
                setVal('md_PSNL_NM', psnlNm);

                if (typeof opener.myTblRefresh === 'function') opener.myTblRefresh();
                window.close();
            }
        }, 400);
    });
});

//팝업창의 경우 행을 클릭했을때 개별 정보 끌어오기가 아닌 무응답으로 처리
function trDataXHR(idx) { }