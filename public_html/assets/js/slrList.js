//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    xhr:{
        url:'/sys/slrList.php',
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
        {title: "idx", data: "SLR_CD", className: "hidden"}
        ,{title: "기준연도", data: "SLR_YEAR", className: ""}
        ,{title: "타입", data: "SLR_TYPE", className: ""}
        ,{title: "급", data: "SLR_GRADE", className: ""}
        ,{title: "호", data: "SLR_PAY", className: ""}
        ,{title: "기본급", data: "NORMAL_PAY", className: ""}
        ,{title: "법정수당", data: "LEGAL_PAY", className: ""}
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
        if(key>1){
            if(key==1){
                input.focus();
            }
            input.readOnly = false;
            input.style.background = "#FFF";
        }
    });
});
//행을 클릭했을때 xhr로 다시 끌어올 데이터는 각 페이지마다 다르기에 여기에서 지정
function trDataXHR(idx){ 
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/sys/slrConfig.php?key="+psnlKey.value+"&SLR_CD="+idx+"&CRUD=R"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
    console.log("/sys/slrConfig.php?key="+psnlKey.value+"&SLR_CD="+idx+"&CRUD=R");
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            var res = JSON.parse(xhr.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            if(res!=null){
                document.querySelector(".modalBody").querySelectorAll("input").forEach((input,key)=>{
                    switch(key){
                        case 0 :
                            input.value=res[0].SLR_CD
                            break;
                        case 1 :
                            input.value=res[0].SLR_YEAR;
                            break;
                        case 2 :
                            input.value=res[0].SLR_GRADE;
                            break;
                        case 3 :
                            input.value=res[0].SLR_PAY;
                            break;
                        case 4 :
                            input.value=res[0].NORMAL_PAY;
                            break;
                        case 5 :
                            input.value=res[0].LEGAL_PAY;
                            break;
                    }
                });
            }
        }else{
            console.log("slrConfigXhr 정보 로드 에러");
        }
    }
}
//저장을 클릭했을때 xhr로 데이터를 기록
modalEdtBtn.addEventListener("click",()=>{
    let xhr = new XMLHttpRequest();
    let writeUrl='';
    try{
        document.querySelector(".modalForm").querySelectorAll("input").forEach((input,key)=>{
            if(key==0){writeUrl+="&SLR_CD="+input.value}
            else if(key==1){
                if(input.value<1900 | input.value>2200){alert("기준연도 값을 확인해주세요.");throw new Error("stop loop");}
                writeUrl+="&SLR_YEAR="+input.value}
            else if(key==2){
                if(input.value<4 | input.value>10){alert("급의 입력범위를 벗어났습니다.");throw new Error("stop loop");}
                writeUrl+="&SLR_GRADE="+input.value
            }
            else if(key==3){
                if(input.value<1 | input.value>50){alert("호의 입력범위를 벗어났습니다");throw new Error("stop loop");}
                writeUrl+="&SLR_PAY="+input.value
            }
            else if(key==4){writeUrl+="&NORMAL_PAY="+input.value}
            else if(key==5){writeUrl+="&LEGAL_PAY="+input.value}
        });
        if(document.querySelector(".modalForm").querySelector("select").value==""){alert("타입은 필수 값입니다.");throw new Error("stop");}
        writeUrl+="&SLR_TYPE="+document.querySelector(".modalForm").querySelector("select").value;
    }catch(e){
        console.log("필수값 체크"); return false;
    }
    console.log("/sys/slrConfig.php?key="+psnlKey.value+writeUrl+"&CRUD=C");
    xhr.open("GET", "/sys/slrConfig.php?key="+psnlKey.value+writeUrl+"&CRUD=C"); xhr.send(); //XHR을 즉시 호출한다. psnlKey는 추후 암호화 하여 재적용 예정
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            var res = xhr.response; //응답 받은 JSON데이터를 파싱한다.
            if(res==""){
                console.log("slrConfig 정보 기록 완료");
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
        if(key==0){deleteUrl+="&SLR_CD="+input.value}
    });
    console.log("/sys/slrConfig.php?key="+psnlKey.value+deleteUrl+"&CRUD=D");
    xhr.open("GET", "/sys/slrConfig.php?key="+psnlKey.value+deleteUrl+"&CRUD=D"); xhr.send(); //XHR을 즉시 호출한다.
    xhr.onload = () => {
        if (xhr.status === 200) { //XHR 응답이 존재한다면
            //var res = JSON.parse(xhr.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            console.log("slr 정보 삭제 완료");
            mytbl.show('myTbl'); //테이블의 아이디
            modalClose();
        }else{
            console.log("slr 정보 제거 에러!!!");
        }
    }    
});
//엑셀 업로드를 위한 코드
document.querySelector("#file").addEventListener('change',target=>{
    let fileName = target.currentTarget.value;
    document.querySelector(".upload-name").value=fileName; //파일명 표시
    // [input 태그에 파일이 선텍 된 경우 로직 수행]
    if(fileName.slice(-4)=='xlsx'||fileName.slice(-4)=='.xls'){
        let input = target.currentTarget; //??? 사용되는 코드인가?
        let reader = new FileReader();
        reader.onload = function () {
            let data = reader.result;
            let workBook = XLSX.read(data, { type: 'binary' });
            workBook.SheetNames.forEach(function (sheetName) {
                let rows = XLSX.utils.sheet_to_json(workBook.Sheets[sheetName]);
                if(rows[0]['SLR_YEAR']){
                    if (!confirm("기준연도:"+rows[0]['SLR_YEAR']+"["+rows[0]['SLR_GRADE']+"급/"+rows[0]['SLR_PAY']+"호]"+rows[0]['NORMAL_PAY']+"원 외 "+(rows.length-1)+"건 의 정보를 일괄등록 하시겠습니까?")) {
                        // 취소(아니오) 버튼 클릭 시 이벤트
                        return false;
                    }                     
                }else{
                    alert("파일 구조가 잘못되었습니다.");
                    return false;
                }
                var xhr = new XMLHttpRequest();//XMLHttpRequest 객체 생성
                xhr.open('POST', '/sys/slrBatchInsert.php?key='+psnlKey.value, true);//요청을 보낼 방식, 주소, 비동기여부 설정                
                xhr.setRequestHeader('Content-type', 'application/json;charset=UTF-8');//HTTP 요청 헤더 설정
                xhr.send(JSON.stringify(rows));  //JSON,stringify를 이용하여 json으로 변환해야만 php상에서 엑셀 텍스트가 json으로 인식됨
                xhr.onload = () => {//통신후 작업
                    if (xhr.status == 200) {//통신 성공
                        //console.log(xhr.response); 
                        mytbl.show("myTbl");
                    }else{
                        console.log("통신 실패 type1");//통신 실패
                    }
                }
                xhr.onloadend = () => {}
            })
        };

        // [input 태그 파일 읽음]
        reader.readAsBinaryString(input.files[0]);
    }else{
        alert("엑셀 파일형식이 아닙니다.");
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