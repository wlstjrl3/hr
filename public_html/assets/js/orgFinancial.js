//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    xhr: {
        url: DIR_ROOT + '/sys/orgFinancial.php',
        columXHR: '',
        key: API_TOKEN,
        where: {
            nothing: '',
        },
        order: {
            column: 1, // FSC_YEAR
            direction: 'desc',
        },
        page: 0,
        limit: 10,
    },
    columns: [
        { title: "idx", data: "UID", className: "hidden" }
        , { title: "회계연도", data: "FSC_YEAR", className: "" }
        , { title: "조직코드", data: "ORG_CD", className: "" }
        , { title: "조직명", data: "ORG_NM", className: "" }
        , { title: "계정명", data: "ACC_NM", className: "" }
        , { title: "금액", data: "AMOUNT", className: "txtR", render: (data) => data ? Number(data).toLocaleString() : '0' }
    ],
});
mytbl.show('myTbl');
mytbl.xportBind();

// 엑셀 업로드 처리
document.getElementById('uploadExcel').addEventListener('click', () => {
    document.getElementById('excelFile').click();
});

document.getElementById('excelFile').addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (evt) => {
        const data = evt.target.result;
        const workbook = XLSX.read(data, { type: 'binary' });
        const sheetName = workbook.SheetNames[0];
        const sheet = workbook.Sheets[sheetName];
        const json = XLSX.utils.sheet_to_json(sheet);

        // core.py에서 생성된 엑셀 컬럼명 매핑
        const processedData = json.map(row => ({
            org_cd: row['조직코드'],
            acc_nm: row['계정명'],
            amount: row['금액'],
            fsc_year: row['회계연도']
        })).filter(row => row.org_cd && row.acc_nm);

        if (processedData.length === 0) {
            alert("유효한 데이터가 없습니다. 엑셀 파일을 확인해주세요.");
            return;
        }

        if (confirm(processedData.length + "개의 데이터를 업로드/갱신 하시겠습니까?")) {
            const url = DIR_ROOT + "/sys/orgFinancialConfig.php?key=" + API_TOKEN + "&CRUD=U";
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(processedData)
            })
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success') {
                    alert("업로드 완료: " + res.message);
                    mytbl.show('myTbl');
                } else {
                    alert("업로드 실패: " + res.message);
                }
            })
            .catch(error => {
                alert("업로드 중 오류 발생: " + error.message);
            });
        }
        e.target.value = '';
    };
    reader.readAsBinaryString(file);
});

// 신규 버튼
newCol.addEventListener("click", () => {
    document.querySelector(".modalForm").style.visibility = "visible";
    document.querySelector(".modalForm").style.opacity = "1";
    document.querySelector(".modalForm").querySelectorAll("input").forEach(input => {
        input.value = "";
    });
});

// 행 클릭 시 상세조회
function trDataXHR(uid) {
    if (!uid) return;
    const [fsc_year, org_cd, acc_nm] = uid.split('|');
    
    // 테이블 내에서 데이터를 찾아 모달에 채움 (서버 호출 최소화)
    const tr = document.querySelector(`tr[data-idx="${uid}"]`);
    if (tr) {
        document.getElementById('FSC_YEAR').value = fsc_year;
        document.getElementById('ORG_CD').value = org_cd;
        document.getElementById('ORG_NM').value = tr.querySelector('[data-label="조직명"]').innerText;
        document.getElementById('ACC_NM').value = acc_nm;
        const amtStr = tr.querySelector('[data-label="금액"]').innerText.replace(/,/g, '');
        document.getElementById('AMOUNT').value = amtStr;
    }
}

// 모달 저장 버튼
modalEdtBtn.addEventListener("click", () => {
    const data = {
        FSC_YEAR: document.getElementById('FSC_YEAR').value,
        ORG_CD: document.getElementById('ORG_CD').value,
        ACC_NM: document.getElementById('ACC_NM').value,
        AMOUNT: document.getElementById('AMOUNT').value,
    };

    if (!data.FSC_YEAR || !data.ORG_CD || !data.ACC_NM) {
        alert("필수 항목을 입력하세요.");
        return;
    }

    const url = DIR_ROOT + "/sys/orgFinancialConfig.php?key=" + API_TOKEN + "&CRUD=C";
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(res => {
        if (res.status === 'success') {
            alert("저장 완료");
            mytbl.show('myTbl');
            modalClose();
        } else {
            alert("저장 실패: " + res.message);
        }
    })
    .catch(error => {
        alert("저장 중 오류 발생: " + error.message);
    });
});

// 삭제 버튼
modalDelBtn.addEventListener("click", () => {
    if (!confirm("삭제 하시겠습니까?")) return;

    const fsc_year = document.getElementById('FSC_YEAR').value;
    const org_cd = document.getElementById('ORG_CD').value;
    const acc_nm = document.getElementById('ACC_NM').value;

    const url = DIR_ROOT + "/sys/orgFinancialConfig.php?key=" + API_TOKEN + "&CRUD=D" 
                + "&FSC_YEAR=" + fsc_year + "&ORG_CD=" + org_cd + "&ACC_NM=" + encodeURIComponent(acc_nm);
    
    fetch(url)
    .then(response => response.json())
    .then(res => {
        if (res.status === 'success') {
            alert("삭제 완료");
            mytbl.show('myTbl');
            modalClose();
        } else {
            alert("삭제 실패: " + res.message);
        }
    })
    .catch(error => {
        alert("삭제 중 오류 발생: " + error.message);
    });
});

// 검색 필터
document.querySelectorAll(".filter").forEach(f => {
    f.addEventListener("keyup", (e) => {
        if (e.keyCode === 13 || e.type === 'change') {
            mytbl.hrDt.xhr.where[f.id.replace('S_', '')] = f.value;
            mytbl.hrDt.xhr.page = 0;
            mytbl.show("myTbl");
        }
    });
    f.addEventListener("change", () => {
        mytbl.hrDt.xhr.where[f.id.replace('S_', '')] = f.value;
        mytbl.hrDt.xhr.page = 0;
        mytbl.show("myTbl");
    });
});
