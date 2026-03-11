//데이터테이블을 지정한다.
const tblStyle = document.createElement('style');
tblStyle.innerHTML = `
    .v-line-left { border-left: 1px solid #adb5bd !important; }
    .v-line-right { border-right: 1px solid #adb5bd !important; }
`;
document.head.appendChild(tblStyle);
var mytbl = new hr_tbl({
    xhr: {
        url: DIR_ROOT + '/sys/orgHrcount.php',
        columXHR: '',
        key: psnlKey.value, //api 호출할 보안 개인인증키
        where: {
            nothing: '',
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
        , { title: "조직명", data: "ORG_NM", className: "" }
        , { title: "상위조직", data: "UPR_ORG_NM", className: "" }
        , {
            title: "정규직", data: "REGULAR_CNT", className: "v-line-left", render: function (data, row) {
                const style = "display:inline-block; min-width:60px; padding:1px 10px; background:#f8f9fa; border:1px solid #ddd; border-radius:3px; color:#444; text-decoration:none; cursor:pointer; font-weight:500; font-size:12px; line-height:20px;";
                return (data != 0 && data != '0') ? `<a href="${DIR_ROOT}/psnlTotal?TRS_TYPE=1&ORG_NM=${encodeURIComponent(row.ORG_NM)}&WORK_TYPE=${encodeURIComponent('정규')}" style="${style}" onclick="event.stopPropagation();">${data}</a>` : data;
            }
        }
        , {
            title: "기능직", data: "FUNC_CNT", className: "", render: function (data, row) {
                const style = "display:inline-block; min-width:60px; padding:1px 10px; background:#f8f9fa; border:1px solid #ddd; border-radius:3px; color:#444; text-decoration:none; cursor:pointer; font-weight:500; font-size:12px; line-height:20px;";
                return (data != 0 && data != '0') ? `<a href="${DIR_ROOT}/psnlTotal?TRS_TYPE=1&ORG_NM=${encodeURIComponent(row.ORG_NM)}&WORK_TYPE=${encodeURIComponent('기능')}" style="${style}" onclick="event.stopPropagation();">${data}</a>` : data;
            }
        }
        , {
            title: "계약직", data: "CONT_CNT", className: "v-line-right", render: function (data, row) {
                const style = "display:inline-block; min-width:60px; padding:1px 10px; background:#f8f9fa; border:1px solid #ddd; border-radius:3px; color:#444; text-decoration:none; cursor:pointer; font-weight:500; font-size:12px; line-height:20px;";
                return (data != 0 && data != '0') ? `<a href="${DIR_ROOT}/psnlTotal?TRS_TYPE=1&ORG_NM=${encodeURIComponent(row.ORG_NM)}&WORK_TYPE=${encodeURIComponent('계약')}" style="${style}" onclick="event.stopPropagation();">${data}</a>` : data;
            }
        }
        , { title: "사무장", data: "POS_MGR_CNT", className: "v-line-left" }
        , { title: "사무원", data: "POS_CLK_CNT", className: "" }
        , { title: "관리장", data: "POS_MNT_CNT", className: "" }
        , { title: "가사사용인", data: "POS_DMS_CNT", className: "v-line-right" }
        , { title: "남", data: "MALE_CNT", className: "v-line-left" }
        , { title: "여", data: "FEMALE_CNT", className: "v-line-right" }
        , {
            title: "인원합계", data: "TOTAL_CNT", className: "", render: function (data, row) {
                const style = "display:inline-block; min-width:60px; padding:1px 10px; background:#f1f3f5; border:1px solid #ced4da; border-radius:3px; color:#212529; text-decoration:none; cursor:pointer; font-weight:700; font-size:12px; line-height:20px;";
                return (data != 0 && data != '0') ? `<a href="${DIR_ROOT}/psnlTotal?TRS_TYPE=1&ORG_NM=${encodeURIComponent(row.ORG_NM)}" style="${style}" onclick="event.stopPropagation();">${data}</a>` : data;
            }
        }
        , {
            title: "신자수", data: "PERSON_CNT", className: "", render: function (data, row) {
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
