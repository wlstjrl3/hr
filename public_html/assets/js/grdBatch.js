document.getElementById("PSNL_NM").focus();

//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    tblType:"chkTbl",
    xhr:{
        url:'/sys/grdBatchList.php',
        columXHR: '',
        key : psnlKey.value, //api 호출할 보안 개인인증키
        where: {
            sample : "sample",
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
        {title: "idx", data: "GRD_CD", className: "hidden"}
        
        ,{title: "조직", data: "ORG_NM", className: ""}
        ,{title: "직원ID", data: "PSNL_CD", className: ""}
        ,{title: "직원명", data: "PSNL_NM", className: ""}
        ,{title: "직책", data: "POSITION", className: ""}
        ,{title: "승급일", data: "ADVANCE_DT", className: ""}
        ,{title: "급", data: "GRD_GRADE", className: ""}
        ,{title: "호", data: "GRD_PAY", className: ""}
        ,{title: "메모", data: "GRD_DTL", className: ""}
        ,{title: "등록일시", data: "REG_DT", className: ""}
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
        qrParams = new URLSearchParams(window.location.search);
        if(qrParams.get('WORK_TYPE')=='계약직'&&key==3){
            input.disabled = true;
        }else if(key==3){
            input.disabled = false;
        }
    });
});

//일괄처리를 클릭했을때 xhr로 데이터를 기록
batchInsert.addEventListener("click",()=>{
    const userInputDt = prompt(" 승급일을 입력하세요:");
    if (userInputDt !== null) {
        if(isValidDateFormat(userInputDt)){
            console.log(`입력한 날짜: ${userInputDt}`);
            let chkedBoxes = [];
            document.querySelectorAll(".chkTd input").forEach((chkBox,key) => {
                if(chkBox.checked==true){
                    let psnlCd = chkBox.parentNode.nextSibling.nextSibling.nextSibling.innerText;
                    chkedBoxes.push(psnlCd);
                }
            });
            const userConfirmed = confirm(`총 ${chkedBoxes.length}명을\n승급일 ${userInputDt} 로\n일괄 처리합니다.`);
            if(userConfirmed){
                var xhr = new XMLHttpRequest();//XMLHttpRequest 객체 생성
                xhr.open('POST', '/sys/grdBatchConfig.php?key='+psnlKey.value+'&CRUD=C', true);//요청을 보낼 방식, 주소, 비동기여부 설정                
                xhr.setRequestHeader('Content-type', 'application/json;charset=UTF-8');//HTTP 요청 헤더 설정

                const jsonDate = {
                    date : userInputDt,
                    psnlCd : chkedBoxes,
                }

                xhr.send(JSON.stringify(jsonDate));  //JSON,stringify를 이용하여 json으로 변환
                xhr.onload = () => {//통신후 작업
                    if (xhr.status == 200) {//통신 성공
                        alert(xhr.response);
                        mytbl.show("myTbl");
                    }else{
                        alert(`통신 실패 type2`);
                    }
                }
                //xhr.onloadend = () => {}
            }
            else{
                alert("진행이 취소되었습니다.");
            }
        }else{
            alert("잘못된 날짜 형식입니다. YYYY-MM-DD 형식으로 다시 입력해주세요.");
            return false;
        }
    }
});
//일괄삭제를 클릭했을때 xhr로 데이터를 제거
batchDel.addEventListener("click",()=>{
    let chkedBoxes = [];
    document.querySelectorAll(".chkTd input").forEach((chkBox,key) => {
        if(chkBox.checked==true){
            let grdCd = chkBox.parentNode.nextSibling.innerText;
            chkedBoxes.push(grdCd);
        }
    });
    const userConfirmed = confirm(`삭제된 데이터는 복구 할 수 없습니다.\n승급관리 > 개별 급호봉 관리 페이지에서\n엑셀 다운로드 후 진행하시기 바랍니다.\n\n총 ${chkedBoxes.length}명의 승급 정보를 일괄삭제 합니까?`);
    if(userConfirmed){
        var xhr = new XMLHttpRequest();//XMLHttpRequest 객체 생성
        xhr.open('POST', '/sys/grdBatchConfig.php?key='+psnlKey.value+'&CRUD=D', true);//요청을 보낼 방식, 주소, 비동기여부 설정                
        xhr.setRequestHeader('Content-type', 'application/json;charset=UTF-8');//HTTP 요청 헤더 설정

        const jsonDate = {
            grdCd : chkedBoxes,
        }

        xhr.send(JSON.stringify(jsonDate));  //JSON,stringify를 이용하여 json으로 변환
        xhr.onload = () => {//통신후 작업
            if (xhr.status == 200) {//통신 성공
                debugger;
                alert(xhr.response);
                mytbl.show("myTbl");
            }else{
                alert(`통신 실패 type2`);
            }
        }
        //xhr.onloadend = () => {}
    }
    else{
        alert("진행이 취소되었습니다.");
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
//날짜 형식이 유효한지 검증을 위한 코드
function isValidDateFormat(date) {
    const regex = /^\d{4}-\d{2}-\d{2}$/; // YYYY-MM-DD 형식 검증
    if (!regex.test(date)) {
      return false;
    }
    
    // 입력된 날짜가 실제 유효한 날짜인지 확인
    const [year, month, day] = date.split("-").map(Number);
    const dateObj = new Date(year, month - 1, day);
    return (
      dateObj.getFullYear() === year &&
      dateObj.getMonth() === month - 1 &&
      dateObj.getDate() === day
    );
}