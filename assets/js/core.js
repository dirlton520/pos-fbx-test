$(function(){
  $(window).scroll(function() {    
    var scroll = $(window).scrollTop();

    if (scroll >= 70) {
      $('.email_buttons').addClass('fixed-buttons');
    } else {
      $('.email_buttons').removeClass('fixed-buttons');
    }
  });
});

$(document).ready(function () {

/////////////////////////////////////////////////////////////////////////////imported js piluku

      var nice = $(".left-bar").niceScroll(); 
			
			var resizeEndEventCallback = function () {
				$(".left-bar").getNiceScroll().resize();
			}

			var classname = document.getElementsByClassName("left-bar");

			for (var i = 0; i < classname.length; i++) {
					classname[i].addEventListener("transitionend", resizeEndEventCallback);
			    classname[i].addEventListener("webkitTransitionEnd", resizeEndEventCallback);
			}
			
			
      $('.menu-bar').click(function(e){ 
       e.preventDefault();                 
        $(".wrapper").toggleClass('mini-bar');        

        $(".left-bar").getNiceScroll().remove();
        setTimeout(function() {
          $(".left-bar").niceScroll();
        }, 200);
      }); 

    /*
      Uncomment if we need to expand the sidebar when hover
        $(".sales-bar .left-bar").hover(
           function() {
            $(".wrapper").removeClass('mini-bar');        
           },
           function() {
            $(".wrapper").addClass('mini-bar');        
           }
        ); 
    */

      $('.menu-bar-mobile').on('click', function (e) {        
        // $(this).addClass('menu_appear');
				// $(this).animate({width:'toggle'},350);
				
        $(".left-bar").getNiceScroll().remove();
        
        $( ".left-bar" ).toggleClass("menu_appear" );
        $( ".overlay" ).toggleClass("show");
        setTimeout(function() {
          $(".left-bar").niceScroll();
        }, 200);
      });

      $(".overlay").on('click',function(){

        $( ".left-bar" ).toggleClass("menu_appear" );
        $(this).removeClass("show");
      });

      $('.right-bar-toggle').on('click', function(e){
        e.preventDefault();
        $('.wrapper').toggleClass('right-bar-enabled');
      });

          $('ul.menu-parent').accordion();

      
      new WOW().init(); 

        $("#employee_current_location_id").select2({
                placeholder: "Select",
                 containerCssClass : "error" 
            });


$('.timer').each(count);
  
      /////////////////////////////////////////////////////////////////////////////imported js piluku



  // PANELS
    // panel close
        $('.panel-close').on('click', function (e) {
          e.preventDefault();
          $(this).parent().parent().parent().parent().addClass(' animated fadeOutDown');
        });


        $('.panel-minimize').on('click', function (e) 
        {
          e.preventDefault();
          var $target = $(this).parent().parent().parent().next('.panel-body');
          if ($target.is(':visible')) {
            $('i', $(this)).removeClass('ti-angle-up').addClass('ti-angle-down');
          } else {
            $('i', $(this)).removeClass('ti-angle-down').addClass('ti-angle-up');
          }
          $target.slideToggle();
        });
        
        
        $('.panel-refresh').on('click', function (e) 
        {
          e.preventDefault();
          // alert('vj');
          var $target = $(this).closest('.panel-heading').next('.panel-body');
          $target.mask('<i class="fa fa-refresh fa-spin"></i> Loading...');

          setTimeout(function () {
            $target.unmask();
          },
          1000);
        });


});

function count(options) 
    {
        var $this = $(this);
        options = $.extend({}, options || {}, $this.data('countToOptions') || {});
        $this.countTo(options);
    }
