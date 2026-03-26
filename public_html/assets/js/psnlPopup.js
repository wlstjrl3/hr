// 부모 창에 작성된 #parentInput의 값 얻어오기
// opener == 부모창
const parentValue = (opener && opener.document.getElementById('PSNL_NM')) ? opener.document.getElementById('PSNL_NM').value : "";
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
            const setVal = (id, val) => {
                let el = opener.document.getElementById(id);
                if(el) el.value = val;
            };
            
            setVal('PSNL_CD', firstRowCells[1].innerText);
            setVal('ORG_NM', firstRowCells[2].innerText);
            setVal('PSNL_NM', firstRowCells[3].innerText);
            setVal('POSITION', firstRowCells[5].innerText);
            
            let searchPop = opener.document.getElementById('psnlSerchPop');
            if (searchPop && searchPop.parentElement && searchPop.parentElement.parentElement && searchPop.parentElement.parentElement.nextElementSibling) {
                let targetInput = searchPop.parentElement.parentElement.nextElementSibling.querySelector("input");
                if (targetInput) targetInput.focus();
            }
            
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
                opener.document.getElementById('PSNL_CD').value = tmp[0].children[1].innerText;
                opener.document.getElementById('ORG_NM').value = tmp[0].children[2].innerText;
                opener.document.getElementById('PSNL_NM').value = tmp[0].children[3].innerText;
                opener.document.getElementById('POSITION').value = tmp[0].children[5].innerText;

                let searchPop = opener.document.getElementById('psnlSerchPop');
                if (searchPop && searchPop.parentElement && searchPop.parentElement.parentElement && searchPop.parentElement.parentElement.nextElementSibling) {
                    let targetInput = searchPop.parentElement.parentElement.nextElementSibling.querySelector("input");
                    if (targetInput) targetInput.focus();
                }

                opener.myTblRefresh();
                window.close();
            }
        }, 400);
    });
});

//팝업창의 경우 행을 클릭했을때 개별 정보 끌어오기가 아닌 무응답으로 처리
function trDataXHR(idx) { }