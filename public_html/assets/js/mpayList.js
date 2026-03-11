//윈도우 로드가 끝나면
window.onload = function () {
    xhrLoad();
    if (document.getElementById("PSNL_NM").value == "") {
        document.getElementById("PSNL_NM").focus();
    } else {
        document.getElementById("MPAY_YEAR").focus();
    }
};

//검색 필터링을 위한 코드
document.querySelectorAll(".filter").forEach((f, key) => {
    f.addEventListener("change", () => {
        xhrLoad();
    });
});

//fetch를 이용한 실시간 정보로드
async function xhrLoad() {
    const url = DIR_ROOT + "/sys/mpayList.php?key=" + psnlKey.value + "&PSNL_CD=" + document.getElementById("PSNL_CD").value + "&MPAY_YEAR=" + document.getElementById("MPAY_YEAR").value;
    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(response.statusText);
        const json = await response.json();
        const res = json['data'];

        let htmlTxt = ` <ul>
                            <li>기준년월</li>
                            <li>급(Lv)</li>
                            <li>호</li>
                            <li>기본급</li>
                            <li>법정수당</li>
                            <li>직책수당</li>
                            <li>가족수당</li>
                            <li>자격수당</li>
                            <li>장애인수당</li>
                            <li>조정수당</li>
                            <li>급여액</li>
                        </ul>`;
        if (res != null) {
            res.forEach((tr, key) => {
                htmlTxt += '<ul>'
                htmlTxt += '    <li>' + tr.YEAR_MON + '</li>'
                htmlTxt += '    <li>' + tr.GRD_GRADE + '</li>'
                htmlTxt += '    <li>' + tr.GRD_PAY + '</li>'
                htmlTxt += '    <li>' + tr.NORMAL_PAY + '</li>'
                htmlTxt += '    <li>' + tr.LEGAL_PAY + '</li>'
                htmlTxt += '    <li>' + tr.POSI_PAY + '</li>'
                htmlTxt += '    <li>' + tr.FML_PAY + '</li>'
                htmlTxt += '    <li>' + tr.LCS_PAY + '</li>'
                htmlTxt += '    <li>' + tr.DIS_PAY + '</li>'
                htmlTxt += '    <li>' + tr.ADJ_PAY + '</li>'
                htmlTxt += '    <li>' + tr.TOTAL_PAY + '</li>'
                htmlTxt += '</ul>'
            });
        }
        document.getElementById("mpayTbl").innerHTML = htmlTxt;
    } catch (error) {
        console.error("mpayLoad 에러:", error);
    }
}

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
        psnl_cd = document.getElementById("PSNL_CD").value;
    });
}