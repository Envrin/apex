// document ready function
$(document).ready(function() { 	

	//------------- Select plugin -------------//
	$("#select1").select2();

	//create success msg for wizard
	function createSuccessMsg (loc, msg) {
		loc.append(
			'<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button><strong><i class="icon24 i-checkmark-circle"></i> Well done!</strong> '+ msg + ' </div>'
		);
	}

    //------------- Form wizard with steps-------------//
 	$("#wizard").formwizard({ 
	 	formPluginEnabled: true,
	 	validationEnabled: true,
	 	focusFirstInput : true,
	 	formOptions :{
			success: function(data){
				//produce success message
				createSuccessMsg($("#wizard .msg"), "You successfully submit this form");
			},
			resetForm: false
	 	},
	 	disableUIStyles: true,
	 	showSteps: true, //show the step
	 	validationOptions: {
	 		rules: {
	    		fname: {
					required: true,
					minlength: 4
				},
				lname: {
					required: true,
					minlength: 4
				},
				gender: {
					required: true
				},
				username1: {
					required: true,
					minlength: 4
				},
				password1: {
					required: true,
					minlength: 5
				},
				confirm_password1: {
					required: true,
					minlength: 5,
					equalTo: "#password1"
				},
				email1: {
					required: true,
					email: true
				}
			},
			messages: {
				fname: {
					required: "This field is required",
					minlength: "This field must consist of at least 4 characters"
				},
				lname: {
					required: "This field is required",
					minlength: "This field must consist of at least 4 characters"
				},
				password1: {
					required: "Please provide a password",
					minlength: "Your password must be at least 5 characters long"
				},
				confirm_password1: {
					required: "Please provide a password",
					minlength: "Your password must be at least 5 characters long",
					equalTo: "Please enter the same password as above"
				},
				email1: "Please enter a valid email address",
				gender: "Choise a gender"
			}	
	 	}
	});

   
    
	//--------------- Form validation ------------------//
	$('#select1').select2({placeholder: "Select"});
    $("#form-validate").validate({
    	ignore: null,
    	ignore: 'input[type="hidden"]',
    	rules: {
    		select1: "required",
			required: "required",
			requiredArea: "required",
			required1: {
				required: true,
				minlength: 4
			},
			password: {
				required: true,
				minlength: 5
			},
			confirm_password: {
				required: true,
				minlength: 5,
				equalTo: "#password"
			},
			email: {
				required: true,
				email: true
			},
			maxLenght: {
				required: true,
      			maxlength: 10
			},
			rangelenght: {
		      required: true,
		      rangelength: [10, 20]
		    },
		    minval: {
		      required: true,
		      min: 13
		    },
		    maxval: {
		      required: true,
		      max: 13
		    },
		    range: {
		      required: true,
		      range: [5, 10]
		    },
		    url: {
		      required: true,
		      url: true
		    },
		    date: {
		      required: true,
		      date: true
		    },
		    number: {
		      required: true,
		      number: true
		    },
		    digits: {
		      required: true,
		      digits: true
		    },
		    ccard: {
		      required: true,
		      creditcard: true
		    },
			agree: "required"
		},
		messages: {
			required: "Please enter a something",
			required1: {
				required: "This field is required",
				minlength: "This field must consist of at least 4 characters"
			},
			password: {
				required: "Please provide a password",
				minlength: "Your password must be at least 5 characters long"
			},
			confirm_password: {
				required: "Please provide a password",
				minlength: "Your password must be at least 5 characters long",
				equalTo: "Please enter the same password as above"
			},
			email: "Please enter a valid email address",
			agree: "Please accept our policy"
		}
    });

	//Boostrap modal
	$('#myModal').modal({ show: false});
	
	//add event to modal after closed
	$('#myModal').on('hidden', function () {
	  	console.log('modal is closed');
	})

});//End document ready functions

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