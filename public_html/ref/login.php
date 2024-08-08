<!DOCTYPE html>
<html lang="ko">
<head>

<title>제1대리구 인사시스템 관리자페이지</title>
<meta charset='utf8' />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
<meta http-equiv="x-ua-compatible" content="ie=edge" />

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css">
<style>
    body{
        margin:0;padding:0;
        background:#079;
    }
    .loginFrame{
        border:1px solid #CCC;
        /*background:no-repeat center/100% url('/assets/images/main01.png');*/
        max-width:600px;
        width:100%;
        text-align:center;
        position:absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .ipt-lg{
        width:265px;
        padding:5px;
        margin-bottom:5px;
    }
    .btn-lg{
        padding:5px 120px;
        background:#247;
        color:white;
        font-weight:900;
        border:0;
    }
</style>
</head>

<body>
    <div class="loginFrame">
        <div style="background: rgba(255, 255, 255, 0.6)">
            <br>
            <h3 style="color:#247;">제1대리구 인사시스템</h3>
            <h4 style="color:#247;">관리자 로그인</h4>
            <form autocomplete="off" name="form-signin" class="form-signin" method="post" target="_self" action="../dbconn/login_chk.php" onsubmit="return frm_check();">
                <div><input class="ipt-lg" placeholder="ID" id="admin-id" name="admin-id" min=3 required></div>
                <div><input class="ipt-lg" placeholder="PASSWORD" type="password" id="admin-password" name="admin-password" min=4 required></div>
                <button class="btn-lg">로그인</button><br>
                <input style="margin:10px 0 0 -190px;" type="checkbox" name="saveId" id="saveId"><label for="saveId" style="font-size:12px;text-shadow:1px 1px 2px #FEA, 0 0 1em #FEA, 0 0 0.2em #FEA;"> 아이디 저장</label>
            </form>
            <br><br>
        </div>
    </div>
</body>

<script type="text/javascript">
    $(function() {
        fnInit();
    });
    function frm_check(){
        saveid();
    }
    function fnInit(){
        var cookieid = getCookie("saveid");
        console.log(cookieid);
        if(cookieid !=""){
            $("input:checkbox[id='saveId']").prop("checked", true);
            $('#admin-id').val(cookieid);
        }
        
    }
    function setCookie(name, value, expiredays) {
        var todayDate = new Date();
        todayDate.setTime(todayDate.getTime() + 0);
        if(todayDate > expiredays){
            document.cookie = name + "=" + escape(value) + "; path=/; expires=" + expiredays + ";";
        }else if(todayDate < expiredays){
            todayDate.setDate(todayDate.getDate() + expiredays);
            document.cookie = name + "=" + escape(value) + "; path=/; expires=" + todayDate.toGMTString() + ";";
        }
        console.log(document.cookie);
    }
 
    function getCookie(Name) {
        var search = Name + "=";
        console.log("search : " + search);
        
        if (document.cookie.length > 0) { // 쿠키가 설정되어 있다면 
            offset = document.cookie.indexOf(search);
            console.log(document.cookie);
            console.log("offset : " + offset);
            if (offset != -1) { // 쿠키가 존재하면 
                offset += search.length;
                // set index of beginning of value
                end = document.cookie.indexOf(";", offset);
                console.log("end : " + end);
                // 쿠키 값의 마지막 위치 인덱스 번호 설정 
                if (end == -1)
                    end = document.cookie.length;
                console.log("end위치  : " + end);
                
                return unescape(document.cookie.substring(offset, end));
            }
        }
        return "";
    }
    function saveid() {
        var expdate = new Date();
        if ($("#saveId").is(":checked")){
            expdate.setTime(expdate.getTime() + 1000 * 3600 * 24 * 30);
            setCookie("saveid", $("#admin-id").val(), expdate);
        }else{
           expdate.setTime(expdate.getTime() - 1000 * 3600 * 24 * 30);
           setCookie("saveid", $("#admin-id").val(), expdate);  
        }
    }
 
 
    
    
</script>