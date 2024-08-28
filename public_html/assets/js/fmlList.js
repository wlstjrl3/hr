document.getElementById("PSNL_NM").focus();

//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    xhr:{
        url:'/sys/fmlList.php',
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
        {title: "idx", data: "FML_CD", className: "hidden"}
        ,{title: "조직", data: "ORG_NM", className: ""}
        ,{title: "직원명", data: "PSNL_NM", className: ""}
        ,{title: "직책", data: "POSITION", className: ""}        
        ,{title: "가족성명", data: "FML_NM", className: ""}
        ,{title: "가족관계", data: "FML_RELATION", className: ""}
        ,{title: "생년월일", data: "FML_BIRTH", className: ""}
        ,{title: "상세정보", data: "FML_DTL", className: ""}
        ,{title: "가족수당", data: "FML_PAY", className: ""}
        ,{title: "수당시작일", data: "FML_STT_DT", className: ""}
        ,{title: "수당종료일", data: "FML_END_DT", className: ""}
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
    xhr.open("GET", "/sys/fmlConfig.php?key="+psnlKey.value+"&FML_CD="+idx+"&CRUD=R"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
    console.log("/sys/fmlConfig.php?key="+psnlKey.value+"&FML_CD="+idx+"&CRUD=R");
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
                            input.value=res[0].FML_CD
                            break;
                        case 1 :
                            input.value=res[0].FML_NM;
                            break;
                        case 2 :
                            input.value=res[0].FML_BIRTH;
                            break;
                        case 3 :
                            input.value=res[0].FML_DTL;
                            break;
                        case 4 :
                            input.value=res[0].FML_PAY;
                            break;
                        case 5 :
                            input.value=res[0].FML_STT_DT;
                            break;
                        case 6 :
                            input.value=res[0].FML_END_DT;
                            break;
                    }
                });
                document.querySelector(".modalBody").querySelector("select").value=res[0].FML_RELATION; //대면 비대면은 셀렉트박스에서 구분
                document.querySelector(".modalBody").querySelector("b").innerHTML=res[0].ORG_NM+" "+res[0].POSITION+" "+res[0].PSNL_NM;
            }
        }else{
            console.log("fmlConfigXhr 정보 로드 에러");
        }
    }
}
//저장을 클릭했을때 xhr로 데이터를 기록
modalEdtBtn.addEventListener("click",()=>{
    let xhr = new XMLHttpRequest();
    let writeUrl='';
    try{
        document.querySelector(".modalForm").querySelectorAll("input").forEach((input,key)=>{
            if(key==0){writeUrl+="&FML_CD="+input.value}
            else if(key==1){
                if(input.value.length<2){alert("가족성명은 필수값입니다.");throw new Error("stop loop");}
                writeUrl+="&FML_NM="+input.value
            }
            else if(key==2){
                if(input.value.length<2){alert("가족생년월일은 필수 값입니다");throw new Error("stop loop");}
                writeUrl+="&FML_BIRTH="+input.value
            }
            else if(key==3){writeUrl+="&FML_DTL="+input.value}
            else if(key==4){writeUrl+="&FML_PAY="+input.value}
            else if(key==5){writeUrl+="&FML_STT_DT="+input.value}
            else if(key==6){writeUrl+="&FML_END_DT="+input.value}
        });
    }catch(e){
        console.log("필수값 체크"); return false;
    }
    writeUrl+="&FML_RELATION="+document.querySelector(".modalBody").querySelector("select").value;
    writeUrl+="&PSNL_CD="+document.getElementById("PSNL_CD").value;
    console.log("/sys/fmlConfig.php?key="+psnlKey.value+writeUrl+"&CRUD=C");
    xhr.open("GET", "/sys/fmlConfig.php?key="+psnlKey.value+writeUrl+"&CRUD=C"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            var res = xhr.response; //응답 받은 JSON데이터를 파싱한다.
            if(res==""){
                console.log("fmlConfig 정보 기록 완료");
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
        if(key==0){deleteUrl+="&FML_CD="+input.value}
    });
    console.log("/sys/fmlConfig.php?key="+psnlKey.value+deleteUrl+"&CRUD=D");
    xhr.open("GET", "/sys/fmlConfig.php?key="+psnlKey.value+deleteUrl+"&CRUD=D"); xhr.send(); //XHR을 즉시 호출한다.
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            //var res = JSON.parse(xhr.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            console.log("fmlConfig 정보 삭제 완료");
            mytbl.show('myTbl'); //테이블의 아이디
            modalClose();
        }else{
            console.log("fmlConfig 정보 제거 에러!!!");
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