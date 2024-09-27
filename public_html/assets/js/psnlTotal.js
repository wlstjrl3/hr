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
        ,{title: "연락처", data: "PHONE_NUM", className: ""}
        ,{title: "주민번호", data: "PSNL_NUM", className: ""}
        ,{title: "재직구분", data: "TRS_TYPE", className: ""}
        ,{title: "입/퇴사일", data: "TRS_DT", className: ""}
        ,{title: "경과(년)", data: "TRS_ELAPSE", className: ""}
        ,{title: "승급일", data: "ADVANCE_DT", className: ""}
        ,{title: "분기", data: "ADVANCE_RNG", className: ""}
        ,{title: "급", data: "GRD_GRADE", className: ""}
        ,{title: "호", data: "GRD_PAY", className: ""}
        ,{title: "기본급", data: "NORMAL_PAY", className: ""}
        ,{title: "법정수당", data: "LEGAL_PAY", className: ""}
        ,{title: "조정수당", data: "ADJUST_PAY", className: ""}
        ,{title: "예상급여", data: "EXPECT_PAY", className: ""}
    ],
});
mytbl.show('myTbl'); //테이블의 아이디에 렌더링 한다(갱신도 가능)
mytbl.xportBind();


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
    document.getElementById("lcsTbl").innerHTML="";
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
        document.getElementById("goPsnlListBtn").addEventListener("click",()=>{location.href="/psnlList?PSNL_NM="+res[0].PSNL_NM+"&BAPT_NM="+res[0].BAPT_NM+"&ORG_NM="+res[0].ORG_NM});
        document.getElementById("goTrsListBtn").addEventListener("click",()=>{location.href="/trsList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM});
        document.getElementById("goGrdListBtn").addEventListener("click",()=>{location.href="/grdList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM});
        document.getElementById("goFmlListBtn").addEventListener("click",()=>{location.href="/fmlList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM});
        document.getElementById("goLcsListBtn").addEventListener("click",()=>{location.href="/lcsList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM});
        document.getElementById("goInsListBtn").addEventListener("click",()=>{location.href="/insList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM});
        document.getElementById("goOpiListBtn").addEventListener("click",()=>{location.href="/opiList?PSNL_CD="+idx+"&PSNL_NM="+res[0].PSNL_NM+"&POSITION="+res[0].POSITION+"&ORG_NM="+res[0].ORG_NM});

        xhr4.open("GET", "/sys/fmlList.php?key="+psnlKey.value+"&PSNL_CD="+idx+"&CRUD=R"); xhr4.send();
    }}}
    xhr4.onload = () => {if (xhr4.status === 200) {var res = JSON.parse(xhr4.response)['data'];
        if(res!=null){
            tmpStr=`
            <tr>
                <th><span>가족성명</span></th>
                <th><span>관계</span></th>
                <th><span>생년월일</span></th>
                <th><span>상세정보</span></th>
            </tr>
            `;
            for($i=0;$i<res.length;$i++){
                tmpStr+=`
                    <tr>
                        <td><span>`+res[$i].FML_NM+`</span></td>
                        <td><span>`+res[$i].FML_RELATION+`</span></td>
                        <td><span>`+res[$i].FML_BIRTH+`</span></td>
                        <td><span>`+res[$i].FML_DTL+`</span></td>
                    </tr>
                `;
            }
            document.getElementById("fmlTbl").innerHTML=tmpStr;
        }
        xhr5.open("GET", "/sys/lcsList.php?key="+psnlKey.value+"&PSNL_CD="+idx+"&CRUD=R"); xhr5.send();
    }}
    xhr5.onload = () => {if (xhr5.status === 200) {var res = JSON.parse(xhr5.response)['data'];
        if(res!=null){
            if(res!=null){
                tmpStr=`
                <tr>
                    <th><span>자격명칭</span></th>
                    <th><span>자격등급</span></th>
                    <th><span>자격번호</span></th>
                    <th><span>취득일</span></th>
                </tr>
                `;
                for($i=0;$i<res.length;$i++){
                    tmpStr+=`
                        <tr>
                            <td><span>`+res[$i].LCS_NM+`</span></td>
                            <td><span>`+res[$i].LCS_LEVEL+`</span></td>
                            <td><span>`+res[$i].LCS_NUM+`</span></td>
                            <td><span>`+res[$i].LCS_GET_DT+`</span></td>
                        </tr>
                    `;
                }
                document.getElementById("lcsTbl").innerHTML=tmpStr;
            }
        }
        xhr6.open("GET", "/sys/opiList.php?key="+psnlKey.value+"&PSNL_CD="+idx+"&CRUD=R"); xhr6.send();        
    }}
    xhr6.onload = () => {if (xhr6.status === 200) {var res = JSON.parse(xhr6.response)['data'];
        if(res!=null){
            if(res!=null){
                tmpStr=`
                <tr>
                    <th><span>상벌평가</span></th></td>
                    <th><span>평가일</span></th></td>
                    <th><span>평가자</span></th></td>
                    <th><span>평가내용</span></th></td>
                </tr>
                `;
                for($i=0;$i<res.length;$i++){
                    tmpStr+=`
                        <tr>
                            <td><span>`;
                    if(res[$i].OPI_TYPE==1){
                        tmpStr+="긍정";
                    }else if(res[$i].OPI_TYPE==2){
                        tmpStr+="부정";
                    }else if(res[$i].OPI_TYPE==3){
                        tmpStr+="포상";
                    }else if(res[$i].OPI_TYPE==4){
                        tmpStr+="징계";
                    }
                    tmpStr+=`</span></td>
                            <td><span>`+res[$i].OPI_DT+`</span></td>
                            <td><span>`+res[$i].OPI_PERSON+`</span></td>
                            <td><span>`+res[$i].OPI_DTL+`</span></td>
                        </tr>
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

//표시 항목 변경 팝업 구성
document.getElementById("showCol").addEventListener("click",()=>{
    if(showColList.style.display=="block"){
        showColList.style.display="none";
    }else{
        showColList.style.display="block";
    }
})
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