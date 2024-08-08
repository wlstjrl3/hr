document.addEventListener("DOMContentLoaded", ()=>{
    document.getElementById('admin-id').focus();
    var cookieid = getCookie("saveid");
    console.log(cookieid);
    if(cookieid !=""){
        document.querySelector('#saveId').checked=true;
        a = document.querySelector('#admin-id').value=cookieid;
    }    
});
function frm_check(){
    saveid();
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
    if (document.querySelector('#saveId').checked==true){
        expdate.setTime(expdate.getTime() + 1000 * 3600 * 24 * 30);
        setCookie("saveid", document.querySelector('#admin-id').value, expdate);
    }else{
       expdate.setTime(expdate.getTime() - 1000 * 3600 * 24 * 30);
       setCookie("saveid", document.querySelector('#admin-id').value, expdate);  
    }
}