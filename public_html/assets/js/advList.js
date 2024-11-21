//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    tblType:'pageLink',
    pageLinkHref:'/grdList?',
    xhr:{
        url:'/sys/advList.php',
        columXHR: '',
        key : psnlKey.value, //api 호출할 보안 개인인증키
        where: {
            TMP : "tmp",
        },
        order: {
            column : '5',
            direction : 'asc',
        },
        page : 0, //표시되는 페이지에서 1이 빠진 값이다 즉 page:0 = 1페이지
        limit : 10, //만약 리미트가 0이라면 리미트 없이 전체 조회하는 것으로 처리 excel down등에서 0 처리해야 함!
    },
    columns: [
        //반드시 첫열이 key값이되는 열이 와야한다. 숨김여부는 class로 추가 지정
        {title: "개인번호", data: "PSNL_CD", className: "hidden"}
        ,{title: "본당코드", data: "ORG_CD", className: "hidden"}
        ,{title: "본당명", data: "ORG_NM", className: ""}
        ,{title: "성명", data: "PSNL_NM", className: ""}
        ,{title: "직책", data: "POSITION", className: ""}
        ,{title: "급", data: "GRD_GRADE", className: ""}
        ,{title: "지속연차", data: "CNTT", className: ""}
    ],
});
mytbl.show('myTbl'); //테이블의 아이디에 렌더링 한다(갱신도 가능)
mytbl.xportBind();

//행을 클릭했을때 xhr로 다시 끌어올 데이터는 각 페이지마다 다르기에 여기에서 지정
function trDataXHR(idx){ 
    console.log("행클릭");
}