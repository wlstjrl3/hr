
$(document).ready(function(){
    $(".modalHeader button").on("click",function(){
        modalClose();
    })
    $(".modalBg").on("click",function(){
        modalClose();
    })    
});

function modalClose(){
    $(".modalForm").css({"visibility":"hidden","opacity":"0"});

}