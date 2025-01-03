//데이터테이블을 지정한다.
var mytbl = new hr_tbl({
    xhr:{
        url:'/sys/psnlTotal.php',
        columXHR: '',
        key : psnlKey.value, //api 호출할 보안 개인인증키
        where: {
            TRS_TYPE : '1', //filter 값 변동 전 초기조건값으로 재직구분이 '재직'인 데이터만
            ORG_NM : document.getElementById("ORG_NM").value,
            PSNL_NM : document.getElementById("PSNL_NM").value,
            BAPT_NM : document.getElementById("BAPT_NM").value,
            POSITION : document.getElementById("POSITION").value,
            PHONE_NUM : document.getElementById("PHONE_NUM").value,
        },
        order: {
            column : '1',
            direction : 'asc',
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
        ,{title: "직책", data: "POSITION", className: ""}
        ,{title: "고용형태", data: "WORK_TYPE", className: ""}
        ,{title: "연락처", data: "PHONE_NUM", className: "hidden"}
        ,{title: "주민번호", data: "PSNL_NUM", className: "hidden"}
        ,{title: "재직구분", data: "TRS_TYPE", className: "hidden"}
        ,{title: "입/퇴사일", data: "TRS_DT", className: ""}
        ,{title: "임용일", data: "APP_DT", className: "hidden"}
        ,{title: "경과(근속)", data: "TRS_ELAPSE", className: ""}
        ,{title: "승급일", data: "ADVANCE_DT", className: ""}
        ,{title: "분기", data: "ADVANCE_RNG", className: "hidden"}
        ,{title: "급(Lv)", data: "GRD_GRADE", className: ""}
        ,{title: "호", data: "GRD_PAY", className: ""}
        ,{title: "기본급", data: "NORMAL_PAY", className: ""}
        ,{title: "법정수당", data: "LEGAL_PAY", className: ""}
        ,{title: "신자수", data: "PERSON_CNT", className: "hidden"}
        ,{title: "직책수당", data: "ADJUST_PAY1", className: ""}
        ,{title: "가족수당", data: "FAMILY_PAY", className: ""}
        ,{title: "자격수당", data: "ADJUST_PAY2", className: ""}
        ,{title: "장애인수당", data: "ADJUST_PAY3", className: ""}
        ,{title: "조정수당", data: "ADJUST_PAY4", className: ""}
        ,{title: "예상급여", data: "EXPECT_PAY", className: ""}
    ],
});

//행을 클릭했을때 xhr로 다시 끌어올 데이터
function trDataXHR(idx){
    //이전 정보 초기화 INIT
    document.getElementById("mdBdOrgNm").innerHTML="";
    document.getElementById("mdBdPsnlNm").innerHTML="";
    document.getElementById("mdBdBaptNm").innerHTML="";
    document.getElementById("mdBdPsnlNum").innerHTML="";
    document.getElementById("mdBdPhoneNum").innerHTML="";
    document.getElementById("mdBdPosition").innerHTML="";
    document.getElementById("mdBdTrsType").innerHTML="";
    document.getElementById("mdBdTrsDt").innerHTML="";
    document.getElementById("mdBdWorkType").innerHTML="";
    document.getElementById("mdBdGrdPay").innerHTML="";
    document.getElementById("fmlTbl").innerHTML="";
    document.getElementById("adjTbl").innerHTML="";
    document.getElementById("opiTbl").innerHTML="";

    let xhr1 = new XMLHttpRequest();
    //let xhr2 = new XMLHttpRequest();
    //let xhr3 = new XMLHttpRequest();
    let xhr4 = new XMLHttpRequest();
    let xhr5 = new XMLHttpRequest();
    let xhr6 = new XMLHttpRequest();
    xhr1.open("GET", "/sys/psnlTotal.php?key="+psnlKey.value+"&PSNL_CD="+idx+"&CRUD=R"); xhr1.send();
    console.log("/sys/psnlTotal.php?key="+psnlKey.value+"&PSNL_CD="+idx+"&CRUD=R");
    xhr1.onload = () => {if (xhr1.status === 200) {var res = JSON.parse(xhr1.response)['data'];if(res!=null){

        if(res[0].POSITION=="가사사용인"){
            document.querySelectorAll(".modalFooter button").forEach(ftBtn => {
                ftBtn.style.display="none";
            });
            document.getElementById("goPsnlListBtn").style.display="inline-block";//기초정보
            document.getElementById("goTrsListBtn").style.display="inline-block";//발령정보
            document.getElementById("goPttListBtn").style.display="inline-block";//최저시급정보
        }else{
            document.querySelectorAll(".modalFooter button").forEach(ftBtn => {
                ftBtn.style.display="inline-block";
            });
            document.getElementById("goPttListBtn").style.display="none";
        }
        document.getElementById("mdBdOrgNm").innerHTML=res[0].ORG_NM;
        document.getElementById("mdBdPsnlNm").innerHTML=res[0].PSNL_NM;
        document.getElementById("mdBdBaptNm").innerHTML=res[0].BAPT_NM;
        document.getElementById("mdBdPsnlNum").innerHTML=res[0].PSNL_NUM;
        document.getElementById("mdBdPhoneNum").innerHTML=res[0].PHONE_NUM;
        document.getElementById("mdBdTrsType").innerHTML=res[0].TRS_TYPE;
        document.getElementById("mdBdPosition").innerHTML=res[0].POSITION;
        document.getElementById("mdBdTrsDt").innerHTML=res[0].TRS_DT;
        document.getElementById("mdBdWorkType").innerHTML=res[0].WORK_TYPE;
        if(res[0].GRD_GRADE){document.getElementById("mdBdGrdPay").innerHTML=res[0].GRD_GRADE+"급 "+res[0].GRD_PAY+"호";}
        document.getElementById("mdBdAdvDt").innerHTML=res[0].ADVANCE_DT;
        document.getElementById("mdBdAdvRng").innerHTML=res[0].ADVANCE_RNG;
        //각 상세보기 페이지 이동 버튼
        document.getElementById("goPsnlListBtn").addEventListener("click",()=>{location.href="/psnlList?PSNL_NM="+res[0].PSNL_NM+"&BAPT_NM="+res[0].BAPT_NM+"&ORG_NM="+res[0].ORG_NM+"&ORG_CD="+res[0].ORG_CD});
        document.getElementById("goTrsListBtn").addEventListener("click",()=>{location.href="/trsList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM+"&ORG_CD="+res[0].ORG_CD});
        document.getElementById("goGrdListBtn").addEventListener("click",()=>{location.href="/grdList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM+"&ORG_CD="+res[0].ORG_CD});
        document.getElementById("goFmlListBtn").addEventListener("click",()=>{location.href="/fmlList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM+"&ORG_CD="+res[0].ORG_CD});
        document.getElementById("goAdjListBtn").addEventListener("click",()=>{location.href="/adjList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM+"&ORG_CD="+res[0].ORG_CD});
        document.getElementById("goInsListBtn").addEventListener("click",()=>{location.href="/insList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM+"&ORG_CD="+res[0].ORG_CD});
        document.getElementById("goOpiListBtn").addEventListener("click",()=>{location.href="/opiList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM+"&ORG_CD="+res[0].ORG_CD});
        document.getElementById("goMPayListBtn").addEventListener("click",()=>{location.href="/mpayList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM+"&ORG_CD="+res[0].ORG_CD+"&MPAY_YEAR="+res[0].ADVANCE_DT.substr(0,4)});
        document.getElementById("goPttListBtn").addEventListener("click",()=>{location.href="/pttList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM+"&ORG_CD="+res[0].ORG_CD});

        xhr4.open("GET", "/sys/fmlList.php?key="+psnlKey.value+"&PSNL_CD="+idx+"&CRUD=R"); xhr4.send();
    }}}
    xhr4.onload = () => {if (xhr4.status === 200) {var res = JSON.parse(xhr4.response)['data'];
        if(res!=null){
            tmpStr=`
            <ul class="clBg5">
                <li class="th"><span>가족성명</span></li>
                <li class="th"><span>관계</span></li>
                <li class="th"><span>생년월일</span></li>
                <li class="th"><span>상세정보</span></li>
                <li class="clearB"></li>
            </ul>
            `;
            for($i=0;$i<res.length;$i++){
                tmpStr+=`
                    <ul class="clBgW">
                        <li class="td"><span>`+res[$i].FML_NM+`</span></li>
                        <li class="td"><span>`+res[$i].FML_RELATION+`</span></li>
                        <li class="td"><span>`+res[$i].FML_BIRTH+`</span></li>
                        <li class="td"><span>`+res[$i].FML_DTL+`</span></li>
                        <li class="clearB"></li>
                    </ul>
                `;
            }
            document.getElementById("fmlTbl").innerHTML=tmpStr;
        }
        xhr5.open("GET", "/sys/adjList.php?key="+psnlKey.value+"&PSNL_CD="+idx+"&CRUD=R"); xhr5.send();
    }}
    xhr5.onload = () => {if (xhr5.status === 200) {var res = JSON.parse(xhr5.response)['data'];
        if(res!=null){
            if(res!=null){
                tmpStr=`
                <ul class="clBg5">
                    <li class="th"><span>수당타입</span></li>
                    <li class="th"><span>명칭</span></li>
                    <li class="th"><span>등급</span></li>
                    <li class="th"><span>수당금액</span></li>
                    <li class="clearB"></li>
                </ul>
                `;
                for($i=0;$i<res.length;$i++){
                    tmpStr+=`
                        <ul class="clBgW">
                            <li class="td"><span>`+res[$i].ADJ_TYPE+`</span></li>
                            <li class="td"><span>`+res[$i].ADJ_NM+`</span></li>
                            <li class="td"><span>`+res[$i].ADJ_LEVEL+`</span></li>
                            <li class="td"><span>`+res[$i].ADJ_PAY+`</span></li>
                            <li class="clearB"></li>
                        </ul>
                    `;
                }
                document.getElementById("adjTbl").innerHTML=tmpStr;
            }
        }
        xhr6.open("GET", "/sys/opiList.php?key="+psnlKey.value+"&PSNL_CD="+idx+"&CRUD=R"); xhr6.send();        
    }}
    xhr6.onload = () => {if (xhr6.status === 200) {var res = JSON.parse(xhr6.response)['data'];
        if(res!=null){
            if(res!=null){
                tmpStr=`
                <ul class="clBg5">
                    <li class="th"><span>타입</span></li></li>
                    <li class="th"><span>날짜</span></li></li>
                    <li class="th"><span>평가자</span></li></li>
                    <li class="th"><span>내용</span></li></li>
                    <li class="clearB"></li>
                </ul>
                `;
                for($i=0;$i<res.length;$i++){
                    tmpStr+=`
                        <ul class="clBgW">
                            <li class="td"><span>`;
                    if(res[$i].OPI_TYPE==1){
                        tmpStr+="긍정";
                    }else if(res[$i].OPI_TYPE==2){
                        tmpStr+="부정";
                    }else if(res[$i].OPI_TYPE==3){
                        tmpStr+="포상";
                    }else if(res[$i].OPI_TYPE==4){
                        tmpStr+="징계";
                    }
                    tmpStr+=`</span></li>
                            <li class="td"><span>`+res[$i].OPI_DT+`</span></li>
                            <li class="td"><span>`+res[$i].OPI_PERSON+`</span></li>
                            <li class="td"><span>`+res[$i].OPI_DTL+`</span></li>
                            <li class="clearB"></li>
                        </ul>
                    `;
                }
                document.getElementById("opiTbl").innerHTML=tmpStr;
            }
        }
    }}
}

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

//빠른 세팅 버튼 구성 > dateFormat.js 파일 참조
//const today = new Date; //오늘
document.querySelectorAll(".quikSetBtn").forEach((q,key)=>{
    q.addEventListener("click",() => {
        document.getElementById("PSNL_BIRTH_From").value="";
        document.getElementById("PSNL_BIRTH_To").value="";
        document.getElementById("TRS_DT_From").value="";
        document.getElementById("TRS_DT_To").value="";
        if(q.id=="setRetire"){
            document.getElementById("PSNL_BIRTH_From").value=(new Date).getFullYear()-60+"-01-01";
            document.getElementById("PSNL_BIRTH_To").value=(new Date).getFullYear()-60+"-12-31";
        }else if(q.id=="set10Yr"){
            document.getElementById("TRS_DT_From").value=dateFormat(dateCalc(dateCalc(new Date,"m",0),"y",-10));
            document.getElementById("TRS_DT_To").value=dateFormat(dateCalc(dateCalc(new Date,"m",6),"y",-10));
        }else if(q.id=="set20Yr"){
            document.getElementById("TRS_DT_From").value=dateFormat(dateCalc(dateCalc(new Date,"m",0),"y",-20));
            document.getElementById("TRS_DT_To").value=dateFormat(dateCalc(dateCalc(new Date,"m",6),"y",-20));
        }else if(q.id=="set30Yr"){
            document.getElementById("TRS_DT_From").value=dateFormat(dateCalc(dateCalc(new Date,"m",0),"y",-30));
            document.getElementById("TRS_DT_To").value=dateFormat(dateCalc(dateCalc(new Date,"m",6),"y",-30));
        }
        document.querySelectorAll(".filter").forEach((f,key)=>{
            mytbl.hrDt.xhr.where[f.id]=f.value;
        });
        mytbl.hrDt.xhr.page=0; //필터가 바뀌면 페이지 수도 바뀌므로 첫장으로 돌려보낸다.
        mytbl.show("myTbl");
    });
});

//표시 항목 변경 팝업 구성
document.getElementById("showCol").addEventListener("click",()=>{
    showColList.style.display="block";
    document.querySelector(".showColBg").style.visibility="visible";
});
document.querySelector(".showColBg").addEventListener("click",()=>{
    showColList.style.display="none";
    document.querySelector(".showColBg").style.visibility="hidden";
});
showColList = document.getElementById("showColList");
for($i=0;$i<Object.keys(mytbl.hrDt.columns).length;$i++){
    let tmpChk;
    if(mytbl.hrDt.columns[$i].className=="hidden"){}else{
        tmpChk="checked";
    }
    showColList.innerHTML +=`
    <div>
        <input type="checkbox" class="showColToggle" data-toggle="`+mytbl.hrDt.columns[$i].data+`" id="`+mytbl.hrDt.columns[$i].data+`Toggle" `+tmpChk+`/>
        <label for="`+mytbl.hrDt.columns[$i].data+`Toggle">`+mytbl.hrDt.columns[$i].title+`</label>
    </div>
    `;
}
document.querySelectorAll(".showColToggle").forEach((st,key)=>{
    st.addEventListener("click",(tmp)=>{
        let targetName = tmp.currentTarget.dataset.toggle;
        for($i=0;$i<Object.keys(mytbl.hrDt.columns).length;$i++){
            if(mytbl.hrDt.columns[$i].data==targetName){
                if(tmp.currentTarget.checked==false){
                    mytbl.hrDt.columns[$i].className="hidden";
                }else{
                    mytbl.hrDt.columns[$i].className="";
                }
            }
        }
        mytbl.show("myTbl");

        checkCnt = document.querySelectorAll('.showColToggle:checked').length;
        if(checkCnt>10){
            myTbl.style.width=(checkCnt*100)+"px";
        }else{
            myTbl.style.width="100%";
        }
    });
});

//뒤로가기로 돌아왔을때 이전 검색 정보 필터
window.onload = function() {
    //파라미터 중 셀렉트 파타미터값이 존재한다면 기초 세팅한다.
    const url = window.location.href; // 현재 URL을 가져온다.
    const params = new URLSearchParams(new URL(url).search); // URLSearchParams 객체 생성
    document.getElementById("WORK_TYPE").value = params.get("WORK_TYPE");
    document.getElementById("TRS_TYPE").value = params.get("TRS_TYPE");
    //debugger;
    //파라미터 기초세팅 종료
    setTimeout(function() { //뒤로가기에 값이 모두 바인딩 될때까지 딜레이가 존재하여 timeout을 추가함.
        document.querySelectorAll(".filter").forEach((f,key)=>{
            mytbl.hrDt.xhr.where[f.id]=f.value;
        });
        mytbl.show('myTbl');
        mytbl.xportBind();
    }, 50);
}