$(document).ready(function(){
    // {{{ 사이드네비게이션 서브메뉴 토글 및 우측 화살표 아이콘 상하 회전
    $(".sideNavBlock li ul").eq(2).hide();
    $(".sideNavBlock > li").eq(1).find('img').eq(0).css({"transform":"rotate(180deg)"});
    $(".sideNavBlock > li").eq(2).find('img').eq(0).css({"transform":"rotate(180deg)"});
    $(".sideNavBlock li").click(function(){
      $("ul",this).slideToggle("fast");
      var matrix = $(this).find('img').eq(0).css("transform");
      if(matrix=='none'){
        $(this).find('img').eq(0).css({"transform":"rotate(180deg)"});
      }else{
        $(this).find('img').eq(0).css({"transform":"none"});
      }
    });
    // }}}
    
  });
  
  // {{{ 상단 헤더 네비게이션 조작
  var didScroll;
  var lastScrollTop = 0;
  var delta = 5;
  var navbarHeight = 64; //네비게이션 높이 체크용
  var navOpen=0; //네비 토글 체크용
  
  $(window).scroll(function(event){
    didScroll = true;
  });
  setInterval(function() { //스크롤 사이의 딜레이를 주어 연속스크롤 인식 방지
    if (didScroll) {
      hasScrolled();
      didScroll = false;
    }
  }, 250);
  
  function hasScrolled() { //스크롤이 발생하면
    var st = $(this).scrollTop();
    if(Math.abs(lastScrollTop - st) <= delta) 
    return; 
    
    if (st > 500) { //하단에 위치한 TOP 올리기 버튼
      $('#MOVE_TOP_BTN').fadeIn();
    } else {
      $('#MOVE_TOP_BTN').fadeOut();
    }
  
    if (st > lastScrollTop && st > navbarHeight){ //최상단 네비게이션
      // Scroll Down
      $('header').removeClass('nav-down').addClass('nav-up'); 
    } else { 
      // Scroll Up
      if(st + $(window).height() < $(document).height()) { 
        $('header').removeClass('nav-up').addClass('nav-down'); 
      } 
    } 
    
    lastScrollTop = st;
  }
  //}}}
  
  // {{{ 사이드네비게이션 숨김/오픈
  function toggleNav() {
      if(navOpen==1){
          $("#navOpen").attr("src","https://mocatholic.or.kr/assets/images/nav/menu.svg");
          navOpen = 0;
          $(".side-nav").css('width','0');
          $("#closeNav").css('width','0'); 
          $('html, body').css({'overflow': 'visible', 'height': 'auto'});
          $(".hd-nav-margin").css('width','0%'); 
      }else{
          $("#navOpen").attr("src","https://mocatholic.or.kr/assets/images/nav/close.svg");
          navOpen = 1;
          if($(window).width() > 1500) {
          $(".side-nav").css({'width':'200px','margin-left':'15%'});
          $(".hd-nav-margin").css('width','15%');
          }else{
          $(".side-nav").css({'width':'200px','margin-left':'0%'});
          }
          $("#closeNav").css({'width':'100%', 'height':'100%'}); 
          $('html, body').css({'overflow': 'hidden', 'height': '100%'});
      }
  }
  // }}}
  
  // {{{ TOP 상단으로 돌리기 버튼
  $(function() {
    $("#MOVE_TOP_BTN").click(function() {
        $('html, body').animate({
            scrollTop : 0
        }, 400);
        return false;
    });
  });
  // }}}
