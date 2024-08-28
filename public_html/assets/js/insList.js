document.getElementById("PSNL_NM").focus();

//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    xhr:{
        url:'/sys/insList.php',
        columXHR: '',
        key : psnlKey.value, //api 호출할 보안 개인인증키
        where: {
            PSNL_CD : '', //filter의 값 변동이 생기면 여기에 즉시 추가 값을 더하고 xhr을 호출한다.
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
        {title: "idx", data: "INS_CD", className: "hidden"}
        ,{title: "조직", data: "ORG_NM", className: ""}
        ,{title: "직원명", data: "PSNL_NM", className: ""}
        ,{title: "직책", data: "POSITION", className: ""}
        ,{title: "가입금액", data: "INS_AMOUNT", className: ""}
        ,{title: "가입일", data: "INS_STT_DT", className: ""}
        ,{title: "종료일", data: "INS_END_DT", className: ""}
        ,{title: "상세정보", data: "INS_DTL", className: ""}
    ],
});
mytbl.show('myTbl'); //테이블의 아이디에 렌더링 한다(갱신도 가능)
mytbl.xportBind();

//이 아래로는 페이지 개별 모달창 이벤트를 지정
//신규버튼을 눌렀을때
newCol.addEventListener("click",()=>{
    if(document.getElementById("PSNL_CD").value.length<1){
        alert("먼저 개인코드를 조회한 후 재시도 하시기 바랍니다.");
        return false;
    }
    document.querySelector(".modalBody").querySelector("b").innerHTML=ORG_NM.value+" "+POSITION.value+" "+PSNL_NM.value;
    document.querySelector(".modalForm").style.visibility="visible"; //모달창이 나타나게 한다.
    document.querySelector(".modalForm").style.opacity="1";     //투명도 애니메이션 적용을 위해 opacity가 0에서 1로 변경된다.
    document.querySelector(".modalForm").querySelectorAll("input").forEach((input,key)=>{
        input.value="";
        if(key==1){
            input.focus();
        }
    });
});
//행을 클릭했을때 xhr로 다시 끌어올 데이터는 각 페이지마다 다르기에 여기에서 지정
function trDataXHR(idx){ 
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/sys/insConfig.php?key="+psnlKey.value+"&INS_CD="+idx+"&CRUD=R"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
    console.log("/sys/insConfig.php?key="+psnlKey.value+"&INS_CD="+idx+"&CRUD=R");
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            var res = JSON.parse(xhr.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            if(res!=null){
                document.getElementById("PSNL_CD").value=res[0].PSNL_CD;
                document.getElementById("ORG_NM").value=res[0].ORG_NM;
                document.getElementById("POSITION").value=res[0].POSITION;
                document.getElementById("PSNL_NM").value=res[0].PSNL_NM;
                document.querySelector(".modalBody").querySelectorAll("input").forEach((input,key)=>{
                    switch(key){
                        case 0 :
                            input.value=res[0].INS_CD
                            break;
                        case 1 :
                            input.value=res[0].INS_AMOUNT;
                            break;
                        case 2 :
                            input.value=res[0].INS_STT_DT;
                            break;
                        case 3 :
                            input.value=res[0].INS_END_DT;
                            break;
                        case 4 :
                            input.value=res[0].INS_DTL;
                            break;
                    }
                });
                document.querySelector(".modalBody").querySelector("b").innerHTML=res[0].ORG_NM+" "+res[0].POSITION+" "+res[0].PSNL_NM;
            }
        }else{
            console.log("insConfigXhr 정보 로드 에러");
        }
    }
}
//저장을 클릭했을때 xhr로 데이터를 기록
modalEdtBtn.addEventListener("click",()=>{
    let xhr = new XMLHttpRequest();
    let writeUrl='';
    try{
        document.querySelector(".modalForm").querySelectorAll("input").forEach((input,key)=>{
            if(key==0){writeUrl+="&INS_CD="+input.value}
            else if(key==1){
                if(input.value.length<2){alert("금액은 필수값입니다.");throw new Error("stop loop");}
                writeUrl+="&INS_AMOUNT="+input.value
            }
            else if(key==2){
                if(input.value.length<2){alert("가입일은 필수 값입니다");throw new Error("stop loop");}
                writeUrl+="&INS_STT_DT="+input.value
            }
            else if(key==3){
                if(input.value.length<2){alert("만료일은 필수 값입니다");throw new Error("stop loop");}
                writeUrl+="&INS_END_DT="+input.value
            }
            else if(key==4){writeUrl+="&INS_DTL="+input.value}
        });
    }catch(e){
        console.log("필수값 체크"); return false;
    }
    writeUrl+="&PSNL_CD="+document.getElementById("PSNL_CD").value;
    console.log("/sys/insConfig.php?key="+psnlKey.value+writeUrl+"&CRUD=C");
    xhr.open("GET", "/sys/insConfig.php?key="+psnlKey.value+writeUrl+"&CRUD=C"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            var res = xhr.response; //응답 받은 JSON데이터를 파싱한다.
            console.log("insConfig 정보 기록 완료");
            mytbl.show('myTbl'); //테이블의 아이디
            modalClose();
        }else{
            console.log("insConfig 정보 기록 에러!!!");
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
        if(key==0){deleteUrl+="&INS_CD="+input.value}
    });
    console.log("/sys/insConfig.php?key="+psnlKey.value+deleteUrl+"&CRUD=D");
    xhr.open("GET", "/sys/insConfig.php?key="+psnlKey.value+deleteUrl+"&CRUD=D"); xhr.send(); //XHR을 즉시 호출한다.
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            //var res = JSON.parse(xhr.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            console.log("insConfig 정보 삭제 완료");
            mytbl.show('myTbl'); //테이블의 아이디
            modalClose();
        }else{
            console.log("insConfig 정보 제거 에러!!!");
        }
    }    
});
//검색 필터링을 위한 코드
document.querySelectorAll(".filter").forEach((f,key)=>{
    f.addEventListener("change",() => {
        mytbl.hrDt.xhr.where['PSNL_CD']=document.getElementById("PSNL_CD").value;
        mytbl.hrDt.xhr.where[f.id]=f.value;
        mytbl.hrDt.xhr.page=0; //필터가 바뀌면 페이지 수도 바뀌므로 첫장으로 돌려보낸다.
        mytbl.show("myTbl");
    });
});
//날짜 형식 자동 하이픈 추가를 위한 코드
document.querySelectorAll(".dateBox").forEach(dtBox => {
    dtBox.onkeyup = function(event){
        event = event || window.event;
        var _val = this.value.trim();
        this.value = autoHypenDate(_val) ;
    }
});
//직원코드 검색 팝업 띄우기
document.getElementById("psnlSerchPop").addEventListener('click',()=>{
    window.open('/components/psnlPopup.php', '조직 검색', 'width=500, height=500');
});
document.getElementById("PSNL_NM").addEventListener("keyup", (evt)=>{
    if (evt.keyCode == 13) {
        window.open('/components/psnlPopup.php', '조직 검색', 'width=500, height=500');
    }
});
function myTblRefresh(){ //팝업창에서 정보를 선택하면 검색 필터링을 진행한다.
    document.querySelectorAll(".filter").forEach((f,key)=>{
        mytbl.hrDt.xhr.where['PSNL_CD']=document.getElementById("PSNL_CD").value;
        mytbl.hrDt.xhr.where[f.id]=f.value;
        mytbl.hrDt.xhr.page=0; //필터가 바뀌면 페이지 수도 바뀌므로 첫장으로 돌려보낸다.
        mytbl.show("myTbl");
    });    
}