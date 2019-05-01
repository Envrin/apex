// make console.log safe to use
window.console||(console={log:function(){}});

//------------- Options for Supr - admin tempalte -------------//
var supr_Options = {
	fixedWidth: false, //activate fixed version with true
	rtl:false, //activate rtl version with true
	showSwitcher: false //show switcher with true
}

//------------- Modernizr -------------//
//load some plugins only if is needed
Modernizr.load({
  test: Modernizr.placeholder,
  nope: 'plugins/forms/placeholder/jquery.placeholder.min.js',
  complete: function () {
	//------------- placeholder fallback  -------------//
	$('input[placeholder], textarea[placeholder]').placeholder();
  }
});
Modernizr.load({
  test: Modernizr.touch,
  yep: ['plugins/fix/ios-fix/ios-orientationchange-fix.js', 'plugins/fix/touch-punch/jquery.ui.touch-punch.min.js']
});

//window resize events
$(window).resize(function(){
	//get the window size
	var wsize =  $(window).width();
	if (wsize > 980 ) {
		$('.shortcuts.hided').removeClass('hided').attr("style","");
		$('.sidenav.hided').removeClass('hided').attr("style","");
	}

	var size ="Window size is:" + $(window).width();
	//console.log(size);
});

$(window).load(function(){
	var wheight = $(window).height();
	$('#sidebar.scrolled').css('height', wheight-63+'px');
});

// document ready function
$(document).ready(function(){ 	

	//------------- Switcher code ( Remove it in production site ) -------------//
	(function(){
		supr_switcher = {
			create : function () {
				//create switcher and inject into html
				$('body').append('<a href="#" id="switchBtn"><span class="icon24 icomoon-icon-cogs"></span></a>');
				$('body').append('<div id="switcher"><h4>Header patterns</h4><div class="header-patterns"><ul><li><a href="#" class="hpat1"><img src="images/patterns/header/1.png"></a></li><li><a href="#" class="hpat2"><img src="images/patterns/header/2.png"></a></li><li><a href="#" class="hpat3"><img src="images/patterns/header/3.png"></a></li><li><a href="#" class="hpat4"><img src="images/patterns/header/4.png"></a></li></ul></div><h4>Sidebar patterns</h4><div class="sidebar-patterns"><ul><li><a href="#" class="spat1"><img src="images/patterns/sidebar/1.png"></a></li><li><a href="#" class="spat2"><img src="images/patterns/sidebar/2.png"></a></li><li><a href="#" class="spat3"><img src="images/patterns/sidebar/3.png"></a></li><li><a href="#" class="spat4"><img src="images/patterns/sidebar/4.png"></a></li></ul></div><h4>Body patterns</h4><div class="body-patterns"><ul><li><a href="#" class="bpat1"><img src="images/patterns/body/1.png"></a></li><li><a href="#" class="bpat2"><img src="images/patterns/body/2.png"></a></li><li><a href="#" class="bpat3"><img src="images/patterns/body/3.png"></a></li><li><a href="#" class="bpat4"><img src="images/patterns/body/4.png"></a></li></ul></div></div>');
			},
			init: function() {
				supr_switcher.create();
				$('#switcher a').click(function() {
					if($(this).hasClass('hpat1')) {$('#header').css('background', 'url(images/patterns/header/bedge_grunge.png)');}
					if($(this).hasClass('hpat2')) {$('#header').css('background', 'url(images/patterns/header/grid.png)');}
					if($(this).hasClass('hpat3')) {$('#header').css('background', 'url(images/patterns/header/nasty_fabric.png)');}
					if($(this).hasClass('hpat4')) {$('#header').css('background', 'url(images/patterns/header/natural_paper.png)');}
					if($(this).hasClass('spat1')) {$('#sidebarbg').css('background', 'url(images/patterns/sidebar/az_subtle.png)');}
					if($(this).hasClass('spat2')) {$('#sidebarbg').css('background', 'url(images/patterns/sidebar/billie_holiday.png)');}
					if($(this).hasClass('spat3')) {$('#sidebarbg').css('background', 'url(images/patterns/sidebar/grey.png)');}
					if($(this).hasClass('spat4')) {$('#sidebarbg').css('background', 'url(images/patterns/sidebar/noise_lines.png)');}
					if($(this).hasClass('bpat1')) {$('#content').css('background', 'url(images/patterns/body/cream_dust.png)');}
					if($(this).hasClass('bpat2')) {$('#content').css('background', 'url(images/patterns/body/dust.png)');}
					if($(this).hasClass('bpat3')) {$('#content').css('background', 'url(images/patterns/body/grey.png)');}
					if($(this).hasClass('bpat4')) {$('#content').css('background', 'url(images/patterns/body/subtle_dots.png)');}					
				});

				$('#switchBtn').click(function() {
					if($(this).hasClass('toggle')) {
						//hide switcher
						$(this).removeClass('toggle').css('right', '-1px');
						$('#switcher').css('display', 'none');

					} else {
						//expand switcher
						$(this).animate({
						    right: '135'
						  }, 200, function() {
						    // Animation complete.
						    $('#switcher').css('display', 'block');
						    $(this).addClass('toggle');
						  });
					}
				});
			}
		}
	})();

	//show switcher
	if(supr_Options.showSwitcher) {
		supr_switcher.init();
	}

	//make template fixed width
	if(supr_Options.fixedWidth) {
		$('body').addClass('fixedWidth');
		$('#header .container-fluid').addClass('container').removeClass('container-fluid');
		$('#wrapper').addClass('container');
	}

	//rtl version
	if(supr_Options.rtl) {
		localStorage.setItem('rtl', 1);
		$('#bootstrap').attr('href', 'css/bootstrap/bootstrap.rtl.min.css');
		$('#bootstrap-responsive').attr('href', 'css/bootstrap/bootstrap-responsive.rtl.min.css');
		$('body').addClass('rtl');
		if(!$('#content-two').length){
			$('#sidebar').attr('id', 'sidebar-right');
			$('#sidebarbg').attr('id', 'sidebarbg-right');
			$('.collapseBtn').addClass('rightbar').removeClass('leftbar');
			$('#content').attr('id', 'content-one');
		}
	} else {localStorage.setItem('rtl', 0);}
	
  	//Disable certain links
    $('a[href^=#]').click(function (e) {
      e.preventDefault()
    })

    $('.search-btn').addClass('nostyle');//tell uniform to not style this element
 
	//------------- Navigation -------------//

	mainNav = $('.mainnav>ul>li');
	mainNav.find('ul').siblings().addClass('hasUl').append('<span class="hasDrop icon16 icomoon-icon-arrow-down-2"></span>');
	mainNavLink = mainNav.find('a').not('.sub a');
	mainNavLinkAll = mainNav.find('a');
	mainNavSubLink = mainNav.find('.sub a').not('.sub li .sub a');
	mainNavCurrent = mainNav.find('a.current');

	/*Auto current system in main navigation */
	var domain = document.domain;
	var folder ='/supr';//if you put site in folder not in main domain you need to specify it. example http://www.host.com/folder/site
	var absoluteUrl = 0; //put value of 1 if use absolute path links. example http://www.host.com/dashboard instead of /dashboard

	function setCurrentClass(mainNavLinkAll, url) {
		mainNavLinkAll.each(function(index) {
			//convert href to array and get last element
			var href= $(this).attr('href');

			if(href == url) {
				//set new current class
				$(this).addClass('current');

				ulElem = $(this).closest('ul');
				if(ulElem.hasClass('sub')) {
					//its a part of sub menu need to expand this menu
					aElem = ulElem.prev('a.hasUl').addClass('drop');
					ulElem.addClass('expand');
				} 
			}
		});
	}


	if(domain === '') {
		//domain not found looks like is in testing phase
		var pageUrl = window.location.pathname.split( '/' );
		console.log(pageUrl);
		var winLoc = pageUrl.pop(); // get last item
		setCurrentClass(mainNavLinkAll, winLoc);

	} else {
		if(absoluteUrl === 0) {
			//absolute url is disabled
			var afterDomain = window.location.pathname;
			if(folder !='') {
				afterDomain = afterDomain.replace(folder + '/','');
			} else {
				afterDomain = afterDomain.replace('/','');
			}
			setCurrentClass(mainNavLinkAll, afterDomain);
		} else {
			//absolute url is enabled
			var newDomain = 'http://' + domain + window.location.pathname;
			setCurrentClass(mainNavLinkAll, newDomain);
		}
	}

	//hover magic add blue color to icons when hover - remove or change the class if not you like.
	mainNavLinkAll.hover(
	  function () {
	    $(this).find('span.icon16').addClass('blue');
	  }, 
	  function () {
	    $(this).find('span.icon16').removeClass('blue');
	  }
	);

	//click magic
	mainNavLink.click(function(event) {
		$this = $(this);
		if($this.hasClass('hasUl')) {
			event.preventDefault();
			if($this.hasClass('drop')) {
				$(this).siblings('ul.sub').slideUp(250).siblings().toggleClass('drop');
			} else {
				$(this).siblings('ul.sub').slideDown(250).siblings().toggleClass('drop');
			}			
		} 
	});
	mainNavSubLink.click(function(event) {
		$this = $(this);
		if($this.hasClass('hasUl')) {
			event.preventDefault();
			if($this.hasClass('drop')) {
				$(this).siblings('ul.sub').slideUp(250).siblings().toggleClass('drop');
			} else {
				$(this).siblings('ul.sub').slideDown(250).siblings().toggleClass('drop');
			}			
		} 
	});

	//responsive buttons
	$('.resBtn>a').click(function(event) {
		$this = $(this);
		if($this.hasClass('drop')) {
			$this.removeClass('drop');
		} else {
			$this.addClass('drop');
		}
		if($('#sidebar').length) {
			$('#sidebar').toggleClass('offCanvas');
			$('#sidebarbg').toggleClass('offCanvas');
			if($('#sidebar-right').length) {
				$('#sidebar-right').toggleClass('offCanvas');
			}
		}
		if($('#sidebar-right').length) {
			$('#sidebar-right').toggleClass('offCanvas');
			$('#sidebarbg-right').toggleClass('offCanvas');
		}
		$('#content').toggleClass('offCanvas');
		if($('#content-one').length) {
			$('#content-one').toggleClass('offCanvas');
		}
	});

	$('.resBtnSearch>a').click(function(event) {
		$this = $(this);
		if($this.hasClass('drop')) {
			$('.search').slideUp(250);
		} else {
			$('.search').slideDown(250);
		}
		$this.toggleClass('drop');
	});
	
	//Hide and show sidebar btn

	$(function () {
		//var pages = ['grid.html','charts.html'];
		var pages = [];
	
		for ( var i = 0, j = pages.length; i < j; i++ ) {

		    if($.cookie("currentPage") == pages[i]) {
				var cBtn = $('.collapseBtn.leftbar');
				cBtn.children('a').attr('title','Show Left Sidebar');
				cBtn.addClass('shadow hide');
				cBtn.css({'top': '20px', 'left':'200px'});
				$('#sidebarbg').css('margin-left','-299'+'px');
				$('#sidebar').css('margin-left','-299'+'px');
				if($('#content').length) {
					$('#content').css('margin-left', '0');
				}
				if($('#content-two').length) {
					$('#content-two').css('margin-left', '0');
				}
		    }

		}
		
	});

	$( '.collapseBtn' ).bind( 'click', function(){
		$this = $(this);

		//left sidbar clicked
		if ($this.hasClass('leftbar')) {
			
			if($(this).hasClass('hide')) {
				//show sidebar
				$this.removeClass('hide');
				$this.children('a').attr('title','Hide Left Sidebar');

			} else {
				//hide sidebar
				$this.addClass('hide');
				$this.children('a').attr('title','Show Left Sidebar');		
			}
			$('#sidebarbg').toggleClass('hided');
			$('#sidebar').toggleClass('hided')
			$('.collapseBtn.leftbar').toggleClass('top shadow');
			//expand content
			
			if($('#content').length) {
				$('#content').toggleClass('hided');
			}
			if($('#content-two').length) {
				$('#content-two').toggleClass('hided');
			}	

		}

		//right sidebar clicked
		if ($this.hasClass('rightbar')) {
			
			if($(this).hasClass('hide')) {
				//show sidebar
				$this.removeClass('hide');
				$this.children('a').attr('title','Hide Right Sidebar');
				
			} else {
				//hide sidebar
				$this.addClass('hide');
				$this.children('a').attr('title','Show Right Sidebar')
			}
			$('#sidebarbg-right').toggleClass('hided');
			$('#sidebar-right').toggleClass('hided');
			if($('#content').length) {
				$('#content').toggleClass('hided-right');
			}
			if($('#content-one').length) {
				$('#content-one').toggleClass('hided');
			}
			if($('#content-two').length) {
				$('#content-two').toggleClass('hided-right');
			}	
			$('.collapseBtn.rightbar').toggleClass('top shadow')
		}
	});


	//------------- widget box magic -------------//

	var widget = $('div.box');
	var widgetOpen = $('div.box').not('div.box.closed');
	var widgetClose = $('div.box.closed');
	//close all widgets with class "closed"
	widgetClose.find('div.content').hide();
	widgetClose.find('.title>.minimize').removeClass('minimize').addClass('maximize');

	widget.find('.title>a').click(function (event) {
		event.preventDefault();
		var $this = $(this);
		if($this .hasClass('minimize')) {
			//minimize content
			$this.removeClass('minimize').addClass('maximize');
			$this.parent('div').addClass('min');
			cont = $this.parent('div').next('div.content')
			cont.slideUp(500, 'easeOutExpo'); //change effect if you want :)
			
		} else  
		if($this .hasClass('maximize')) {
			//minimize content
			$this.removeClass('maximize').addClass('minimize');
			$this.parent('div').removeClass('min');
			cont = $this.parent('div').next('div.content');
			cont.slideDown(500, 'easeInExpo'); //change effect if you want :)
		} 
		
	})

	//show minimize and maximize icons
	widget.hover(function() {
		    $(this).find('.title>a').show(50);	
		}
		, function(){
			$(this).find('.title>a').hide();	
	});

	//add shadow if hover box
	widget.not('.drag').hover(function() {
		    $(this).addClass('hover');	
		}
		, function(){
			$(this).removeClass('hover');	
	});

	//------------- Search forms  submit handler  -------------//
	if($('#tipue_search_input').length) {
		$('#tipue_search_input').tipuesearch({
          'show': 5
	     });
		$('#search-form').submit(function() {
		  return false;
		});

		//make custom redirect for search form in .heading
		$('#searchform').submit(function() {
			var sText = $('.top-search').val();
			var sAction = $(this).attr('action');
			var sUrl = sAction + '?q=' + sText;
			$(location).attr('href',sUrl);
			return false;
		});
	}
	//------------- To top plugin  -------------//
	$().UItoTop({ easingType: 'easeOutQuart' });

	//------------- Tooltips -------------//

	//top tooltip
	$('.tip').qtip({
		content: false,
		position: {
			my: 'bottom center',
			at: 'top center',
			viewport: $(window)
		},
		style: {
			classes: 'qtip-tipsy'
		}
	});

	//tooltip in right
	$('.tipR').qtip({
		content: false,
		position: {
			my: 'left center',
			at: 'right center',
			viewport: $(window)
		},
		style: {
			classes: 'qtip-tipsy'
		}
	});

	//tooltip in bottom
	$('.tipB').qtip({
		content: false,
		position: {
			my: 'top center',
			at: 'bottom center',
			viewport: $(window)
		},
		style: {
			classes: 'qtip-tipsy'
		}
	});

	//tooltip in left
	$('.tipL').qtip({
		content: false,
		position: {
			my: 'right center',
			at: 'left center',
			viewport: $(window)
		},
		style: {
			classes: 'qtip-tipsy'
		}
	});

	//------------- Jrespond -------------//
	var jRes = jRespond([
        {
            label: 'small',
            enter: 0,
            exit: 1000
        },{
            label: 'desktop',
            enter: 1001,
            exit: 10000
        }
    ]);

    jRes.addFunc({
        breakpoint: 'small',
        enter: function() {
           $('#sidebarbg,#sidebar,#content').removeClass('hided');
        },
        exit: function() {
           $('.collapseBtn.top.hide').removeClass('top hide');
        }
    });
	
	//------------- Uniform  -------------//
	//add class .nostyle if not want uniform to style field
	$("input, textarea, select").not('.nostyle').uniform();

	//remove overlay and show page
	$("#qLoverlay").fadeOut(250);
	$("#qLbar").fadeOut(250);

});