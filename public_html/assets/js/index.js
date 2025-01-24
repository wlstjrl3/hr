console.log("인덱스 페이지에 접속하셨습니다.");
//윈도우 로드가 끝나면
window.onload=function(){
    xhrLoad();
};

//검색 필터링을 위한 코드
document.querySelectorAll(".filter").forEach((f,key)=>{
    f.addEventListener("change",() => {
        xhrLoad();
    });
});

//xhr을 이용한 실시간 정보로드
function xhrLoad(){
    let xhr1 = new XMLHttpRequest();
    let xhr2 = new XMLHttpRequest();
    let xhr3 = new XMLHttpRequest();
    let xhr4 = new XMLHttpRequest();
    xhr1.open("GET", "/sys/psnlTotal.php?key="+psnlKey.value+"&TRS_TYPE=1&ORDER=PSNL_NUM ASC&PSNL_BIRTH_From="+((new Date).getFullYear()-60)+"-01-01&PSNL_BIRTH_To="+((new Date).getFullYear()-60)+"-12-31"); xhr1.send();
    xhr1.onload = () => {
        if (xhr1.status === 200) { //XHR 응답이 존재한다면
            var res = JSON.parse(xhr1.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            let htmlTxt = ` <ul>
                                <li>생년월일</li>
                                <li>성명</li>
                                <li>소속</li>
                                <li>직책</li>
                                <li>발령일</li>
                                <li>발령기간</li>
                                <li>근무형태</li>
                                <li>급</li>
                                <li>호</li>
                            </ul>`;
            if(res!=null){
                res.forEach((tr,key)=>{
                    htmlTxt += '<ul>'
                    htmlTxt += '    <li>'+tr.PSNL_NUM.slice(0, 6);+'</li>'
                    htmlTxt += '    <li>'+tr.PSNL_NM+'('+tr.BAPT_NM+')</li>'
                    htmlTxt += '    <li>'+tr.ORG_NM+'</li>'
                    htmlTxt += '    <li>'+tr.POSITION+'</li>'
                    htmlTxt += '    <li>'+tr.TRS_DT+'</li>'
                    htmlTxt += '    <li>'+tr.TRS_ELAPSE+'</li>'
                    htmlTxt += '    <li>'+tr.WORK_TYPE+'</li>'
                    htmlTxt += '    <li>'+tr.GRD_GRADE+'</li>'
                    htmlTxt += '    <li>'+tr.GRD_PAY+'</li>'
                    htmlTxt += '</ul>'
                });
            }
            document.getElementById("rtrTbl").innerHTML=htmlTxt;
        }      
        xhr2.open("GET", "/sys/psnlTotal.php?key="+psnlKey.value+"&TRS_TYPE=1&ORDER=PSNL_NUM ASC&TRS_DT_From="+dateFormat(dateCalc(dateCalc(new Date,"m",0),"y",-10))+"&TRS_DT_To="+dateFormat(dateCalc(dateCalc(new Date,"m",6),"y",-10))); xhr2.send();
    }
    xhr2.onload = () => {
        if (xhr2.status === 200) { //XHR 응답이 존재한다면
            var res = JSON.parse(xhr2.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            let htmlTxt = ` <ul>
                                <li>발령기간</li>
                                <li>성명</li>
                                <li>소속</li>
                                <li>직책</li>
                                <li>발령일</li>
                                <li>생년월일</li>
                                <li>근무형태</li>
                                <li>급</li>
                                <li>호</li>
                            </ul>`;
            if(res!=null){
                res.forEach((tr,key)=>{
                    htmlTxt += '<ul>'
                    htmlTxt += '    <li>'+tr.TRS_ELAPSE+'</li>'
                    htmlTxt += '    <li>'+tr.PSNL_NM+'('+tr.BAPT_NM+')</li>'
                    htmlTxt += '    <li>'+tr.ORG_NM+'</li>'
                    htmlTxt += '    <li>'+tr.POSITION+'</li>'
                    htmlTxt += '    <li>'+tr.TRS_DT+'</li>'
                    htmlTxt += '    <li>'+tr.PSNL_NUM.slice(0, 6);+'</li>'
                    htmlTxt += '    <li>'+tr.WORK_TYPE+'</li>'
                    htmlTxt += '    <li>'+tr.GRD_GRADE+'</li>'
                    htmlTxt += '    <li>'+tr.GRD_PAY+'</li>'
                    htmlTxt += '</ul>'
                });
            }
            document.getElementById("ctnTbl").innerHTML+=htmlTxt;
        }      
        xhr3.open("GET", "/sys/psnlTotal.php?key="+psnlKey.value+"&TRS_TYPE=1&ORDER=PSNL_NUM ASC&TRS_DT_From="+dateFormat(dateCalc(dateCalc(new Date,"m",0),"y",-20))+"&TRS_DT_To="+dateFormat(dateCalc(dateCalc(new Date,"m",6),"y",-20))); xhr3.send();
    }
    xhr3.onload = () => {
        if (xhr3.status === 200) { //XHR 응답이 존재한다면
            var res = JSON.parse(xhr3.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            let htmlTxt = ``;
            if(res!=null){
                res.forEach((tr,key)=>{
                    htmlTxt += '<ul>'
                    htmlTxt += '    <li>'+tr.TRS_ELAPSE+'</li>'
                    htmlTxt += '    <li>'+tr.PSNL_NM+'('+tr.BAPT_NM+')</li>'
                    htmlTxt += '    <li>'+tr.ORG_NM+'</li>'
                    htmlTxt += '    <li>'+tr.POSITION+'</li>'
                    htmlTxt += '    <li>'+tr.TRS_DT+'</li>'
                    htmlTxt += '    <li>'+tr.PSNL_NUM.slice(0, 6);+'</li>'
                    htmlTxt += '    <li>'+tr.WORK_TYPE+'</li>'
                    htmlTxt += '    <li>'+tr.GRD_GRADE+'</li>'
                    htmlTxt += '    <li>'+tr.GRD_PAY+'</li>'
                    htmlTxt += '</ul>'
                });
            }
            document.getElementById("ctnTbl").innerHTML+=htmlTxt;
        }      
        xhr4.open("GET", "/sys/psnlTotal.php?key="+psnlKey.value+"&TRS_TYPE=1&ORDER=PSNL_NUM ASC&TRS_DT_From="+dateFormat(dateCalc(dateCalc(new Date,"m",0),"y",-30))+"&TRS_DT_To="+dateFormat(dateCalc(dateCalc(new Date,"m",6),"y",-30))); xhr4.send();
    }
    xhr4.onload = () => {
        if (xhr4.status === 200) { //XHR 응답이 존재한다면
            var res = JSON.parse(xhr4.response)['data']; //응답 받은 JSON데이터를 파싱한다.
            let htmlTxt = ``;
            if(res!=null){
                res.forEach((tr,key)=>{
                    htmlTxt += '<ul>'
                    htmlTxt += '    <li>'+tr.TRS_ELAPSE+'</li>'
                    htmlTxt += '    <li>'+tr.PSNL_NM+'('+tr.BAPT_NM+')</li>'
                    htmlTxt += '    <li>'+tr.ORG_NM+'</li>'
                    htmlTxt += '    <li>'+tr.POSITION+'</li>'
                    htmlTxt += '    <li>'+tr.TRS_DT+'</li>'
                    htmlTxt += '    <li>'+tr.PSNL_NUM.slice(0, 6);+'</li>'
                    htmlTxt += '    <li>'+tr.WORK_TYPE+'</li>'
                    htmlTxt += '    <li>'+tr.GRD_GRADE+'</li>'
                    htmlTxt += '    <li>'+tr.GRD_PAY+'</li>'
                    htmlTxt += '</ul>'
                });
            }
            document.getElementById("ctnTbl").innerHTML+=htmlTxt;
        }      
    }        
}