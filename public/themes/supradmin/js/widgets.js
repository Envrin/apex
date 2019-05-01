// document ready function
$(document).ready(function() { 	

	//------------- Check all checkboxes  -------------//
	
	$("#masterCh").click(function() {
		var checkedStatus = $(this).find('span').hasClass('checked');
		$("#checkAll tr .chChildren input:checkbox").each(function() {
			this.checked = checkedStatus;
				if (checkedStatus == this.checked) {
					$(this).closest('.checker > span').removeClass('checked');
				}
				if (this.checked) {
					$(this).closest('.checker > span').addClass('checked');
				}
		});
	});

	//------------- Toggle button  -------------//
	$('.iToggle-button').toggleButtons({
	    width: 70,
	    label: {
	        enabled: "<span class='icon16 icomoon-icon-checkmark-2 white'></span>",
	        disabled: "<span class='icon16 icomoon-icon-cancel-3 white marginL5'></span>"
	    }
	});

	//------------- Custom scroll in widget box  -------------//
	if($(".scroll").length) {
		$(".scroll").niceScroll({
			cursoropacitymax: 0.7,
			cursorborderradius: 6,
			cursorwidth: "7px"
		});
	}
	if($(".scrolled").length) {
		$(".scrolled").niceScroll({
			cursoropacitymax: 0.7,
			cursorborderradius: 6,
			cursorwidth: "4px"
		});
	}

	//--------------- Typeahead ------------------//
	$('.typeahead').typeahead({
		source: ['jonh','carlos','arcos','stoner']
	})

	$('.findUser').typeahead({
		source: ['Sammy','Jonny','Sugge Elson','Elenna','Rayan','Dimitrios','Sidarh','Jana','Daniel','Morerira','Stoichkov']
	})

	//------------- Datepicker -------------//

	if($('#datepicker-inline').length) {
		$('#datepicker-inline').datepicker({
	        inline: true,
			showOtherMonths:true
	    });
	}

	//--------------- carousel ------------------//
	$('.carousel').carousel({
	  interval: 5000
	})

	//--------------- Prettyphoto ------------------//
	$("a[rel^='prettyPhoto']").prettyPhoto({
		default_width: 800,
		default_height: 600,
		theme: 'facebook',
		social_tools: false,
		show_title: false
	});
	//--------------- Gallery & lazzy load & jpaginate ------------------//
	$(function() {
		//hide the action buttons
		$('.actionBtn').hide();
		//show action buttons on hover image
		$('.galleryView>li').hover(
			function () {
			   $(this).find('.actionBtn').stop(true, true).show();
			},
			function () {
			    $(this).find('.actionBtn').stop(true, true).hide();
			}
		);
		//remove the gallery item after press delete
		$('.actionBtn>.delete').click(function(){
			$(this).closest('li').remove();
			/* destroy and recreate gallery */
		    $("div.holder").jPages("destroy").jPages({
		        containerID : "itemContainer",
		        animation   : "fadeInUp",
		        perPage		: 16,
		        scrollBrowse   : true, //use scroll to change pages
		        keyBrowse   : true,
		        callback    : function( pages ,items ){
		            /* lazy load current images */
		            items.showing.find("img").trigger("turnPage");
		            /* lazy load next page images */
		            items.oncoming.find("img").trigger("turnPage");
		        }
		    });
		    // add notificaton 
			$.pnotify({
				type: 'success',
			    title: 'Done',
	    		text: 'You just delete this picture.',
			    icon: 'picon icon16 brocco-icon-info white',
			    opacity: 0.95,
			    history: false,
			    sticker: false
			});

		});

	    /* initiate lazyload defining a custom event to trigger image loading  */
	    $("ul#itemContainer li img").lazyload({
	        event : "turnPage",
	        effect : "fadeIn"
	    });
	    /* initiate plugin */
	    $("div.holder").jPages({
	        containerID : "itemContainer",
	        animation   : "fadeInUp",
	        perPage		: 16,
	        scrollBrowse   : true, //use scroll to change pages
	        keyBrowse   : true,
	        callback    : function( pages ,items ){
	            /* lazy load current images */
	            items.showing.find("img").trigger("turnPage");
	            /* lazy load next page images */
	            items.oncoming.find("img").trigger("turnPage");
	        }
	    });
	});

	//------------- Form wizard with steps-------------//

 	$("#wizard").formwizard({ 
	 	formPluginEnabled: true,
	 	validationEnabled: false,
	 	focusFirstInput : true,
	 	formOptions :{
			success: function(data){
				
			},
			resetForm: true
	 	},
	 	disableUIStyles: true,
	 	showSteps: true //show the step
	});

	$("#wizard1").formwizard({ 
	 	formPluginEnabled: true,
	 	validationEnabled: false,
	 	focusFirstInput : true,
	 	formOptions :{
			success: function(data){
				
			},
			resetForm: true
	 	},
	 	disableUIStyles: true,
	 	showSteps: true, //show the step
	 	vertical: true
	});

    //--------------- Data tables ------------------//

	if($('table').hasClass('contactTable')){
		$('.contactTable').dataTable({
			"bJQueryUI": false,
			"bAutoWidth": false,
			"iDisplayLength": 5,
			"bLengthChange": false,
			"aoColumnDefs": [{ 
				"bSortable": false, "aTargets": [ 0, 1, 2, 3 ] 
			}],
		});
	}

	//------------- JQuery Autocomplete -------------//
    $(function() {
		var availableTags = [
			"ActionScript",
			"AppleScript",
			"Asp",
			"BASIC",
			"C",
			"C++",
			"Clojure",
			"COBOL",
			"ColdFusion",
			"Erlang",
			"Fortran",
			"Groovy",
			"Haskell",
			"Java",
			"JavaScript",
			"Lisp",
			"Perl",
			"PHP",
			"Python",
			"Ruby",
			"Scala",
			"Scheme"
		];
		$( "#autocomplete" ).autocomplete({
			source: availableTags
		});
	});

	//------------- File tree plugin  -------------//
	if($('#file-tree').length) {
	     $('#file-tree').fileTree({
	        root: '/images/',
	        script: 'php/filetree/jqueryFileTree.php',
	        expandSpeed: 500,
	        collapseSpeed: 500,
	        multiFolder: false
	    }, function(file) {
	        alert(file);
	    });
	}

	//------------- Combobox  -------------//
    (function( $ ) {
    $.widget( "custom.combobox", {
      _create: function() {
        this.wrapper = $( "<span>" )
          .addClass( "custom-combobox" )
          .insertAfter( this.element );
 
        this.element.hide();
        this._createAutocomplete();
        this._createShowAllButton();
      },
 
      _createAutocomplete: function() {
        var selected = this.element.children( ":selected" ),
          value = selected.val() ? selected.text() : "";
 
        this.input = $( "<input>" )
          .appendTo( this.wrapper )
          .val( value )
          .attr( "title", "" )
          .addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
          .autocomplete({
            delay: 0,
            minLength: 0,
            source: $.proxy( this, "_source" )
          })
          .tooltip({
            tooltipClass: "ui-state-highlight"
          });
 
        this._on( this.input, {
          autocompleteselect: function( event, ui ) {
            ui.item.option.selected = true;
            this._trigger( "select", event, {
              item: ui.item.option
            });
          },
 
          autocompletechange: "_removeIfInvalid"
        });
      },
 
      _createShowAllButton: function() {
        var input = this.input,
          wasOpen = false;
 
        $( "<a>" )
          .attr( "tabIndex", -1 )
          .attr( "title", "Show All Items" )
          .tooltip()
          .appendTo( this.wrapper )
          .button({
            icons: {
              primary: "ui-icon-triangle-1-s"
            },
            text: false
          })
          .removeClass( "ui-corner-all" )
          .addClass( "custom-combobox-toggle ui-corner-right" )
          .mousedown(function() {
            wasOpen = input.autocomplete( "widget" ).is( ":visible" );
          })
          .click(function() {
            input.focus();
 
            // Close if already visible
            if ( wasOpen ) {
              return;
            }
 
            // Pass empty string as value to search for, displaying all results
            input.autocomplete( "search", "" );
          });
      },
 
      _source: function( request, response ) {
        var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
        response( this.element.children( "option" ).map(function() {
          var text = $( this ).text();
          if ( this.value && ( !request.term || matcher.test(text) ) )
            return {
              label: text,
              value: text,
              option: this
            };
        }) );
      },
 
      _removeIfInvalid: function( event, ui ) {
 
        // Selected an item, nothing to do
        if ( ui.item ) {
          return;
        }
 
        // Search for a match (case-insensitive)
        var value = this.input.val(),
          valueLowerCase = value.toLowerCase(),
          valid = false;
        this.element.children( "option" ).each(function() {
          if ( $( this ).text().toLowerCase() === valueLowerCase ) {
            this.selected = valid = true;
            return false;
          }
        });
 
        // Found a match, nothing to do
        if ( valid ) {
          return;
        }
 
        // Remove invalid value
        this.input
          .val( "" )
          .attr( "title", value + " didn't match any item" )
          .tooltip( "open" );
        this.element.val( "" );
        this._delay(function() {
          this.input.tooltip( "close" ).attr( "title", "" );
        }, 2500 );
        this.input.data( "ui-autocomplete" ).term = "";
      },
 
      _destroy: function() {
        this.wrapper.remove();
        this.element.show();
      }
    });
  })( jQuery );

    if($("#combobox").length) {
    	$( "#combobox" ).combobox();
    }

	//Boostrap modal
	$('#myModal').modal({ show: false});
	
	//add event to modal after closed
	$('#myModal').on('hidden', function () {
	  	console.log('modal is closed');
	})

});//End document ready functions

//generate random number for charts
randNum = function(){
	//return Math.floor(Math.random()*101);
	return (Math.floor( Math.random()* (1+40-20) ) ) + 20;
}

//sparkline in sidebar area
var positive = [1,5,3,7,8,6,10];
var negative = [10,6,8,7,3,5,1]
var negative1 = [7,6,8,7,6,5,4]

$('#stat1').sparkline(positive,{
	height:15,
	spotRadius: 0,
	barColor: '#9FC569',
	type: 'bar'
});
$('#stat2').sparkline(negative,{
	height:15,
	spotRadius: 0,
	barColor: '#ED7A53',
	type: 'bar'
});
$('#stat3').sparkline(negative1,{
	height:15,
	spotRadius: 0,
	barColor: '#ED7A53',
	type: 'bar'
});
$('#stat4').sparkline(positive,{
	height:15,
	spotRadius: 0,
	barColor: '#9FC569',
	type: 'bar'
});
//sparkline in widget
$('#stat5').sparkline(positive,{
	height:15,
	spotRadius: 0,
	barColor: '#9FC569',
	type: 'bar'
});

$('#stat6').sparkline(positive, { 
	width: 70,//Width of the chart - Defaults to 'auto' - May be any valid css width - 1.5em, 20px, etc (using a number without a unit specifier won't do what you want) - This option does nothing for bar and tristate chars (see barWidth)
	height: 20,//Height of the chart - Defaults to 'auto' (line height of the containing tag)
	lineColor: '#88bbc8',//Used by line and discrete charts to specify the colour of the line drawn as a CSS values string
	fillColor: '#f2f7f9',//Specify the colour used to fill the area under the graph as a CSS value. Set to false to disable fill
	spotColor: '#e72828',//The CSS colour of the final value marker. Set to false or an empty string to hide it
	maxSpotColor: '#005e20',//The CSS colour of the marker displayed for the maximum value. Set to false or an empty string to hide it
	minSpotColor: '#f7941d',//The CSS colour of the marker displayed for the mimum value. Set to false or an empty string to hide it
	spotRadius: 3,//Radius of all spot markers, In pixels (default: 1.5) - Integer
	lineWidth: 2//In pixels (default: 1) - Integer
});

//sparklines (making loop with random data for all 7 sparkline)
i=1;
for (i=1; i<8; i++) {
 	var data = [[1, 3+randNum()], [2, 5+randNum()], [3, 8+randNum()], [4, 11+randNum()],[5, 14+randNum()],[6, 17+randNum()],[7, 20+randNum()], [8, 15+randNum()], [9, 18+randNum()], [10, 22+randNum()]];
 	placeholder = '.sparkLine' + i;
	$(placeholder).sparkline(data, { 
		width: 100,//Width of the chart - Defaults to 'auto' - May be any valid css width - 1.5em, 20px, etc (using a number without a unit specifier won't do what you want) - This option does nothing for bar and tristate chars (see barWidth)
		height: 30,//Height of the chart - Defaults to 'auto' (line height of the containing tag)
		lineColor: '#88bbc8',//Used by line and discrete charts to specify the colour of the line drawn as a CSS values string
		fillColor: '#f2f7f9',//Specify the colour used to fill the area under the graph as a CSS value. Set to false to disable fill
		spotColor: '#467e8c',//The CSS colour of the final value marker. Set to false or an empty string to hide it
		maxSpotColor: '#9FC569',//The CSS colour of the marker displayed for the maximum value. Set to false or an empty string to hide it
		minSpotColor: '#ED7A53',//The CSS colour of the marker displayed for the mimum value. Set to false or an empty string to hide it
		spotRadius: 3,//Radius of all spot markers, In pixels (default: 1.5) - Integer
		lineWidth: 2//In pixels (default: 1) - Integer
	});
}