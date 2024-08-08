var mytbl = new hr_tbl({
    xhr:{
        url:'/ref/user?user_id=a',
        order: {
            column : '1',
            direction : 'desc',
        }
    },
    columns: [
         {title: "No.", data: "num", orderable: false,
            "render": function ( data, type, row, meta ) {
                return "<div class='idx' data-idx='"+row.ORG_NO+"'>"+data+"</div>";
            },
         }
        ,{title: "기관아이디", data: "ORG_ID", className: "colidx"}
        ,{title: "기관명", data: "ORG_NM", className: "colOrgNm"}   
        ,{title: "전화번호", data: "ORG_TEL", className: "colOrgTel"}
        ,{title: "공개여부", data: "SHOW_YN", className: "colshowYn"}
        ,{title: "기관이메일", data: "ORG_EMAIL", className: "colOrgEmail"}
        ,{title: "메모", data: "MEMO", className: "colMemo"}
        ,{title: "등록일", data: "REG_DT", className: "colRegDt"}
    ],
});