document.addEventListener("DOMContentLoaded", ()=>{
    // 모든 모달 배경(.modalBg) 클릭 시 모든 모달을 닫음
    document.querySelectorAll(".modalBg").forEach(bg => {
        bg.addEventListener("click", () => {
            modalClose();
        });
    });
    
    // 모달 헤더 닫기 버튼 (기존 로직 유지하되 전체 대상)
    document.querySelectorAll(".modalHeader button").forEach(btn => {
        btn.addEventListener("click", () => {
            modalClose();
        });
    });
});

function modalClose(){
    document.querySelectorAll(".modalForm").forEach(m => {
        m.style.visibility = "hidden";
        m.style.opacity = "0";
    });
}