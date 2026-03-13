var mytbl = new hr_tbl({
    xhr: {
        url: DIR_ROOT + '/sys/statWorkType.php',
        columXHR: '',
        key: psnlKey.value,
        where: {
            TARGET_TYPE: 'ALL',
        },
        order: {
            column: '0',
            direction: 'asc',
        },
        page: 0,
        limit: 10,
    },
    columns: [
        { title: "idx", data: "ORG_CD", className: "hidden" }
        , { title: "상위조직", data: "UPR_ORG_NM", className: "nameCol" }
        , { title: "사업장명", data: "ORG_NM", className: "nameCol" }
        , { title: "정규직 남성", data: "REG_MALE", className: "statCol", render: function(d,r){ return getWorkTypeLink(d,r,'REG_MALE'); } }
        , { title: "정규직 여성", data: "REG_FEMALE", className: "statCol", render: function(d,r){ return getWorkTypeLink(d,r,'REG_FEMALE'); } }
        , { title: "계약직 남성", data: "CONT_MALE", className: "statCol", render: function(d,r){ return getWorkTypeLink(d,r,'CONT_MALE'); } }
        , { title: "계약직 여성", data: "CONT_FEMALE", className: "statCol", render: function(d,r){ return getWorkTypeLink(d,r,'CONT_FEMALE'); } }
        , { title: "단축근로 남성", data: "SHORT_MALE", className: "statCol", render: function(d,r){ return getWorkTypeLink(d,r,'SHORT_MALE'); } }
        , { title: "단축근로 여성", data: "SHORT_FEMALE", className: "statCol", render: function(d,r){ return getWorkTypeLink(d,r,'SHORT_FEMALE'); } }
    ],
});

function getWorkTypeLink(data, row, cat) {
    if(data == 0 || data == '0') return data;
    const targetType = document.getElementById("TARGET_TYPE") ? document.getElementById("TARGET_TYPE").value || 'ALL' : 'ALL';
    const href = `${DIR_ROOT}/psnlTotal?STAT_MODE=1&STAT_TARGET=${encodeURIComponent(targetType)}&STAT_ORG_CD=${encodeURIComponent(row.ORG_CD)}&STAT_CAT=${encodeURIComponent(cat)}`;
    const style = "display:inline-block; min-width:60px; padding:1px 10px; background:#f8f9fa; border:1px solid #ddd; border-radius:3px; color:#444; text-decoration:none; cursor:pointer; font-weight:500; font-size:12px; line-height:20px;";
    return `<a href="${href}" style="${style}" onclick="event.stopPropagation();">${data}</a>`;
}

mytbl.show('myTbl');
mytbl.xportBind();

document.querySelectorAll(".filter").forEach((f) => {
    f.addEventListener("change", () => {
        if (f.id === "TARGET_TYPE") {
            if (f.value === "PARISH") {
                document.getElementById("districtFilterArea").style.display = "block";
            } else {
                document.getElementById("districtFilterArea").style.display = "none";
                document.getElementById("UPR_ORG").value = "";
                delete mytbl.hrDt.xhr.where["UPR_ORG"];
            }
        }
        
        mytbl.hrDt.xhr.where[f.id] = f.value;
        mytbl.hrDt.xhr.page = 0;
        mytbl.show("myTbl");
    });
});
