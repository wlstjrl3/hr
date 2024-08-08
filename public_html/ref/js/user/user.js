var selected_tr = ''; //테이블의 선택라인번호

$(document).ready(function(){
    // {{{ 버튼 클릭시[엑셀/프린트 등] 현재 페이지뿐 아니라 전체 페이지 데이터로 변환하여 다운로드
    $.fn.DataTable.Api.register('buttons.exportData()',function(options){
        var arr=[];
        $.ajax({
            url:'../dbconn/user/getList.php',
            type:'POST',
            data: {
                teamNm : $("#filter-teamNm").val(),
                leader : $("#filter-leader").val(),
                phoneNum : $("#filter-phoneNum").val(),
                userId : $("#filter-userId").val(),
                
                regDtFrom : $("#filter-regDtFrom").val(),
                regDtTo : $("#filter-regDtTo").val(),
            },
            dataType: "json",
            success:function(res){
                console.log(res);
                for(var key in res.data){
                    sub = [res.data[key]['num'],
                    res.data[key]['teamNm'],
                    res.data[key]['leader'],
                    res.data[key]['phoneNum'],
                    res.data[key]['userId'],
                    res.data[key]['userPass'],
                    res.data[key]['etc'],
                    res.data[key]['regDt']];
                    arr.push(sub);
                }
            },
            async:false
        });
        return {body:arr,header:$("#userListTable thead tr th").map(function(){return this.innerHTML;}).get()};
    });
    // }}}

    // {{{ 메인표시 기간 datepicker 달력 한글등 커스텀 지정
    $(".dualDateBox").datepicker({
        dateFormat: 'yy-mm-dd',
        prevText: '이전 달',
        nextText: '다음 달',
        monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        dayNames: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
        showMonthAfterYear: true,
        yearSuffix: '년'    });
    // }}}

    // {{{ 데이터테이블 코드
    var table = $('#userListTable').DataTable({
        //"dom": '<lf<t>ip>',
        dom: '<lf<Bt>ip>',        
        /*buttons: [
            { extend: 'excelHtml5', footer: true, className:'crud_button', text:'엑셀 다운로드',title:'' },
        ],*/     
        buttons: [            
            { text:'신규', className:'crud_button insert_button' },
            { extend: 'excelHtml5', footer: true, className:'crud_button xlDown', text:'엑셀 다운로드',title:'' },
            { text:'전체 데이터 삭제', className:'crud_button delAll_button'},
        ],            
        language: { //언어변경
            "emptyTable": "데이터가 없어요.",
            "lengthMenu": "페이지당 _MENU_ 개씩 보기",
            "info": "현재 _START_ - _END_ / _TOTAL_건",
            "infoEmpty": "데이터 없음",
            "infoFiltered": "( _MAX_건의 데이터에서 필터링됨 )",
            "search": "에서 검색: ",
            "zeroRecords": "일치하는 데이터가 없어요.",
            "loadingRecords": "로딩중...",
            "processing":     "잠시만 기다려 주세요...",
            "paginate": {
                "next": "다음",
                "previous": "이전"
            }
        },       
        "responsive": true, //반응형
        "processing": true, //로딩 애니메이션 추가
        "serverSide": true, //start 와 length를 자체적으로 ajax의 post 데이터값으로 넘기게됨! records_total 값과 filtered_total값을 정확히 반환받아야함!
        "searching": false, //자체 DB검색을 별도로 구성할것임.
        "lengthMenu": [ 10, 25, 50, ], //한 페이지당 몇개씩 볼 수 있도록 할지 선택할 수 있도록 옵션 지정
        "ajax": {
             url: "../dbconn/user/getList.php"
            ,error:function(jqXHR, ajaxOptions, thrownError){
                console.log(thrownError + "\r\n" + jqXHR.statusText + "\r\n" + jqXHR.responseText + "\r\n" + ajaxOptions.responseText);
            }
            /*,success: function(res){
                console.log(res);
            } */           
            ,type: "POST"
            ,data: function(data){
                data.teamNm = $("#filter-teamNm").val();
                data.leader = $("#filter-leader").val();
                data.phoneNum = $("#filter-phoneNum").val();
                data.userId = $("#filter-userId").val();

                data.regDtFrom = $("#filter-regDtFrom").val();
                data.regDtTo = $("#filter-regDtTo").val();
            }
        },
        "order": [
            [7, "desc"] 
        ],
        "columns": [
             {title: "No.", data: "num", orderable: false,
                "render": function ( data, type, row, meta ) {
                    return "<div class='idx' data-idx='"+row.idx+"'>"+data+"</div>";
                }
             }
             ,{title: "팀명", data: "teamNm", className: "colOrgNm"}
            ,{title: "대표성명", data: "leader", className: "colOrgId"}
            ,{title: "전화번호", data: "phoneNum", className: "colUserNm"}   
            ,{title: "아이디", data: "userId", className: "colUserNo"}
			,{title: "비밀번호", data: "userPass", className: "colMemo"}
            ,{title: "비고", data: "etc", className: "colFromDt"}
            ,{title: "등록일", data: "regDt", className: "colRegDt"}
        ],

    });
    $('#userListTable_filter').prepend('<span>전체 열 </span>');
    //$('.dataTables_length').append('<button class="crud_button insert_button">신규</button><button class="crud_button xlDown">엑셀 다운로드</button>');
    $('.xlDown').after('<div class="filebox"><label class="crud_button" for="file">엑셀 업로드</label><input class="upload-name" value="첨부파일" placeholder="첨부파일"><input type="file" id="file"></div>');
    $('#userListTable_paginate').after('<div style="clear:both;float:right;">페이지 번호로 이동 <input class="pagePickInput"> <button class="pagePickBtn">이동</button></div>');
    // }}}

    $('.delAll_button').off().on( 'click', function () {
        if (!confirm("주의! 모든 데이터가 삭제됩니다. 정말 삭제하시겠습니까?")) {
            //삭제 취소
        } else {                        
            //alert("삭제 기능 개발중");
            $.ajax({ //모달창에서 삭제
                url: "../dbconn/user/userCRUD.php"
                ,data: {
                    control:"DALL"
                }
                ,dataType: "json"
                ,type: "post"
                ,success: function(res){
                    $(".modalForm").css({"visibility":"hidden","opacity":"0"});
                    var info = table.page.info(); //페이지번호는 0부터 시작함에 유의할것
                    table.ajax.reload(); //테이블을 새로 고침한다
                    table.page(info.page).draw(false); //이전 페이지로 페이지를 이동한다
                }
                ,error: function(res){
                    alert('에러 발생');
                    console.log(res);
                }                              
            });            
        }
    });

    // 신규 클릭
    $('.crud_button').eq(0).off().on( 'click', function () {
        $(".modalForm").css({"visibility":"visible","opacity":"1"});       

        $(".modalBody").find("input").eq(0).val("");
        $(".modalBody").find("input").eq(1).val("");
        $(".modalBody").find("input").eq(2).val("");
        $(".modalBody").find("input").eq(3).val("");

        $("#modalEdtBtn").off().on("click",function(){
            $.ajax({ //모달 설정
                url: "../dbconn/user/userCRUD.php"
                ,data: {
                    control:"U",
                    teamNm:$(".modalBody").find("input").eq(0).val(),
                    leader:$(".modalBody").find("input").eq(1).val(),
                    phoneNum:$(".modalBody").find("input").eq(2).val(),
                    userId:$(".modalBody").find("input").eq(3).val(),
                    userPass:$(".modalBody").find("input").eq(4).val(),
                    etc:$(".modalBody").find("input").eq(5).val(),
                    idx:0
                }
                ,dataType: "json"
                ,type: "post"
                ,success: function(res){
                    $(".modalForm").css({"visibility":"hidden","opacity":"0"});
                    var info = table.page.info(); //페이지번호는 0부터 시작함에 유의할것
                    table.ajax.reload(); //테이블을 새로 고침한다
                    table.page(info.page).draw(false); //이전 페이지로 페이지를 이동한다
                }
                ,error: function(res){
                    alert('에러 발생');
                    console.log(res);
                }                              
            });
        });     
    });
    // 컬럼 선택
    $('#userListTable tbody').on( 'click', 'tr td:not(:first-child)', function () {
        if ( $(this).hasClass('selected') ) {
            selected_tr = "";
            $(this).removeClass('selected');
        }
        else {
            $('#userListTable tbody tr.selected').removeClass('selected');
            $(this).parent().addClass('selected');
            console.log($(this).parent().find("div.idx")[0]);
            selected_tr = $(this).parent().find("div.idx")[0].dataset.idx; //선택된 행의 idx

            $.ajax({ //모달 설정
                url: "../dbconn/user/userCRUD.php"
                ,dataType: "json"
                ,data: {
                    control:"R",
                    idx: selected_tr
                }
                ,type: "post"
                ,success: function(res){             
                    $(".modalForm").css({"visibility":"visible","opacity":"1"});                    
                    console.log(res);

                    $(".modalBody").find("input").eq(0).val(res.teamNm);
                    $(".modalBody").find("input").eq(1).val(res.leader);
                    $(".modalBody").find("input").eq(2).val(res.phoneNum);
                    $(".modalBody").find("input").eq(3).val(res.userId);
                    $(".modalBody").find("input").eq(4).val(res.userPass);
                    $(".modalBody").find("input").eq(5).val(res.etc);

                    $idx = res.idx;

                    $("#modalEdtBtn").off().on("click",function(){
                        $.ajax({ //모달창에서 수정
                            url: "../dbconn/user/userCRUD.php"
                            ,data: {
                                control:"U",
                                teamNm:$(".modalBody").find("input").eq(0).val(),
                                leader:$(".modalBody").find("input").eq(1).val(),
                                phoneNum:$(".modalBody").find("input").eq(2).val(),
                                userId:$(".modalBody").find("input").eq(3).val(),
                                userPass:$(".modalBody").find("input").eq(4).val(),
                                etc:$(".modalBody").find("input").eq(5).val(),
                                idx:$idx
                            }
                            ,dataType: "json"
                            ,type: "post"
                            ,success: function(res){
                                $(".modalForm").css({"visibility":"hidden","opacity":"0"});
                                var info = table.page.info(); //페이지번호는 0부터 시작함에 유의할것
                                table.ajax.reload(); //테이블을 새로 고침한다
                                table.page(info.page).draw(false); //이전 페이지로 페이지를 이동한다
                            }
                            ,error: function(res){
                                alert('에러 발생');
                                console.log(res);
                            }                              
                        });
                    });     
                    $("#modalDelBtn").off().on("click",function(){
                        if (!confirm("이 이용자를 정말 삭제하시겠습니까?")) {
                            //삭제 취소
                        } else {                        
                            $.ajax({ //모달창에서 삭제
                                url: "../dbconn/user/userCRUD.php"
                                ,data: {
                                    control:"D",
                                    idx:$idx
                                }
                                ,dataType: "json"
                                ,type: "post"
                                ,success: function(res){
                                    $(".modalForm").css({"visibility":"hidden","opacity":"0"});
                                    var info = table.page.info(); //페이지번호는 0부터 시작함에 유의할것
                                    table.ajax.reload(); //테이블을 새로 고침한다
                                    table.page(info.page).draw(false); //이전 페이지로 페이지를 이동한다
                                }
                                ,error: function(res){
                                    alert('에러 발생');
                                    console.log(res);
                                }                              
                            });
                        }
                    });        
                }
                ,error: function(res){
                    alert('에러 발생');
                    console.log(res);
                }                
            });
        }
    });
    $(".pagePickInput").on("change",function(){
        pageMove();
    });
    $(".pagePickBtn").on("click",function(){
        pageMove();
    });
    function pageMove(){
        pgNum = $(".pagePickInput").val();
        pgNum--; //페이지 번호는 내부적으로 0부터 시작하므로 1을 뺀 숫자로 처리한다.
        table.page(pgNum).draw(false);
    }

    $(".search, .daterange").on("keydown", function(key){
        if(key.keyCode == 13){
            selected_tr = "";
            table.ajax.reload();
        }
    });

    $(".filter").on("change", function(key){
        selected_tr = "";
        table.ajax.reload();
    }); 
    
    $("#file").on('change',function(){ //엑셀등록을 위한 파일경로 더미 레이아웃에 반영
        var fileName = $("#file").val();
        $(".upload-name").val(fileName);

        // [input 태그에 파일이 선텍 된 경우 로직 수행]
        if(fileName.slice(-4)=='xlsx'||fileName.slice(-4)=='.xls'){
            let input = event.target;
            let reader = new FileReader();
            reader.onload = function () {
                let data = reader.result;
                let workBook = XLSX.read(data, { type: 'binary' });
                workBook.SheetNames.forEach(function (sheetName) {
                    let rows = XLSX.utils.sheet_to_json(workBook.Sheets[sheetName]);
                    
                    if(rows[0]['팀명']){
                        if (!confirm(rows[0]['전화번호']+"["+rows[0]['대표성명']+"] "+rows[0]['아이디']+" 외 "+(rows.length-1)+"건 의 정보를 일괄등록 하시겠습니까?")) {
                            // 취소(아니오) 버튼 클릭 시 이벤트
                            return false;
                        }                     
                    }else{
                        alert("파일 구조가 잘못되었습니다.");
                        return false;
                    }
                    var xhr = new XMLHttpRequest();//XMLHttpRequest 객체 생성
                    xhr.open('POST', '/pass/dbconn/user/batchInsert.php', true);//요청을 보낼 방식, 주소, 비동기여부 설정                
                    xhr.setRequestHeader('Content-type', 'application/json;charset=UTF-8');//HTTP 요청 헤더 설정
                    xhr.send(JSON.stringify(rows));  //JSON,stringify를 이용하여 json으로 변환해야만 php상에서 엑셀 텍스트가 json으로 인식됨
                    xhr.onload = () => {//통신후 작업
                        if (xhr.status == 200) {//통신 성공
                            //console.log(xhr.response); 
                            table.ajax.reload();
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
});
function oninputPhone(target) {
    target.value = target.value
    .replace(/[^0-9]/g, '')
    .replace(/(^02.{0}|^01.{1}|[0-9]{3,4})([0-9]{3,4})([0-9]{4})/g, "$1-$2-$3");
}