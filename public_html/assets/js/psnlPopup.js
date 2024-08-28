// 부모 창에 작성된 #parentInput의 값 얻어오기
// opener == 부모창
const parentValue = opener.document.getElementById('PSNL_NM').value;
document.querySelector('#PSNL_NM').value = parentValue;

//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    xhr:{
        url:'/sys/psnlPopSearch.php',
        columXHR: '',
        //key : psnlKey.value, //api 호출할 보안 개인인증키
        where: {
            PSNL_NM : parentValue, //filter의 값 변동이 생기면 여기에 즉시 추가 값을 더하고 xhr을 호출한다.
        },
        order: {
            column : '0',
            direction : 'desc',
        },
        page : 0, //표시되는 페이지에서 1이 빠진 값이다 즉 page:0 = 1페이지
        limit : 5, //만약 리미트가 0이라면 리미트 없이 전체 조회하는 것으로 처리 excel down등에서 0 처리해야 함!
    },
    columns: [
        //반드시 첫열이 key값이되는 열이 와야한다. 숨김여부는 class로 추가 지정
        {title: "직원코드", data: "PSNL_CD", className: ""}
        ,{title: "조직명", data: "ORG_NM", className: ""}
        ,{title: "성명", data: "PSNL_NM", className: ""}
        ,{title: "세례명", data: "BAPT_NM", className: ""}
        ,{title: "직책", data: "POSITION", className: ""}
    ],
});
mytbl.show('myTbl'); //테이블의 아이디에 렌더링 한다(갱신도 가능)

//행을 클릭했을때 이벤트 추가(.hr_tbl 이 바인딩 된 후에 적용되어야 하기에 타임아웃 지연 로딩)
window.onload = function() {
    document.getElementById("PSNL_NM").focus();
    setTimeout(() => {
        let tmp = document.querySelector(".hr_tbl").children[1].children;
        if(tmp.length==1&&tmp[0].children[0].innerText!="데이터가 없습니다."){ //값이 있으면서 단 하나만 존재한다면 즉시 바인딩 처리한다.
            opener.document.getElementById('PSNL_CD').value = tmp[0].children[1].innerText
            opener.document.getElementById('ORG_NM').value = tmp[0].children[2].innerText;
            opener.document.getElementById('PSNL_NM').value = tmp[0].children[3].innerText;
            opener.document.getElementById('POSITION').value = tmp[0].children[5].innerText;
            //opener.document.getElementById('psnlSerchPop').focus();
            opener.document.getElementById('psnlSerchPop').parentElement.parentElement.nextElementSibling.querySelector("input").focus();
            opener.myTblRefresh();
            window.close();
        }
        document.querySelector(".hr_tbl").querySelectorAll('tr').forEach(tr => { //아니라면 각 행에 클릭 이벤트를 추가한다.
            tr.addEventListener('click', (target)=>{
                opener.document.getElementById('PSNL_CD').value = target.currentTarget.children[1].innerText;
                opener.document.getElementById('ORG_NM').value = target.currentTarget.children[2].innerText;
                opener.document.getElementById('PSNL_NM').value = target.currentTarget.children[3].innerText;
                opener.document.getElementById('POSITION').value = target.currentTarget.children[5].innerText;
                //opener.document.getElementById('psnlSerchPop').focus();
                opener.document.getElementById('psnlSerchPop').parentElement.parentElement.nextElementSibling.querySelector("input").focus();    
                opener.myTblRefresh();            
                window.close();
            });
        });
    }, 800);
};

//검색 필터링을 위한 코드
document.querySelectorAll(".filter").forEach((f,key)=>{
    f.addEventListener("change",() => {
        mytbl.hrDt.xhr.where[f.id]=f.value;
        mytbl.hrDt.xhr.page=0; //필터가 바뀌면 페이지 수도 바뀌므로 첫장으로 돌려보낸다.
        mytbl.show("myTbl");
        setTimeout(() => {
            let tmp = document.querySelector(".hr_tbl").children[1].children;
            if(tmp.length==1&&tmp[0].children[0].innerText!="데이터가 없습니다."){ //값이 있으면서 단 하나만 존재한다면 즉시 바인딩 처리한다.
                opener.document.getElementById('PSNL_CD').value = tmp[0].children[1].innerText
                opener.document.getElementById('ORG_NM').value = tmp[0].children[2].innerText;
                opener.document.getElementById('PSNL_NM').value = tmp[0].children[3].innerText;
                opener.document.getElementById('POSITION').value = tmp[0].children[5].innerText;
                //opener.document.getElementById('psnlSerchPop').focus();
                opener.document.getElementById('psnlSerchPop').parentElement.parentElement.nextElementSibling.querySelector("input").focus();                
                opener.myTblRefresh();
                window.close();
            }
        }, 200);          
    });
});

//팝업창의 경우 행을 클릭했을때 개별 정보 끌어오기가 아닌 무응답으로 처리
function trDataXHR(idx){ }