document.getElementById("PSNL_NM").focus();

//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    xhr: {
        url: DIR_ROOT + '/sys/certPrintList.php',
        columXHR: '',
        key: psnlKey.value, //api 호출할 보안 개인인증키
        where: {
            PSNL_CD: document.getElementById("PSNL_CD").value,
            PSNL_NM: document.getElementById("PSNL_NM").value,
            CERT_TYPE: document.getElementById("CERT_TYPE").value,
            ISSUE_DT_From: document.getElementById("ISSUE_DT_From").value,
            ISSUE_DT_To: document.getElementById("ISSUE_DT_To").value,
            CERT_DTL: document.getElementById("CERT_DTL").value,
        },
        order: {
            column: '4',
            direction: 'desc',
        },
        page: 0, //표시되는 페이지에서 1이 빠진 값이다 즉 page:0 = 1페이지
        limit: 10, //만약 리미트가 0이라면 리미트 없이 전체 조회하는 것으로 처리 excel down등에서 0 처리해야 함!
    },
    columns: [
        //반드시 첫열이 key값이되는 열이 와야한다. 숨김여부는 class로 추가 지정
        { title: "idx", data: "CERT_CD", className: "hidden" }
        , { title: "조직", data: "ORG_NM", className: "" }
        , { title: "직원명", data: "PSNL_NM", className: "" }
        , { title: "직책", data: "POSITION", className: "" }
        , { title: "증명서 종류", data: "CERT_TYPE", className: "" }
        , { title: "발급일자", data: "ISSUE_DT", className: "" }
        , { title: "증명서 내용", data: "CERT_DTL", className: "" }
    ],
});
mytbl.show('myTbl'); //테이블의 아이디에 렌더링 한다(갱신도 가능)
mytbl.xportBind();

//이 아래로는 페이지 개별 모달창 이벤트를 지정
//신규버튼을 눌렀을때
newCol.addEventListener("click", () => {
    if (document.getElementById("PSNL_CD").value.length < 1) {
        alert("먼저 개인코드를 조회한 후 재시도 하시기 바랍니다.");
        return false;
    }
    document.querySelector(".modalBody").querySelector("b").innerHTML = ORG_NM.value + " " + POSITION.value + " " + PSNL_NM.value;
    document.querySelector(".modalForm").style.visibility = "visible"; //모달창이 나타나게 한다.
    document.querySelector(".modalForm").style.opacity = "1";     //투명도 애니메이션 적용을 위해 opacity가 0에서 1로 변경된다.
    document.querySelector(".modalForm").querySelectorAll("input").forEach((input, key) => {
        input.value = "";
        if (key == 1) {
            input.focus();
        }
    });
    document.querySelector(".modalForm").querySelectorAll("select").forEach((select, key) => {
        select.value = "";
    });
});

//행을 클릭했을때 fetch로 다시 끌어올 데이터는 각 페이지마다 다르기에 여기에서 지정
function trDataXHR(idx) {
    const url = DIR_ROOT + "/sys/certPrintConfig.php?key=" + psnlKey.value + "&CERT_CD=" + idx + "&CRUD=R";
    console.log(url);
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error(response.statusText);
            return response.json();
        })
        .then(json => {
            var res = json['data'];
            if (res != null) {
                document.querySelector(".modalBody").querySelectorAll("input").forEach((input, key) => {
                    switch (key) {
                        case 0: input.value = res[0].CERT_CD; break;
                        case 1: input.value = res[0].ISSUE_DT; break;
                        case 2: input.value = res[0].CERT_DTL; break;
                    }
                });
                document.querySelector(".modalBody").querySelectorAll("select").forEach((select, key) => {
                    select.value = res[0].CERT_TYPE;
                });
                document.querySelector(".modalBody").querySelector("b").innerHTML = res[0].ORG_NM + " " + res[0].POSITION + " " + res[0].PSNL_NM;
            }
        })
        .catch(error => {
            console.error("certPrintConfigXhr 정보 로드 에러:", error);
        });
}

//저장을 클릭했을때 fetch로 데이터를 기록
modalEdtBtn.addEventListener("click", () => {
    let writeUrl = '';
    try {
        document.querySelector(".modalForm").querySelectorAll("input").forEach((input, key) => {
            if (key == 0) { writeUrl += "&CERT_CD=" + input.value }
            else if (key == 1) {
                if (input.value.length < 3) { alert("발급일자는 필수값입니다."); throw new Error("stop loop"); }
                writeUrl += "&ISSUE_DT=" + input.value
            }
            else if (key == 2) { writeUrl += "&CERT_DTL=" + input.value }
        });
        document.querySelector(".modalForm").querySelectorAll("select").forEach((select, key) => {
            if (key == 0) {
                if (select.value.length < 1) { alert("증명서 종류는 필수값입니다"); throw new Error("stop loop"); }
                writeUrl += "&CERT_TYPE=" + select.value
            }
        });
    } catch (e) {
        console.log("필수값 체크"); return false;
    }
    writeUrl += "&PSNL_CD=" + document.getElementById("PSNL_CD").value;
    const url = DIR_ROOT + "/sys/certPrintConfig.php?key=" + psnlKey.value + writeUrl + "&CRUD=C";
    console.log(url);

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error(response.statusText);
            return response.text();
        })
        .then(text => {
            console.log("certPrintConfig 정보 기록 완료");
            mytbl.show('myTbl'); //테이블의 아이디
            modalClose();
        })
        .catch(error => {
            console.log("certPrintConfig 정보 기록 에러!!!:", error);
        });
});

//삭제를 클릭했을때 fetch로 데이터를 제거
modalDelBtn.addEventListener("click", () => {
    if (!confirm("삭제 하시겠습니까?")) {
        return false;
    }
    let deleteUrl = '';
    document.querySelector(".modalForm").querySelectorAll("input").forEach((input, key) => {
        if (key == 0) { deleteUrl += "&CERT_CD=" + input.value }
    });
    const url = DIR_ROOT + "/sys/certPrintConfig.php?key=" + psnlKey.value + deleteUrl + "&CRUD=D";
    console.log(url);

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error(response.statusText);
            console.log("certPrintConfig 정보 삭제 완료");
            mytbl.show('myTbl'); //테이블의 아이디
            modalClose();
        })
        .catch(error => {
            console.log("certPrintConfig 정보 제거 에러!!!:", error);
        });
});

//검색 필터링을 위한 코드
document.querySelectorAll(".filter").forEach((f, key) => {
    f.addEventListener("change", () => {
        mytbl.hrDt.xhr.where['PSNL_CD'] = document.getElementById("PSNL_CD").value;
        mytbl.hrDt.xhr.where['PSNL_NM'] = document.getElementById("PSNL_NM").value;
        mytbl.hrDt.xhr.where['CERT_TYPE'] = document.getElementById("CERT_TYPE").value;
        mytbl.hrDt.xhr.where['ISSUE_DT_From'] = document.getElementById("ISSUE_DT_From").value;
        mytbl.hrDt.xhr.where['ISSUE_DT_To'] = document.getElementById("ISSUE_DT_To").value;
        mytbl.hrDt.xhr.where['CERT_DTL'] = document.getElementById("CERT_DTL").value;
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

//직원코드 검색 팝업 띄우기
document.getElementById("psnlSerchPop").addEventListener('click', () => {
    window.open(DIR_ROOT + '/components/psnlPopup.php', '조직 검색', 'width=500, height=500');
});
document.getElementById("PSNL_NM").addEventListener("keyup", (evt) => {
    if (evt.keyCode == 13) {
        window.open(DIR_ROOT + '/components/psnlPopup.php', '조직 검색', 'width=500, height=500');
    }
});
function myTblRefresh() { //팝업창에서 정보를 선택하면 검색 필터링을 진행한다.
    document.querySelectorAll(".filter").forEach((f, key) => {
        mytbl.hrDt.xhr.where['PSNL_CD'] = document.getElementById("PSNL_CD").value;
        mytbl.hrDt.xhr.where['PSNL_NM'] = document.getElementById("PSNL_NM").value;
        mytbl.hrDt.xhr.where['CERT_TYPE'] = document.getElementById("CERT_TYPE").value;
        mytbl.hrDt.xhr.where['ISSUE_DT_From'] = document.getElementById("ISSUE_DT_From").value;
        mytbl.hrDt.xhr.where['ISSUE_DT_To'] = document.getElementById("ISSUE_DT_To").value;
        mytbl.hrDt.xhr.where['CERT_DTL'] = document.getElementById("CERT_DTL").value;
        mytbl.hrDt.xhr.page = 0; //필터가 바뀌면 페이지 수도 바뀌므로 첫장으로 돌려보낸다.
        mytbl.show("myTbl");
    });
}