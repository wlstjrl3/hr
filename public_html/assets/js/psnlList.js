//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    xhr:{
        url:'/sys/psnlList.php',
        columXHR: '',
        key : psnlKey.value, //api 호출할 보안 개인인증키
        where: {
            nothing : '', //filter의 값 변동이 생기면 여기에 즉시 추가 값을 더하고 xhr을 호출한다.
            ORG_NM : document.getElementById("ORG_NM").value,
            PSNL_NM : document.getElementById("PSNL_NM").value,
            BAPT_NM : document.getElementById("BAPT_NM").value,
            PHONE_NUM : document.getElementById("PHONE_NUM").value,            
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
        {title: "idx", data: "PSNL_CD", className: "hidden"}
        ,{title: "소속조직", data: "ORG_NM", className: ""}
        ,{title: "성명", data: "PSNL_NM", className: ""}
        ,{title: "세례명", data: "BAPT_NM", className: ""}
        ,{title: "연락처", data: "PHONE_NUM", className: ""}
        ,{title: "주민번호", data: "PSNL_NUM", className: ""}
    ],
});
mytbl.show('myTbl'); //테이블의 아이디에 렌더링 한다(갱신도 가능)
mytbl.xportBind();

//이 아래로는 페이지 개별 모달창 이벤트를 지정
//신규버튼을 눌렀을때
newCol.addEventListener("click",()=>{
    document.querySelector(".modalForm").style.visibility="visible"; //모달창이 나타나게 한다.
    document.querySelector(".modalForm").style.opacity="1";     //투명도 애니메이션 적용을 위해 opacity가 0에서 1로 변경된다.
    document.getElementById("orgNm").focus();
    document.querySelector(".modalForm").querySelectorAll("input").forEach((input,key)=>{
        input.value="";
        if(key>1){
            input.readOnly = false;
            input.style.background = "#FFF";
        }
    });
});
//행을 클릭했을때 xhr로 다시 끌어올 데이터는 각 페이지마다 다르기에 여기에서 지정
function trDataXHR(idx){ 
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/sys/psnlConfig.php?key="+psnlKey.value+"&PSNL_CD="+idx+"&CRUD=R"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
    console.log("/sys/psnlConfig.php?key="+psnlKey.value+"&PSNL_CD="+idx+"&CRUD=R");
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            var res = JSON.parse(xhr.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            if(res!=null){
                /* 발령정보 페이지로 이동버튼 */
                document.getElementById("goTrsListBtn").addEventListener("click",()=>{location.href="/trsList?PSNL_CD="+res[0].PSNL_CD+"&PSNL_NM="+res[0].PSNL_NM+"&ORG_CD="+res[0].ORG_CD+"&ORG_NM="+res[0].ORG_NM});

                document.querySelector(".modalBody").querySelectorAll("input").forEach((input,key)=>{
                    switch(key){
                        case 0 :
                            input.value=res[0].PSNL_CD
                            break;
                        case 1 :
                            input.value=res[0].PSNL_NM;
                            break;  
                        case 2 :
                            input.value=res[0].BAPT_NM;
                            break;                  
                        case 3 :
                            input.value=res[0].PHONE_NUM
                            break;
                        case 4 :
                            input.value=res[0].PSNL_NUM
                            break;
                    }
                });
            }
        }else{
            console.log("psnlConfigXhr 정보 로드 에러");
        }
    }
}
//저장을 클릭했을때 xhr로 데이터를 기록
modalEdtBtn.addEventListener("click",()=>{
    let xhr = new XMLHttpRequest();
    let writeUrl='';
    try{
        document.querySelector(".modalForm").querySelectorAll("input").forEach((input,key)=>{
            if(key==0){writeUrl+="&PSNL_CD="+input.value}
            else if(key==1){
                if(input.value.length<2){alert("성명은 필수 값입니다");throw new Error("stop loop");}
                writeUrl+="&PSNL_NM="+input.value
            }
            else if(key==2){writeUrl+="&BAPT_NM="+input.value}
            else if(key==3){writeUrl+="&PHONE_NUM="+input.value}
            else if(key==4){writeUrl+="&PSNL_NUM="+input.value}
        });
    }catch(e){
        console.log("필수값 체크"); return false;
    }
    console.log("/sys/psnlConfig.php?key="+psnlKey.value+writeUrl+"&CRUD=C");
    xhr.open("GET", "/sys/psnlConfig.php?key="+psnlKey.value+writeUrl+"&CRUD=C"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            var res = xhr.response; //응답 받은 JSON데이터를 파싱한다.
            if(res==""){
                console.log("psnlConfig 정보 기록 완료");
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
        if(key==0){deleteUrl+="&PSNL_CD="+input.value}
    });
    console.log("/sys/psnlConfig.php?key="+psnlKey.value+deleteUrl+"&CRUD=D");
    xhr.open("GET", "/sys/psnlConfig.php?key="+psnlKey.value+deleteUrl+"&CRUD=D"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            //var res = JSON.parse(xhr.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            console.log("psnl 정보 삭제 완료");
            mytbl.show('myTbl'); //테이블의 아이디
            modalClose();
        }else{
            console.log("psnl 정보 제거 에러!!!");
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

//날짜 형식 자동 하이픈 추가를 위한 코드
document.querySelectorAll(".dateBox").forEach(dtBox => {
    dtBox.onkeyup = function(event){
        event = event || window.event;
        var _val = this.value.trim();
        this.value = autoHypenDate(_val) ;
    }
});
document.querySelectorAll(".shortDateBox").forEach(sdtBox => {
    sdtBox.onkeyup = function(event){
        event = event || window.event;
        var _val = this.value.trim();
        this.value = autoHypenShortDate(_val) ;
    }
});
//주민번호 형식 자동 하이픈 추가를 위한 코드
document.querySelectorAll(".juminNumBox").forEach(jmBox => {
    jmBox.onkeyup = function(event){
        event = event || window.event;
        var _val = this.value.trim();
        this.value = autoHypenJumin(_val) ;
    }
});
//전화번호 형식 자동 하이픈 추가를 위한 코드
document.querySelectorAll(".phoneNumBox").forEach(phBox => {
    phBox.onkeyup = function(event){
        event = event || window.event;
        var _val = this.value.trim();
        this.value = autoHypenPhone(_val) ;
    }
});