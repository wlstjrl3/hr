class hr_tbl{
    constructor(hrDt){
        this.hrDt = hrDt;
    }
    show(tbNm){
        let xhr = new XMLHttpRequest(); 
        let resValue = this.hrDt.xhr.url; //XHR로 불러올 URL을 기본 지정
        resValue += "?key="+this.hrDt.xhr.key; //API 호출에 사용할 보안인증키 추후 MD5암호화 정보로 이용할 예정
        Object.keys(this.hrDt.xhr.where).forEach(wh=>{ //JSON의 KEY NAME 명칭을 가져온 뒤 그 갯수만큼 반복
            if(this.hrDt.xhr.where[wh]!=''){ //해당 명칭에 값이 존재한다면
                resValue += "&"+wh+"="+this.hrDt.xhr.where[wh]; //XHR에서 DB 통신할 WHERE 조건으로 KEY명칭과 값을 덧붙인다
            }
        });
        resValue += "&ORDER="+this.hrDt.columns[this.hrDt.xhr.order.column].data+" "+this.hrDt.xhr.order.direction;
        resValue += "&LIMIT="+this.hrDt.xhr.page*this.hrDt.xhr.limit+","+this.hrDt.xhr.limit; //페이지네이션 정보를 추가한다.
        //debugger;
        xhr.open("GET", resValue); xhr.send(); //XHR을 즉시 호출한다.
        xhr.onload = () => {
            if (xhr.status === 200) { //XHR 응답이 존재한다면
                //debugger;
                var tableData = JSON.parse(xhr.response)['data']; //응답 받은 JSON데이터를 파싱한다.
                let totalCnt = JSON.parse(xhr.response)['totalCnt'];
                let filterCnt = JSON.parse(xhr.response)['filterCnt'];
                //let query = JSON.parse(xhr.response)['query'];
//테이블 객체 생성 이벤트//
                let table = document.querySelector('#'+tbNm) //외부에서 호출할때 적은 테이블 ID로 오브젝트를 준비한다.
                table.classList.add('hr_tbl'); //해당 오브젝트에 hr_tbl이라는 클래스 명칭을 추가한다.
                let str=`<thead><tr><th class="openCol">반응</th>` //모바일 반응형에서 접혀있는 Coulmn(열)을 오픈할때 사용할 칸을 준비한다.
                this.hrDt.columns.forEach((cl,key)=>{//외부에서 호출한 컬럼의 갯수만큼 반복
                    str+=`<th class="`+cl.className+`"><p data-order-col="`+key+`" data-col-nm="`+this.hrDt.columns[key].data+`">`+cl.title;
                    if(mytbl.hrDt.xhr.order.column==key){
                        if(mytbl.hrDt.xhr.order.direction=="desc"){
                            str+=` ▾`;
                        }else{
                            str+=` ▴`;
                        }
                    }                    
                    str+=`</p>
                    </th>`; //테이블의 제목열에 클래스네임을 지정하고 타이틀도 적는다.
                });
                str+=`</tr></thead><tbody>`; //테이블의 제목영역을 닫고 데이터영역을 시작한다.
                if(tableData!=null){
                    tableData.forEach((row,key)=>{
                        str+=`<tr data-idx="`+row[this.hrDt.columns[0].data]+`"`; //tr라인에 idx 키값을 지정한다. 모달창에서 개별 CURD 처리하기 위함
                        if(key%2 == 0){str+=` class="even">`;}else{str+=` class="odd">`;}
                        str+= `<td class="openCol"><a class="colBtn">＋</a></td>`;
                        this.hrDt.columns.forEach((cl,key)=>{
                            str+= `<td class="`+cl.className+`" data-label="`+cl.title+`"><p class="sharp">`;
                            if(!row[cl.data]){}else{str+=row[cl.data];}
                            str+=`</p></td>`;
                        });
                        str+=`</tr>`;
                    });
                }else{
                    str+=`<tr><td colspan="`+this.hrDt.columns.length+`">데이터가 없습니다.</td></tr>`;
                }
                str+='</tbody>';
                table.innerHTML=str;
//페이지당 표시행수 변경//
                document.querySelector('.tblLimit').addEventListener('change',target=>{
                    this.hrDt.xhr.limit = Number(target.currentTarget.value);
                    mytbl.show(tbNm);                                                   //테이블의 정보갱신
                });
//페이지네이션 지정//
                //let pagination = document.createElement('div');
                let totalPg = Math.ceil(filterCnt/this.hrDt.xhr.limit);
                let pageStr = `
                    <br>
                    <div class="pagination txtCenter" style="font-size:0;">
                        <a class="pageMoveBtn pddS pointer rndCorner cl3">◀</a>
                    `;
                    if(1==1){ //현재페이지의 조걸별로 말줄임표와 첫장 마지막장 표시를 접어두기 위해
                        if(this.hrDt.xhr.page+1>3){ ///////////...과 함께 첫장
                            pageStr +=`
                            <a class="pageMoveBtn pddS pointer rndCorner clBrC">1</a>
                            <span>&nbsp; ... &nbsp;</span>
                            `;
                        }
                        if(this.hrDt.xhr.page-1>0){
                            pageStr+=`<a class="pageMoveBtn pddS pointer rndCorner clBrC">`+(this.hrDt.xhr.page-1)+`</a>`;
                        }
                        if(this.hrDt.xhr.page>0){
                            pageStr+=`<a class="pageMoveBtn pddS pointer rndCorner clBrC">`+(this.hrDt.xhr.page)+`</a>`;
                        }
                        pageStr+=`<a class="pddS pointer rndCorner clBg3 clW">`+(this.hrDt.xhr.page+1)+`</a>`;//현재 페이지값
                        if(this.hrDt.xhr.page+2<totalPg){
                            pageStr+=`<a class="pageMoveBtn pddS pointer rndCorner clBrC">`+(this.hrDt.xhr.page+2)+`</a>`;
                        }
                        if(this.hrDt.xhr.page+3<totalPg){
                            pageStr+=`<a class="pageMoveBtn pddS pointer rndCorner clBrC">`+(this.hrDt.xhr.page+3)+`</a>`;
                        }                 
                        if(totalPg>this.hrDt.xhr.page+3){ /////////...과 함께 마지막장
                        pageStr+=`
                            <span>&nbsp; ... &nbsp;</span>
                            <a class="pageMoveBtn pddS pointer rndCorner clBrC">`+totalPg+`</a>
                        `;}else{
                            //여기 ...이 없이 나올 2개의 버튼덧붙이기 위치임
                            if(this.hrDt.xhr.page+2<totalPg){
                                pageStr+=`<a class="pageMoveBtn pddS pointer rndCorner clBrC">`+totalPg-1+`</a>`;
                            }
                            if(this.hrDt.xhr.page+1<totalPg){
                                pageStr+=`<a class="pageMoveBtn pddS pointer rndCorner clBrC">`+totalPg+`</a>`;
                            }
                        }
                    }
                    pageStr+=`
                        <a class="pageMoveBtn pddS pointer rndCorner cl3">▶</a>
                    </div>
                    <div class="pddS floatL">
                        <span>총 `+totalCnt+`개 행중 `+filterCnt+`개 필터링</span>
                    </div>
                    <div class="pddS floatR">
                            <span>페이지 지정</span>
                            <input class="txtCenter pddSS rndCorner clBrC" style="width:90px;">
                        <a id="xport" class="pddS clBg3 clW rndCorner pointer">이동</a>
                    </div>
                    <div class="clearB"></div>
                `;
                tblPagination.innerHTML = pageStr;
                document.querySelectorAll(".pageMoveBtn").forEach(pgMvBtn=>{
                    pgMvBtn.addEventListener('click',()=>{
                    console.log("팝업 페이지 이동 이벤트");
                    if(pgMvBtn.text=="▶"){
                        this.hrDt.xhr.page=this.hrDt.xhr.page+1;
                        if(this.hrDt.xhr.page>totalPg-1){this.hrDt.xhr.page=totalPg-1;} //최대페이지보다 높지않도록
                    }else if(pgMvBtn.text=="◀"){
                        this.hrDt.xhr.page=this.hrDt.xhr.page-1;
                        if(this.hrDt.xhr.page<0){this.hrDt.xhr.page=0;} //1페이지보다 낮지않도록
                    }else{
                        this.hrDt.xhr.page = Number(pgMvBtn.text)-1;
                    }

                    
                    mytbl.show(tbNm);                                                   //테이블의 정보갱신
                    });
                });
//테이블의 행 클릭 이벤트//
                table.querySelectorAll("tr").forEach(tr=>{                              //테이블의 모든 행을 tr이라고 지정한다.
                    tr.addEventListener("click",target=>{                               //모든 행에 클릭 이벤트를 추가한다.
                        if(target.target.className == 'colBtn'){                        //모바일 반응형 구성을 위해 추가한 colBtn을 눌렀다면
                            if(target.target.innerHTML=="＋"){                          //아직 열려있지 않다면
                                target.target.innerHTML = "－";                         //반응형 버튼의 txt를 - 로 변경하고
                                target.target.style.background="#D66";                  //반응형 버튼의 색상도 붉은색으로 변경한다.
                                target.currentTarget.querySelectorAll("td").forEach(td=>{ //테이블 컬럼들을 td라고 지정하고
                                    td.style.float="none";                              //좌측으로 이어 붙어있던 컬럼들을 모두 아래로 내림처리한다.
                                    td.style.width="auto";                              //열의 가로너비를 100% 채운다.
                                });
                                target.currentTarget.style.height="auto";               //추가정보를 숨기기 위해 고정값으로 지정했던 행의 높이를 내용길이에 맞춘다.
                            }else{                                                      //이미 열려있다면
                                target.target.innerHTML = "＋";                         //반응형 버튼의 txt를 + 로 변경하고
                                target.target.style.background="#6BD";                  //반응형 버튼의 색상도 푸른계열로 변경한다.
                                target.currentTarget.querySelectorAll("td").forEach(td=>{ //테이블 컬럼들을 td라고 지정하고
                                    td.style.float="left";                              //왼쪽으로 띄워 붙임 처리한뒤
                                    td.style.width="45%";                               //가로 너비를 45%로 맞춘다 (제일 왼쪽 반응형 버튼이 10%를 차지하고 추가로 2개의 td가 표시된다.)
                                });
                                target.currentTarget.style.height="35px";               //추가정보를 숨기기 위해 고정값으로 행의 높이를 지정한다.
                            }
                        }else if(target.target.dataset.colNm!=undefined){               //제목열 th 내의 p를 눌렀다면 정렬순서 변경
                            mytbl.hrDt.xhr.order.column=target.target.dataset.orderCol; //
                            if(target.target.innerHTML.slice(-1)=="▾"){
                                mytbl.hrDt.xhr.order.direction="asc"  //오름차순 정렬
                            }else{
                                mytbl.hrDt.xhr.order.direction="desc"  //내림차순 정렬
                            }
                            mytbl.show(tbNm);                                           //테이블의 정보갱신
                        }else if(this.hrDt.tblType=="psnlPopup"){   //개인검색 팝업창에서의 행클릭 이벤트
                            opener.document.getElementById('PSNL_CD').value = target.currentTarget.children[1].innerText;
                            opener.document.getElementById('ORG_NM').value = target.currentTarget.children[2].innerText;
                            opener.document.getElementById('PSNL_NM').value = target.currentTarget.children[3].innerText;
                            opener.document.getElementById('POSITION').value = target.currentTarget.children[5].innerText;
                            opener.document.getElementById('psnlSerchPop').parentElement.parentElement.nextElementSibling.querySelector("input").focus();    
                            opener.myTblRefresh();            
                            window.close(); 
                        }else if(this.hrDt.tblType=="orgPopup"){   //조직검색 팝업창에서의 행클릭 이벤트
                            opener.document.getElementById('orgCd').value = target.currentTarget.children[1].innerText
                            opener.document.getElementById('orgNm').value = target.currentTarget.children[2].innerText;
                            opener.document.getElementById('orgSerchPop').focus();
                            window.close();
                        }else if(this.hrDt.tblType=="pageLink"){   //페이지 이동처리 이벤트
                            let param = "";
                            tr.querySelectorAll("td").forEach(td=>{
                                if(td.dataset.label=="본당명"){
                                    param+="ORG_NM="+td.textContent+"&";
                                }else if(td.dataset.label=="성명"){
                                    param+="PSNL_NM="+td.textContent+"&";
                                }else if(td.dataset.label=="직책"){
                                    param+="POSITION="+td.textContent+"&";
                                }else if(td.dataset.label=="개인번호"||td.dataset.label=="개인코드"){
                                    param+="PSNL_CD="+td.textContent+"&";
                                }else if(td.dataset.label=="본당코드"){
                                    param+="ORG_CD="+td.textContent+"&";
                                }
                            });
                            location.href=this.hrDt.pageLinkHref+param;
                        }else{                                                          //팝업창도 아니고 +버튼이 아닌 행을 클릭하여 모달창을 연다면
                            let modalForm = document.querySelector(".modalForm")
                            modalForm.style.visibility="visible"; //모달창이 나타나게 한다.
                            modalForm.style.opacity="1";     //투명도 애니메이션 적용을 위해 opacity가 0에서 1로 변경된다.
                            trDataXHR(tr.dataset.idx); //행을 클릭했을때 열린 모달창의 DB 데이터관리는 해당 페이지 js에서 담당한다.
                        }
                    });
                });
            } else { //XHR 응답이 존재하지 않는다면(에러)
                console.error(xhr.status, xhr.statusText);
            }
        };
    };
    xportBind(){
        document.getElementById("xport").addEventListener("click", async() => {
            let xhr = new XMLHttpRequest(); 
            let resValue = this.hrDt.xhr.url;
            resValue += "?key="+this.hrDt.xhr.key;
            Object.keys(this.hrDt.xhr.where).forEach(wh=>{
                if(this.hrDt.xhr.where[wh]!=''){
                    resValue += "&"+wh+"="+this.hrDt.xhr.where[wh];
                }
            });
            //엑셀다운로드는 페이징과 무관하게 전체 데이터를 가져와야 하므로 limit 데이터를 풀고 별도 코드를 통해 xhr로 가져와야 함!
            //resValue += "&LIMIT="+this.hrDt.xhr.page*this.hrDt.xhr.limit+","+this.hrDt.xhr.limit;
            xhr.open("GET", resValue); xhr.send();
            xhr.onload = () => {
                if (xhr.status === 200) {
                    var rows=JSON.parse(xhr.response)['data'];
                    const worksheet = XLSX.utils.json_to_sheet(rows);
                    const workbook = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(workbook, worksheet, "Dates");    
                    /* fix headers */

                    let clTitle = [];
                    this.hrDt.columns.forEach((cl) => {
                    //    clTitle.push(cl.title); //페이지의 제목열을 엑셀파일 제목열로 푸시
                    });
                    XLSX.utils.sheet_add_aoa(worksheet, [clTitle], { origin: "A1" });
                    /* create an XLSX file and try to save to Presidents.xlsx */

                    // 오늘 날짜
                    const today = new Date();const year = today.getFullYear();const month = (today.getMonth() + 1).toString().padStart(2, '0');const day = today.getDate().toString().padStart(2, '0'); 
                    const yyyymmdd = `${year}${month}${day}`;

                    XLSX.writeFile(workbook, yyyymmdd+window.location.pathname+".xlsx", { compression: true });    
                } else {
                    console.error(xhr.status, xhr.statusText);
                }
            }
        });
    }
    dataBind(){
        console.log("키값에 맞춰 데이터 바인드");
    }
}