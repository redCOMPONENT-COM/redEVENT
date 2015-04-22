jQuery(document).ready(function($){
	
	
	$('body').click(function() {
	   $('.search .box').css({
			display: 'none'
	   });
	});
	  $('input#mod-search-searchword').click(function(event) {
		event.stopPropagation();
	});
	$('a#modsearch').click(function(event) {
		event.stopPropagation();
		if($('.search .box').css('display')=='none')
		{
			$('.search .box').css({
				display: 'block',
				opacity: 1,
			});
		}
		else{
			$('.search .box').css({
				display: 'none',
				opacity: 0,
			});
		}

	});

	$(".wrapper-menu ul.nav.menu > li").hover(function() {
		/* Stuff to do when the mouse enters the element */
		var ulWidth = 0;
		$(".wrapper-menu ul.nav.menu ul.dropdown-menu").each(function() {
			//var lis = $(this).find("li");
			$(this).find("> li").each(function() {
				 var $this = $(this);

				ulWidth += $this.innerWidth();

			});

			$(this).css("width", ulWidth + 25);

			ulWidth = 0;

		});

	}, function() {
		/* Stuff to do when the mouse leaves the element */
	});
	/*Hover img in menu galleri*/
	$('#reditemCategories .reditem_photo_subcate_inner').hover(function() {
		/* Stuff to do when the mouse enters the element */
		$(this).css('border-color', '#E5007D');
	}, function() {
		/* Stuff to do when the mouse leaves the element */
		$(this).css('border-color', '#e8e8e8');
	});
	$('.reditemItem .reditem_photo_inner').hover(function() {
		/* Stuff to do when the mouse enters the element */
		$(this).css('border-color', '#E5007D');
	}, function() {
		/* Stuff to do when the mouse leaves the element */
		$(this).css('border-color', '#e8e8e8');
	});
	$(".wrapper-menu ul.nav.menu > li").hover(function() {
		var with2 = $(this).prev().innerWidth();
		$(this).children('ul.dropdown-menu').css("margin-left", -with2);
		//$(this).children('ul.dropdown-menu li a:after').css("left", with2 + 30);
	}, function() {
		/* Stuff to do when the mouse leaves the element */
	});


	$('.slide-out-div').tabSlideOut({
		tabHandle: '.handle',                     //class of the element that will become your tab
		pathToTabImage: '', //path to the image for the tab //Optionally can be set using css
		imageHeight: '122px',                     //height of tab image           //Optionally can be set using css
		imageWidth: '40px',                       //width of tab image            //Optionally can be set using css
		tabLocation: 'right',                      //side of screen where tab lives, top, right, bottom, or left
		speed: 300,                               //speed of animation
		action: 'click',                          //options: 'click' or 'hover', action to trigger animation
		topPos: '280px',                          //position from the top/ use if tabLocation is left or right
		leftPos: '20px',                          //position from left/ use if tabLocation is bottom or top
		fixedPosition: true                      //options: true makes it stick(fixed position) on scroll
	});

	$(window).resize(function() {
		windowWidth = $(this).width();
		screenWidth = window.screen.availWidth;
		var scrollWidth2 = windowWidth - $(window).scrollLeft();
		var widthMove = screenWidth - scrollWidth2;
		if(scrollWidth2 < 1920)
		{
			$('.standard_slide .slide-img').css("margin-left", -(widthMove/2));
		}
		else
		{
			$('.standard_slide .slide-img').css("margin-left", 0);
		}
	});

	try
	{
		$(".reditem_photo_images .reditem_image a").addClass('photo');
		$('.reditem_photo_images .reditem_image a.cboxElement').colorbox({
			rel:'photo',
			width:'650px',
			transition:'fade',
			reposition: 'true'
		});
	}
	catch(e){}

	var i = 1;
	var curD = '';

	$(".custom-filter select option").each(function()
	{
		var a = $(this).parent().attr('id');

		if (curD == '')
		{
			curD = a;
		}

		if ($(this).val() != '')
		{
			var id = a.replace("filtercustom", "");
			id = id.replace("filter_", "");

			if (id != 'date')
				$("#divselect" + id).append('<li id="' + i + '" class="option' + i + '"><div class="img-type-session"></div>' + '<span>'+$(this).text() + '</span></li>');
			

			i++;
		}


		if (curD != a)
		{
			i = 1;

			curD = a;
		}
	});

	var countli = $( "#divselect6 >li" ).length;
    var selectedtypetest = [];
    $('#filtercustom6 :selected').each(function(i, selected){
		  selectedtypetest[i] = $(selected).text();
	});
	var length=selectedtypetest.length;
		for(var i=0;i<length;i++)
		{
			$('#divselect6').find('li').each(function()
			{
				if($(this).text() == selectedtypetest[i])
				{
					$(this).addClass('img-type-session-hover-active');
				}
			});
		}
	
	$("ul#divselect7").find('li').click(function(){
		
		var a = $(this).parent().attr('id');
		var id = a.replace("divselect", "");
		//$('#filtercustom' + id + ' option').eq($(this).attr('id')).prop('selected', true);
		var selectedage1 = [];
		$('#filtercustom7 :selected').each(function(i, selected){
		  selectedage1[i] = $(selected).text();
		});
		var length=selectedage1.length;
		for(var i=0;i<length;i++)
		{
			if($(this).text() == selectedage1[i])
			{
				$('#filtercustom' + id + ' option').eq($(this).attr('id')).prop('selected', false);
				break;
			}
			else
			{

				$('#filtercustom' + id + ' option').eq($(this).attr('id')).prop('selected', true);
			}
		}
		
		$('#adminForm').submit();
		

	});
	$("ul#divselect6").find('li').click(function(){

		
		$(this).submit(function(){ 
			$(this).addClass('active');
		});
		var a = $(this).parent().attr('id');
		var id = a.replace("divselect", "");
		//for type
		var selectedtypes = [];
		$('#filtercustom6 :selected').each(function(i, selected){
		  selectedtypes[i] = $(selected).text();
		});
		var length1=selectedtypes.length;
		for(var i=0;i<length1;i++)
		{
			if($(this).text() == selectedtypes[i])
			{
				$('#filtercustom' + id + ' option').eq($(this).attr('id')).prop('selected', false);
				break;
			}
			else
			{
				
				$('#filtercustom' + id + ' option').eq($(this).attr('id')).prop('selected', true);

				
			}
		}
		
		$('#adminForm').submit();
		
		

	});

	$('ul#divselect6').before('<div class="type">Type<span class="valuetypefilter">Alle</span></div>');
	$('ul#divselect6').addClass('hiddentype');
	
	$('.type').toggle(
		  function() {
		  	
		    $('ul#divselect6').removeClass('hiddentype');$('ul#divselect7').removeClass().addClass('hiddentype');
		  }, function() {
		    $('ul#divselect6').addClass('hiddentype');
		  }
	);

	$(document).click(function(e) {
	    var target = e.target;
	    if (!$(target).is('.type') && !$(target).parents().is('.type')) {
	       $('ul#divselect6').removeClass().addClass('hiddentype');
	    }
	});
	$(document).click(function(e) {
	    var target = e.target;
	    if (!$(target).is('.agefilter') && !$(target).parents().is('.type')) {
	       $('ul#divselect7').removeClass().addClass('hiddentype');
	    }
	});


	$('ul#divselect7').before('<div class="agefilter">Aldersgruppe<span class="valueagefilter">Alle</span></div>');
	$('ul#divselect7').addClass('hiddentype');
	$('.agefilter').toggle(
		  function() {
		    $('ul#divselect7').removeClass('hiddentype');$('ul#divselect6').removeClass().addClass('hiddentype');
		  }, function() {
		    $('ul#divselect7').addClass('hiddentype');
		  }
	);
	$('ul#divselect6 li').each(function(e)
	{
		$(this).hover(
		  function() {
		    $( this ).addClass('img-type-session-hover');
		  }, function() {
		    $( this ).removeClass('img-type-session-hover');
		  }
		);
	});

	$('.timeline-sessions-wrapper .timeline-sessions .time-marker').height($('.timeline-sessions-wrapper .timeline-sessions').height());
	$('.redevent-timeline .timeline-sessions-wrapper').css('width', $(window).width()+'px');
	
	//get value of multiple selected type
	var selectedtype = [];
	$('#filtercustom6 :selected').each(function(i, selected){
	  selectedtype[i] = $(selected).text();
	});
	$contentfacesession='<div><img src="/templates/hcafestivals/images/pink.png" width="39px" height="29px"/></div>';
	$contentkitesession='<div><img src="/templates/hcafestivals/images/dieu.png" width="35px" height="34px"/></div>';

	$contentmusicsession='<div><img src="/templates/hcafestivals/images/music.png" width="14px" height="35px"/></div>';
	$contentbooksession='<div><img src="/templates/hcafestivals/images/book.png" width="36px" height="25px"/></div>';
	$contentartsession='<div><img src="/templates/hcafestivals/images/draw.png" width="30px" height="28px"/></div>';
	$contentspokensession='<div><img src="/templates/hcafestivals/images/spoken-word-type.png" width="39px" height="29px"/></div>';
	$.each(selectedtype, function( index, value ) {
  		
	    if(value == 'Underholdning og teater')
	    {
	    	$('.valuetypefilter').empty();

	    	$('.type').append($contentfacesession);
			
			
	    }
	    else if(value == 'Leg og læring')
	    {
	    	$('.valuetypefilter').empty();
	    	$('.type').append($contentkitesession);
	    }
	    else if(value == 'Musik')
	    {
	    	$('.valuetypefilter').empty();
	    	$('.type').append($contentmusicsession);
	    }
	    else if(value == 'Kulturformidling')
	    {
	    	$('.valuetypefilter').empty();
	    	$('.type').append($contentbooksession);
	    }
	    else if(value == 'Kunst og kultur')
	    {
	    	$('.valuetypefilter').empty();
	    	$('.type').append($contentartsession);
	    }
	    else if(value == 'Spoken Word Festival')
	    {
	    	$('.valuetypefilter').empty();
	    	$('.type').append($contentspokensession);
	    }
	//get value of multiple selected age
	var selectedage = [];
	$('#filtercustom7 :selected').each(function(i, selected){
	  selectedage[i] = $(selected).text();
	});   
	
	$.each(selectedage, function( index, value ) {
		
		if(index==1)
		{
			$('.valueagefilter').html('<span >'+selectedage[0]+'   & '+ selectedage[1] + '</span>');
		}
		else if(index==2)
		{
			$('.valueagefilter').html('<span >'+selectedage[0]+'   & ' + selectedage[1] + '& '+ selectedage[2] + '</span>');
			$('.valueagefilter').css('margin-top','-13px');
		}
		else
		{
			$('.valueagefilter').html('<span >'+selectedage[0]+'</span>');
			$('.valueagefilter').css('margin-top','0');
		}
		
	});      
	  
	});
	

	// Su check date


	$('ul#divselectdate').before('<div class="date-filter">Dag<span class="valuedatefilter">Alle</span></div>');
	$('ul#divselectdate').addClass('hiddentype');
	
	$('.date-filter').toggle(
		  function() {
		  	
		    $('ul#divselectdate').removeClass('hiddentype');
		  }, function() {
		    $('ul#divselectdate').addClass('hiddentype');
		  }
	);
	
	$(document).click(function(e) {
	    var target = e.target;
	    if (!$(target).is('.date-filter') && !$(target).parents().is('.date-filter')) {
	       $('ul#divselectdate').removeClass().addClass('hiddentype');
	    }
	});

/*	$("#filter_date option").filter(function() {
	    //may want to use $.trim in here
	    return $(this).text() == '2015-08-16'; 
	}).attr('selected', true);*/
	    

    $("#divselectdate li").removeClass('active');
    var filter_date = $('#filter_date').val();

	// Add option for li on filter_date
	$('#filter_date option').each(function(i){
		var index = i + 1;
		//var additionClass = ($(this).is(':selected')) ? ' active' : '';
		var days = [
		    'søn',
		    'man',
		    'tir',
		    'ons',
		    'tor',
		    'fre',
		    'lør'
		];
		var d = new Date($(this).text());
		var dayName = days[d.getDay()];
		var date = d.getDate();
		var month = d.getMonth() + 1;
		var fulldate = dayName + '<span>' + date + '/' + month + '</span>';
		
		var opClass = 'option' + index;
		
		if ($(this).val() == filter_date) {
			opClass += ' active';		
		}

		$('<li>').attr('id', index)
			.addClass(opClass)
			.attr('val', $(this).text())
			.append($('<div>').addClass('img-type-session ' ))
			.append($('<span>').html(fulldate))
			.appendTo($('#divselectdate'));
	});


	$('ul#divselectdate').on("click", "li", function (event) {
		$("#divselectdate").children().removeAttr("selected");
		$('#filter_date').val($(this).attr('val'));

		$('#adminForm').submit();
	});
	
	var selecteddag = [];
	$('#filter_date :selected').each(function(i, selected){
	  selecteddag[i] = $(selected).text();
	});


	var days = [
	    'søn',
	    'man',
	    'tir',
	    'ons',
	    'tor',
	    'fre',
	    'lør'
	];

    $.each(selecteddag, function( index, value ) {
		var d = new Date(value);
		var dayName = days[d.getDay()];
		var date = d.getDate();
		var month = d.getMonth() + 1;

    	$('.valuedatefilter').html(dayName + '<span>' + date + '/' + month + '</span>');

	 });
    /* end check date */

    /* check filter_venuecategory */

	    $('ul#divselectvenuecategory').before('<div class="venue-filter">Venue<span class="valuevenuefilter">Alle</span></div>');
		$('ul#divselectvenuecategory').addClass('hiddentype');
		
		$('.venue-filter').toggle(
			  function() {
			  	
			    $('ul#divselectvenuecategory').removeClass('hiddentype');
			  }, function() {
			    $('ul#divselectvenuecategory').addClass('hiddentype');
			  }
		);
		
		$(document).click(function(e) {
		    var target = e.target;
		    if (!$(target).is('.venue-filter') && !$(target).parents().is('.venue-filter')) {
		       $('ul#divselectvenuecategory').removeClass().addClass('hiddentype');
		    }
		});

		// Add option for li on filter_venue
		$('#filter_venuecategory option').each(function(i){
			var index = i + 1;
			var additionClass = ($(this).is(':selected')) ? ' active' : '';
			
			$('<li>').attr('id', index)
				.addClass('option' + index + ' ' + additionClass)
				.attr('val', $(this).text())
				.append($('<div>').addClass('img-type-session ' + additionClass))
				.append($('<span>').html($(this).text()));
				//.appendTo($('#divselectvenuecategory'));
		});


		$('ul#divselectvenuecategory').on("click", "li", function (event) {

			var selectedvenuetest = [];

			var a = $(this).parent().attr('id');
			var id = a.replace("divselect", "");

			$('#filter_venuecategory :selected').each(function(i, selected){
			  selectedvenuetest[i] = $(selected).text();
			});
			var length1=selectedvenuetest.length;

			for(var i=0;i<length1;i++)
			{
				if($(this).attr('val') == selectedvenuetest)
				{
					$('#filter_' + id + ' option').eq($(this).attr('id')).prop('selected', false);
					break;
				}
				else
				{
					
					$('#filter_' + id + ' option').eq($(this).attr('id')).prop('selected', true);

					
				}
			}

			$('#adminForm').submit();
		});



		var selectedvenue = [];
		$('#filter_venuecategory :selected').each(function(i, selected){
		  selectedvenue[i] = $(selected).text();
		});

	    $.each(selectedvenue, function( index, value ) {

	    	$('.valuevenuefilter').html(value);

		 });


	/* end check filter_venuecategory */    

    $(".event-map.venuemap").text("Se arrangementet på kortet");

    $("#sessions .session-status .session-paid a").before('<label></label>');
    $("#midle-top1 .readmore_button").find("a").each(function() {
    	var text = $(this).text();
    	if (text == "")
    		$(this).css('display', 'none');
    });

    $(".btn-reset-filter-redvent").click(function() {
    	/* Act on the event */
    	$('#filter_date').prop('selectedIndex',0);
    	$('#filter_venuecategory').prop('selectedIndex',0);
    	$('#filtercustom6').prop('selectedIndex',0);
    	$('#filtercustom7').prop('selectedIndex',0);
    });

$( window ).resize(function() {
	pad();
});
pad();

function pad(){
    var w_win = $(window).width();
    var w = 926 - 40;
    var padding = (w_win - w)/2;

    $(".timeline-wrapper .venues-list").css("padding-left", padding);
    //$(".scrollbar .sessions-left").css("width", $(".timeline-wrapper .venues-list").woth);

    
  var venue = $('.venues-list').width();
    
    var w_ss = 926 - venue - 30;
    
     $(".scrollbar .sessions-left").width(venue);
    $(".scrollbar .sessions-time").width(w_ss);
}



    function getUrlParameter(sParam)
    {
        var sPageURL = window.location.search.substring(1);
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++)
        {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] == sParam)
            {
                return sParameterName[1];
            }
        }
    }

});
