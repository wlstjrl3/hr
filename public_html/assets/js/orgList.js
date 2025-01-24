//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    xhr:{
        url:'/sys/orgList.php',
        columXHR: '',
        key : psnlKey.value, //api 호출할 보안 개인인증키
        where: {
            nothing : '', //filter의 값 변동이 생기면 여기에 즉시 추가 값을 더하고 xhr을 호출한다.
        },
        order: {
            column : '0',
            direction : 'desc',
        },
        page : 0, //표시되는 페이지에서 1이 빠진 값이다 즉 page:0 = 1페이지
        limit : 10, //만약 리미트가 0이라면 리미트 없이 전체 조회하는 것으로 처리 excel down등에서 0 처리해야 함!
    },
    columns: [
        //반드시 첫열이 key값이되는 열이 와야한다. 숨김여부는 class로 추가 지정
        {title: "idx", data: "ORG_CD", className: "hidden"}
        ,{title: "조직명", data: "ORG_NM", className: ""}
        ,{title: "상위조직", data: "UPR_ORG_NM", className: ""}
        ,{title: "조직타입", data: "ORG_TYPE", className: ""}
        ,{title: "내선번호", data: "ORG_IN_TEL", className: ""}
        ,{title: "전화번호", data: "ORG_OUT_TEL", className: ""}
        ,{title: "갱신일자", data: "REFRESH_DT", className: ""}
    ],
});
mytbl.show('myTbl'); //테이블의 아이디에 렌더링 한다(갱신도 가능)
mytbl.xportBind();

//이 아래로는 페이지 개별 모달창 이벤트를 지정
//신규버튼을 눌렀을때
newCol.addEventListener("click",()=>{
    document.querySelector(".modalForm").style.visibility="visible"; //모달창이 나타나게 한다.
    document.querySelector(".modalForm").style.opacity="1";     //투명도 애니메이션 적용을 위해 opacity가 0에서 1로 변경된다.
    document.querySelector(".modalForm").querySelectorAll("input").forEach((input,key)=>{
        input.value="";
        if(key==0){
            input.focus();
        }else if(key==2){
            input.value="지구";
        }
    });
});
//행을 클릭했을때 xhr로 다시 끌어올 데이터는 각 페이지마다 다르기에 여기에서 지정
function trDataXHR(idx){ 
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/sys/orgConfig.php?key="+psnlKey.value+"&ORG_CD="+idx+"&CRUD=R"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
    console.log("/sys/orgConfig.php?key="+psnlKey.value+"&ORG_CD="+idx+"&CRUD=R");
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            var res = JSON.parse(xhr.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            if(res!=null){
                document.querySelector(".modalBody").querySelectorAll("input").forEach((input,key)=>{
                    switch(key){
                        case 0 :
                            input.value=res[0].ORG_CD
                            break;
                        case 1 :
                            input.value=res[0].UPPR_ORG_CD;
                            break;
                        case 2 :
                            input.value=res[0].UPR_ORG_NM;
                            break;
                        case 3 :
                            input.value=res[0].ORG_NM;
                            break;
                        case 4 :
                            input.value=res[0].ORG_IN_TEL;
                            break;
                        case 5 :
                            input.value=res[0].ORG_OUT_TEL;
                            break;
                    }
                });
                document.querySelector(".modalBody").querySelector("select").value=res[0].ORG_TYPE; //대면 비대면은 셀렉트박스에서 구분
            }
        }else{
            console.log("orgConfigXhr 정보 로드 에러");
        }
    }
}
//저장을 클릭했을때 xhr로 데이터를 기록
modalEdtBtn.addEventListener("click",()=>{
    let xhr = new XMLHttpRequest();
    let writeUrl='';
    try{
        document.querySelector(".modalForm").querySelectorAll("input").forEach((input,key)=>{
            if(key==0){writeUrl+="&ORG_CD="+input.value}
            else if(key==1){
                if(input.value.length<4){alert("상위조직은 필수값입니다.");throw new Error("stop loop");}
                writeUrl+="&UPPR_ORG_CD="+input.value}
            else if(key==3){
                if(input.value.length<2){alert("조직명은 필수 값입니다");throw new Error("stop loop");}
                writeUrl+="&ORG_NM="+input.value
            }
            else if(key==4){writeUrl+="&ORG_IN_TEL="+input.value}
            else if(key==5){writeUrl+="&ORG_OUT_TEL="+input.value}
        });
    }catch(e){
        console.log("필수값 체크"); return false;
    }
    writeUrl+="&ORG_TYPE="+document.querySelector(".modalBody").querySelector("select").value;
    console.log("/sys/orgConfig.php?key="+psnlKey.value+writeUrl+"&CRUD=C");
    xhr.open("GET", "/sys/orgConfig.php?key="+psnlKey.value+writeUrl+"&CRUD=C"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            var res = xhr.response; //응답 받은 JSON데이터를 파싱한다.
            if(res==""){
                console.log("orgConfig 정보 기록 완료");
                mytbl.show('myTbl'); //테이블의 아이디
                modalClose();
            }else{
                alert("오류발생! 아래 코드를 개발자에게 전달해주세요.\n\n"+res);
            }
        }else{
            alert(xhr.statusText+" 정보 기록 에러!!!");
        }
    }    
});
//삭제를 클릭했을때 xhr로 데이터를 제거
modalDelBtn.addEventListener("click",()=>{
    if (!confirm("삭제 하시겠습니까?")) {
        return false;
    }                     
    let xhr = new XMLHttpRequest();
    let deleteUrl='';
    document.querySelector(".modalForm").querySelectorAll("input").forEach((input,key)=>{
        if(key==0){deleteUrl+="&ORG_CD="+input.value}
    });
    console.log("/sys/orgConfig.php?key="+psnlKey.value+deleteUrl+"&CRUD=D");
    xhr.open("GET", "/sys/orgConfig.php?key="+psnlKey.value+deleteUrl+"&CRUD=D"); xhr.send(); //XHR을 즉시 호출한다.
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            //var res = JSON.parse(xhr.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            console.log("org 정보 삭제 완료");
            mytbl.show('myTbl'); //테이블의 아이디
            modalClose();
        }else{
            console.log("org 정보 제거 에러!!!");
        }
    }    
});
//검색 필터링을 위한 코드
document.querySelectorAll(".filter").forEach((f,key)=>{
    f.addEventListener("change",() => {
        mytbl.hrDt.xhr.where[f.id]=f.value;
        mytbl.hrDt.xhr.page=0; //필터가 바뀌면 페이지 수도 바뀌므로 첫장으로 돌려보낸다.
        mytbl.show("myTbl");
    });
});
//대리구 필터 적용시 지구 필터의 자동 필터링 코드
document.getElementById("UUPR_ORG").addEventListener("change",evt=>{
    if(evt.currentTarget.value=="13061001"){
        document.querySelectorAll(".sw1d").forEach(tmp=>{tmp.style.display="block";});document.querySelectorAll(".sw2d").forEach(tmp=>{tmp.style.display="none";});
    }else if(evt.currentTarget.value=="13062001"){
        document.querySelectorAll(".sw1d").forEach(tmp=>{tmp.style.display="none";});document.querySelectorAll(".sw2d").forEach(tmp=>{tmp.style.display="block";});
    }else{
        document.querySelectorAll(".sw1d").forEach(tmp=>{tmp.style.display="block";});document.querySelectorAll(".sw2d").forEach(tmp=>{tmp.style.display="block";});
    }
});
//조직 검색 팝업 띄우기
document.getElementById("orgSerchPop").addEventListener('click',()=>{
    window.open('/components/orgPopup.php', '조직 검색', 'width=320, height=500');
});
document.getElementById("orgNm").addEventListener("keyup", (evt)=>{
    if (evt.keyCode == 13) {
        window.open('/components/orgPopup.php', '조직 검색', 'width=320, height=500');
    }
});