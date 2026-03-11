console.log("인덱스 페이지에 접속하셨습니다.");
//윈도우 로드가 끝나면
window.onload = function () {
    xhrLoad();
};

//검색 필터링을 위한 코드
document.querySelectorAll(".filter").forEach((f, key) => {
    f.addEventListener("change", () => {
        xhrLoad();
    });
});

//fetch를 이용한 실시간 정보로드
async function xhrLoad() {
    try {
        // 1. 퇴직 예정자 정보 (60세)
        const birthFrom = (new Date().getFullYear() - 60) + "-01-01";
        const birthTo = (new Date().getFullYear() - 60) + "-12-31";
        const url1 = `${DIR_ROOT}/sys/psnlTotal.php?key=${psnlKey.value}&TRS_TYPE=1&ORDER=PSNL_NUM ASC&PSNL_BIRTH_From=${birthFrom}&PSNL_BIRTH_To=${birthTo}`;

        const res1 = await fetch(url1).then(r => r.json());
        let htmlTxt1 = `<ul><li>생년월일</li><li>성명</li><li>소속</li><li>직책</li><li>발령일</li><li>발령기간</li><li>근무형태</li><li>급</li><li>호</li></ul>`;

        if (res1.data) {
            res1.data.forEach(tr => {
                htmlTxt1 += `<ul>
                    <li>${tr.PSNL_NUM.slice(0, 6)}</li>
                    <li>${tr.PSNL_NM}(${tr.BAPT_NM})</li>
                    <li>${tr.ORG_NM}</li>
                    <li>${tr.POSITION}</li>
                    <li>${tr.TRS_DT}</li>
                    <li>${tr.TRS_ELAPSE}</li>
                    <li>${tr.WORK_TYPE}</li>
                    <li>${tr.GRD_GRADE}</li>
                    <li>${tr.GRD_PAY}</li>
                </ul>`;
            });
        }
        document.getElementById("rtrTbl").innerHTML = htmlTxt1;

        // 2~4. 장기 근속자 정보 (10, 20, 30년)
        const years = [10, 20, 30];
        let htmlTxtSum = `<ul><li>발령기간</li><li>성명</li><li>소속</li><li>직책</li><li>발령일</li><li>생년월일</li><li>근무형태</li><li>급</li><li>호</li></ul>`;

        for (const year of years) {
            const dtFrom = dateFormat(dateCalc(dateCalc(new Date(), "m", 0), "y", -year));
            const dtTo = dateFormat(dateCalc(dateCalc(new Date(), "m", 6), "y", -year));
            const url = `${DIR_ROOT}/sys/psnlTotal.php?key=${psnlKey.value}&TRS_TYPE=1&ORDER=PSNL_NUM ASC&TRS_DT_From=${dtFrom}&TRS_DT_To=${dtTo}`;

            const res = await fetch(url).then(r => r.json());
            if (res.data) {
                res.data.forEach(tr => {
                    htmlTxtSum += `<ul>
                        <li>${tr.TRS_ELAPSE}</li>
                        <li>${tr.PSNL_NM}(${tr.BAPT_NM})</li>
                        <li>${tr.ORG_NM}</li>
                        <li>${tr.POSITION}</li>
                        <li>${tr.TRS_DT}</li>
                        <li>${tr.PSNL_NUM.slice(0, 6)}</li>
                        <li>${tr.WORK_TYPE}</li>
                        <li>${tr.GRD_GRADE}</li>
                        <li>${tr.GRD_PAY}</li>
                    </ul>`;
                });
            }
        }
        document.getElementById("ctnTbl").innerHTML = htmlTxtSum;

    } catch (e) {
        console.error("Dashboard data load failed:", e);
    }
}