//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    xhr:{
        url:'/sys/userList.php',
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
        {title: "idx", data: "USER_CD", className: "colUserCd hidden"}
        ,{title: "아이디", data: "USER_ID", className: "colUserId"}
        ,{title: "성명", data: "USER_NM", className: "colUserNm"}
        ,{title: "권한", data: "USER_AUTH", className: "colUserAuth"}
        ,{title: "이메일", data: "EMAIL", className: "colEmail"}
        ,{title: "직책", data: "POSITION", className: "colPosition"}
        ,{title: "부서", data: "ORG_NM", className: "colOrgNm"}
        ,{title: "메모", data: "MEMO", className: "colMemo"}
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
        if(key>0){
            input.readOnly = false;
            input.style.background = "#FFF";
        }
    });
});
//행을 클릭했을때 xhr로 다시 끌어올 데이터는 각 페이지마다 다르기에 여기에서 지정
function trDataXHR(idx){ 
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/sys/userConfig.php?key="+psnlKey.value+"&USER_CD="+idx+"&CRUD=R"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
    console.log("/sys/userConfig.php?key="+psnlKey.value+"&USER_CD="+idx+"&CRUD=R");
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            var res = JSON.parse(xhr.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            if(res!=null){
                document.querySelector(".modalBody").querySelectorAll("input").forEach((input,key)=>{
                    switch(key){
                        case 0 :
                            input.value=res[0].USER_CD
                            break;
                        case 1 :
                            input.value=res[0].USER_ID;
                            input.readOnly = true;
                            input.style.background="#EEE";
                            break;
                        case 2 :
                            input.value=res[0].USER_NM
                            break;
                        case 3 :
                            input.value=""; //새 비밀번호란은 비워둔다.
                            break;  
                        case 4 :
                            input.value=""; //비밀번호 확인란도 비워둔다.
                            break;                  
                        case 5 :
                            input.value=res[0].EMAIL
                            break;
                        case 6 :
                            input.value=res[0].POSITION
                            break;
                        case 7 :
                            input.value=res[0].ORG_NM
                            break;
                        case 8 :
                            input.value=res[0].MEMO
                            break;
                    }
                });
                document.querySelector(".modalBody").querySelector("select").value=res[0].USER_AUTH; //대면 비대면은 셀렉트박스에서 구분
            }
        }else{
            console.log("userConfigXhr 정보 로드 에러");
        }
    }
}
//저장을 클릭했을때 xhr로 데이터를 기록
modalEdtBtn.addEventListener("click",()=>{
    let edtPass;    //비밀번호 정합검증 이벤트 추가
    let chkPass;
    document.querySelector(".modalBody").querySelectorAll("input").forEach((input,key)=>{
        if(key==3){edtPass=input;}if(key==4){chkPass=input;}
    });
    if(edtPass.value!=chkPass.value){
        console.log("비번 정합 검증에 실패하였습니다.");
        chkPass.style.background="#FEE";
        alert("비밀번호를 확인해주세요.");
        return false;
    }else{
        console.log("비번 정합 검증에 성공!");
        chkPass.style.background="#FFF";
    }
    let xhr = new XMLHttpRequest();
    let writeUrl='';
    try{
        document.querySelector(".modalForm").querySelectorAll("input").forEach((input,key)=>{
            if(key==0){writeUrl+="&USER_CD="+input.value}
            else if(key==1){
                if(input.value.length<3){alert("아이디는 필수 값입니다");throw new Error("stop loop");}
                writeUrl+="&USER_ID="+input.value}
            else if(key==2){writeUrl+="&USER_NM="+input.value}
            else if(key==3){
                if(input.value.length>1){
                    writeUrl+="&USER_PASS="+input.value;
                }
            }
            else if(key==5){writeUrl+="&EMAIL="+input.value}
            else if(key==6){writeUrl+="&POSITION="+input.value}
            else if(key==7){writeUrl+="&ORG_NM="+input.value}
            else if(key==8){writeUrl+="&MEMO="+input.value}
        });
    }catch(e){
        console.log("필수값 체크"); return false;
    }

    writeUrl+="&USER_AUTH="+document.querySelector(".modalBody").querySelector("select").value;
    console.log("/sys/userConfig.php?key="+psnlKey.value+writeUrl+"&CRUD=C");
    xhr.open("GET", "/sys/userConfig.php?key="+psnlKey.value+writeUrl+"&CRUD=C"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            var res = xhr.response; //응답 받은 JSON데이터를 파싱한다.
            ///////////////////////////////////////////////////여기수정중 여기수정중 여기수정중 여기수정중 여기수정중 여기수정중 여기수정중 여기수정중 여기수정중 여기수정중
            console.log("userConfig 정보 기록 완료");
            //alert("기록 되었습니다.");
            //mytbl.hrDt.xhr.where.USER_ID="7"; //이런 형식으로 조건문 위치를 싹 스캔하여 전체 필터 적용할것
            mytbl.show('myTbl'); //테이블의 아이디
            modalClose();

        }else{
            console.log("userConfig 정보 기록 에러!!!");
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
        if(key==0){deleteUrl+="&USER_CD="+input.value}
    });
    console.log("/sys/userConfig.php?key="+psnlKey.value+deleteUrl+"&CRUD=D");
    xhr.open("GET", "/sys/userConfig.php?key="+psnlKey.value+deleteUrl+"&CRUD=D"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
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