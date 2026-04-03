//데이터테이블을 지정한다.
const tblStyle = document.createElement('style');
tblStyle.innerHTML = `
    .v-line-left { border-left: 1px solid #adb5bd !important; }
    .v-line-right { border-right: 1px solid #adb5bd !important; }
    .v-line-left-bold { border-left: 2px solid #495057 !important; }
`;
document.head.appendChild(tblStyle);
var mytbl = new hr_tbl({
    xhr: {
        url: DIR_ROOT + '/sys/statOrgHr.php',
        columXHR: '',
        key: API_TOKEN, //api 호출할 보안 개인인증키
        where: {
            UUPR_ORG: '13061001',
        },
        order: {
            column: '0',
            direction: 'desc',
        },
        page: 0, //표시되는 페이지에서 1이 빠진 값이다 즉 page:0 = 1페이지
        limit: 10, //만약 리미트가 0이라면 리미트 없이 전체 조회하는 것으로 처리
    },
    columns: [
        //반드시 첫열이 key값이되는 열이 와야한다. 숨김여부는 class로 추가 지정
        { title: "idx", data: "ORG_CD", className: "hidden" }
        , { title: "조직명", data: "ORG_NM", className: "nameCol" }
        , { title: "상위조직", data: "UPR_ORG_NM", className: "nameCol" }
        , {
            title: "정규직", data: "REGULAR_CNT", className: "v-line-left statCol", render: function (data, row) {
                const style = "display:inline-block; min-width:60px; padding:1px 10px; background:#f8f9fa; border:1px solid #ddd; border-radius:3px; color:#444; text-decoration:none; cursor:pointer; font-weight:500; font-size:12px; line-height:20px;";
                return (data != 0 && data != '0') ? `<a href="${DIR_ROOT}/psnlTotal?TRS_TYPE=1&ORG_NM=${encodeURIComponent(row.ORG_NM)}&WORK_TYPE=${encodeURIComponent('정규')}" style="${style}" onclick="event.stopPropagation();">${data}</a>` : data;
            }
        }
        , {
            title: "기능직", data: "FUNC_CNT", className: "statCol", render: function (data, row) {
                const style = "display:inline-block; min-width:60px; padding:1px 10px; background:#f8f9fa; border:1px solid #ddd; border-radius:3px; color:#444; text-decoration:none; cursor:pointer; font-weight:500; font-size:12px; line-height:20px;";
                return (data != 0 && data != '0') ? `<a href="${DIR_ROOT}/psnlTotal?TRS_TYPE=1&ORG_NM=${encodeURIComponent(row.ORG_NM)}&WORK_TYPE=${encodeURIComponent('기능')}" style="${style}" onclick="event.stopPropagation();">${data}</a>` : data;
            }
        }
        , {
            title: "계약직", data: "CONT_CNT", className: "statCol", render: function (data, row) {
                const style = "display:inline-block; min-width:60px; padding:1px 10px; background:#f8f9fa; border:1px solid #ddd; border-radius:3px; color:#444; text-decoration:none; cursor:pointer; font-weight:500; font-size:12px; line-height:20px;";
                return (data != 0 && data != '0') ? `<a href="${DIR_ROOT}/psnlTotal?TRS_TYPE=1&ORG_NM=${encodeURIComponent(row.ORG_NM)}&WORK_TYPE=${encodeURIComponent('계약')}" style="${style}" onclick="event.stopPropagation();">${data}</a>` : data;
            }
        }
        , {
            title: "소계", data: "EMPLOY_SUBTOTAL", className: "v-line-right statCol"
        }
        , { title: "사무장", data: "POS_MGR_CNT", className: "v-line-left statCol" }
        , { title: "사무원", data: "POS_CLK_CNT", className: "statCol" }
        , { title: "관리장", data: "POS_MNT_CNT", className: "v-line-right statCol" }
        , { title: "남", data: "MALE_CNT", className: "v-line-left statCol" }
        , { title: "여", data: "FEMALE_CNT", className: "statCol" }
        , { title: "가사사용인", data: "POS_DMS_CNT", className: "v-line-left-bold v-line-right statCol" }
        , {
            title: "가사직<br>포함총계", data: "TOTAL_CNT", className: "statCol", render: function (data, row) {
                const style = "display:inline-block; min-width:60px; padding:1px 10px; background:#f1f3f5; border:1px solid #ced4da; border-radius:3px; color:#212529; text-decoration:none; cursor:pointer; font-weight:700; font-size:12px; line-height:20px;";
                return (data != 0 && data != '0') ? `<a href="${DIR_ROOT}/psnlTotal?TRS_TYPE=1&ORG_NM=${encodeURIComponent(row.ORG_NM)}" style="${style}" onclick="event.stopPropagation();">${data}</a>` : data;
            }
        }
        , {
            title: "신자수", data: "PERSON_CNT", className: "v-line-left statCol", render: function (data, row) {
                return data ? data.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : '0';
            }
        }
    ],
});
mytbl.show('myTbl'); //테이블의 아이디에 렌더링 한다(갱신도 가능)
mytbl.xportBind();

//검색 필터링을 위한 코드
document.querySelectorAll(".filter").forEach((f, key) => {
    f.addEventListener("change", () => {
        mytbl.hrDt.xhr.where[f.id] = f.value;
        mytbl.hrDt.xhr.page = 0; //필터가 바뀌면 페이지 수도 바뀌므로 첫장으로 돌려보낸다.
        mytbl.show("myTbl");
    });
});

//대리구 필터 적용시 지구 필터의 자동 필터링 코드
document.getElementById("UUPR_ORG").addEventListener("change", evt => {
    if (evt.currentTarget.value == "13061001") {
        document.querySelectorAll(".sw1d").forEach(tmp => { tmp.style.display = "block"; }); document.querySelectorAll(".sw2d").forEach(tmp => { tmp.style.display = "none"; });
    } else if (evt.currentTarget.value == "13062001") {
        document.querySelectorAll(".sw1d").forEach(tmp => { tmp.style.display = "none"; }); document.querySelectorAll(".sw2d").forEach(tmp => { tmp.style.display = "block"; });
    } else {
        document.querySelectorAll(".sw1d").forEach(tmp => { tmp.style.display = "block"; }); document.querySelectorAll(".sw2d").forEach(tmp => { tmp.style.display = "block"; });
    }
});
//초기 진입시 제1대리구 고정에 따른 지구 필터링 트리거
document.querySelectorAll(".sw1d").forEach(tmp => { tmp.style.display = "block"; }); document.querySelectorAll(".sw2d").forEach(tmp => { tmp.style.display = "none"; });

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
    { title: "고용형태", id: "grpWorkType", keys: ["REGULAR_CNT", "FUNC_CNT", "CONT_CNT"], checked: "checked" },
    { title: "소계", id: "grpSubTotal", keys: ["EMPLOY_SUBTOTAL"], checked: "checked" },
    { title: "직종", id: "grpPosition", keys: ["POS_MGR_CNT", "POS_CLK_CNT", "POS_MNT_CNT"], checked: "checked" },
    { title: "성별", id: "grpGender", keys: ["MALE_CNT", "FEMALE_CNT"], checked: "checked" },
    { title: "가사사용인", id: "grpDms", keys: ["POS_DMS_CNT"], checked: "checked" },
    { title: "총계", id: "grpTotal", keys: ["TOTAL_CNT"], checked: "checked" },
    { title: "신자수", id: "grpPersonCnt", keys: ["PERSON_CNT"], checked: "checked" }
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
for (let i = 0; i < Object.keys(mytbl.hrDt.columns).length; i++) {
    const col = mytbl.hrDt.columns[i];
    col.originalClassName = col.className || "";
}

document.querySelectorAll(".showColGrpToggle").forEach(st => {
    st.addEventListener("click", (tmp) => {
        let keys = tmp.currentTarget.dataset.keys.split(',');
        let isChecked = tmp.currentTarget.checked;

        for (let i = 0; i < Object.keys(mytbl.hrDt.columns).length; i++) {
            const col = mytbl.hrDt.columns[i];
            if (keys.includes(col.data)) {
                if (isChecked == false) {
                    col.className = "hidden";
                } else {
                    col.className = col.originalClassName; // 원래 클래스로 복원 (예: v-line-left 등)
                }
            }
        }
        mytbl.show("myTbl");
    });
});
