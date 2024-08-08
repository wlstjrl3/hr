document.addEventListener("DOMContentLoaded", ()=>{
    document.querySelector(".modalHeader").addEventListener("click",()=>{ //button에 이벤트 걸어야함
        modalClose();
    });
    document.querySelector(".modalBg").addEventListener("click",()=>{
        modalClose();
    });    
});

function modalClose(){
    document.querySelector(".modalForm").style.visibility="hidden";
    document.querySelector(".modalForm").style.opacity="0";
}