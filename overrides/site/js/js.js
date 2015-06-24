jQuery(document).ready(function($){

	if($(window).width() < 768 ){
		$("#menu #nav-menu li#item-109 a").click(function(){
			var wpurl = "http://hcafestivals.dk/da/festival/app";
			$(this).attr('href', wpurl);
		});

	}

	var visited = readCookie('mypopup');
	var url = window.location.href;
	if (!visited && url=='http://hcafestivals.dk/da/timeline') {
		$(document).ready(function(){
			var url = 'http://hcafestivals.dk/da/popup';
			SqueezeBox.open(url, {handler: 'iframe', size: {x: 600, y: 350}});
			createCookie('mypopup','no',0);
		});
	}


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
				opacity: 1
			});
		}
		else{
			$('.search .box').css({
				display: 'none',
				opacity: 0
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
		//var with2 = $(this).prev().innerWidth();
		// $(this).children('ul.dropdown-menu').css("margin-left", -with2);
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

	// Get selected options text
	var selectedtypetext = $('#filtercustom6 :selected').get().map(function(selected){
		return $(selected).text();
	});

	// Highlight matching elements in ul list
	$('#divselect6, #divselect9').find('li').each(function()
	{
		if (selectedtypetext.indexOf($(this).text()) > -1)
		{
			$(this).addClass('img-type-session-hover-active');
		}
	});

	// Toggle selected state of items on clicking
	$("ul#divselect6, ul#divselect7, ul#divselect9, ul#divselect10").find('li').click(function(){

		$(this).submit(function(){
			$(this).addClass('active');
		});

		var a = $(this).parent().attr('id');
		var fieldId = a.replace("divselect", "");

		//for type
		var selectedtypes = $('#filtercustom' + fieldId + ' :selected').get().map(function(selected){
			 return $(selected).text();
		});

		var selected = selectedtypes.indexOf($(this).text()) > -1;

		// Toggle selected state
		$('#filtercustom' + fieldId + ' option').eq($(this).attr('id')).prop('selected', !selected);

		$('#adminForm').submit();
	});

	// age filter label
	$('ul#divselect6, ul#divselect9').before(
		'<div class="type">' + Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_LABEL_TYPE')
		+ '<span class="valuetypefilter">' + Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_ALL') + '</span></div>'
	);
	$('ul#divselect6, ul#divselect9').addClass('hiddentype');

	// toggle type filter
	$('.type').toggle(
		function() {
			$('ul#divselect6, ul#divselect9').removeClass('hiddentype');
		},
		function() {
			$('ul#divselect6, ul#divselect9').addClass('hiddentype');
		}
	);

	// Hide selector when clicking away
	$(document).click(function(e) {
		var target = e.target;
		if (!$(target).is('.type') && !$(target).parents().is('.type')) {
			$('ul#divselect6, ul#divselect9').removeClass().addClass('hiddentype');
		}
	});

	// Hide selector when clicking away
	$(document).click(function(e) {
		var target = e.target;
		if (!$(target).is('.agefilter') && !$(target).parents().is('.agefilter')) {
			$('ul#divselect7, ul#divselect10').removeClass().addClass('hiddentype');
		}
	});

	// add label to age filter
	$('ul#divselect7, ul#divselect10').before(
		'<div class="agefilter">' + Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_LABEL_AGE')
		+ '<span class="valueagefilter" style="width: auto;">' + Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_ALL') + '</span></div>'
	);
	$('ul#divselect7, ul#divselect10').addClass('hiddentype');

	// Toggle age filter action
	$('.agefilter').toggle(
		function() {
			$('ul#divselect7, ul#divselect10').removeClass('hiddentype');
		}, function() {
			$('ul#divselect7, ul#divselect10').addClass('hiddentype');
		}
	);

	$('ul#divselect6 li, ul#divselect9 li').each(function(e)
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

	// Display of pictures types
	var selectedtype = [];
	$('#filtercustom6 :selected, #filtercustom9 :selected').each(function(i, option){
		selectedtype.push(option);
	});

	$contentfacesession='<div><img src="/templates/hcafestivals/images/pink.png" width="39px" height="29px"/></div>';
	$contentkitesession='<div><img src="/templates/hcafestivals/images/dieu.png" width="35px" height="34px"/></div>';

	$contentmusicsession='<div><img src="/templates/hcafestivals/images/music.png" width="14px" height="35px"/></div>';
	$contentbooksession='<div><img src="/templates/hcafestivals/images/book.png" width="36px" height="25px"/></div>';
	$contentartsession='<div><img src="/templates/hcafestivals/images/draw.png" width="30px" height="28px"/></div>';
	$contentspokensession='<div><img src="/templates/hcafestivals/images/spoken-word-type.png" width="39px" height="29px"/></div>';

	$.each(selectedtype, function(index, option) {
		if(option.value == 'Underholdning og teater')
		{
			$('.valuetypefilter').empty();
			$('.type').append($contentfacesession);
		}
		else if(option.value == 'Leg og læring')
		{
			$('.valuetypefilter').empty();
			$('.type').append($contentkitesession);
		}
		else if(option.value == 'Musik')
		{
			$('.valuetypefilter').empty();
			$('.type').append($contentmusicsession);
		}
		else if(option.value == 'Kulturformidling')
		{
			$('.valuetypefilter').empty();
			$('.type').append($contentbooksession);
		}
		else if(option.value == 'Kunst og kultur')
		{
			$('.valuetypefilter').empty();
			$('.type').append($contentartsession);
		}
		else if(option.value == 'Spoken Word Festival')
		{
			$('.valuetypefilter').empty();
			$('.type').append($contentspokensession);
		}
	});

	// Display of selected age options next to label
	var selectedage = $('#filtercustom7 :selected, #filtercustom10 :selected').get().map(function(selected){
		return $(selected).text();
	});

	switch (selectedage.length) {
		case 3:
			$('.valueagefilter').html('<span >'+selectedage[0] + ' & ' + selectedage[1] + ' & '+ selectedage[2] + '</span>')
				.css('margin-top','-13px');
			break;

		case 2:
			$('.valueagefilter').html('<span >'+selectedage[0]+'   & '+ selectedage[1] + '</span>');
			break;

		default:
			$('.valueagefilter').html('<span >'+selectedage[0]+'</span>')
				.css('margin-top','0');
	}

	// Su check date


	$('ul#divselectdate').before(
		'<div class="date-filter">' + Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_LABEL_DAY') + '<span class="valuedatefilter">'
		+ Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_ALL') + '</span></div>'
	);
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

	var days = [
		Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_DAY3_SUN'),
		Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_DAY3_MON'),
		Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_DAY3_TUE'),
		Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_DAY3_WED'),
		Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_DAY3_THU'),
		Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_DAY3_FRI'),
		Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_DAY3_SAT')
	];

	$("#divselectdate li").removeClass('active');
	var filter_date = $('#filter_date').val();

	// Add option for li on filter_date
	$('#filter_date option').each(function(i){
		var index = i + 1;
		//var additionClass = ($(this).is(':selected')) ? ' active' : '';
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

	$.each(selecteddag, function( index, value ) {
		var d = new Date(value);
		var dayName = days[d.getDay()];
		var date = d.getDate();
		var month = d.getMonth() + 1;

		$('.valuedatefilter').html(dayName + '<span>' + date + '/' + month + '</span>');

	});
	/* end check date */

	/* check filter_venuecategory */
	$("ul#divselectvenuecategory li.option1").before('<li id="0" class="option0">' + Joomla.JText._('COM_REDEVENT_TIMELINE_FILTERCATEGORY_SELECT') + '</li>');

	$('ul#divselectvenuecategory').before('<div class="venue-filter">'
	+ Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_LABEL_VENUE') + '<span class="valuevenuefilter">'
	+ Joomla.JText._('COM_REDEVENT_TIMELINE_FILTER_ALL') + '</span></div>');
	$('ul#divselectvenuecategory').addClass('hiddentype');

	$('.venue-filter').toggle(
		function() {
			$('ul#divselectvenuecategory').removeClass('hiddentype');
		},
		function() {
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
		$('#filter_date').prop('selectedIndex',3);
		$('#filter_venuecategory').prop('selectedIndex',0);
		$('#filtercustom6, #filtercustom7, #filtercustom9, #filtercustom10').prop('selectedIndex',0);
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


	function fixDiv() {
		var w_win = $(window).width();
		var w = w_win - 100;
		var venue = (w_win - w)/2 - 18;
		var $cache = $('.redevent-timeline .scrollbar');
		var w_scroll = w_win - 50 - venue;
		if ($(window).scrollTop() > 350)
			$cache.css({
				'position': 'fixed',
				'z-index': '999',
				'left': venue,
				'padding-left': '20px',
				'top': '0',
				'width': w_scroll
			});
		else
			$cache.css({
				'position': 'relative',
				'left': 'auto',
				'padding-left': '0',
				'top': 'auto'

			});
	}
	$(window).scroll(fixDiv);
	fixDiv();


	$('#above-main ul.nyheder').each(function() {
		var selecta = $(document.createElement('select')).insertBefore($(this).hide());
		selecta.className = "nyheder";
		$('>li a', this).each(function() {
			var a = $(this).click(function() {
					if ($(this).attr('target')==='_blank') {
						window.open(this.href);
					}
					else {
						window.location.href = this.href;
					}
				}),
				option = $(document.createElement('option')).appendTo(selecta).val(this.href).html($(this).html()).click(function() {
					a.click();
				});
		});
	});
	$('#above-main select').insertAfter(".blog h2");


	$(function(){
		// bind change event to select
		var link = $("#above-main ul li.active a").attr("href");
		$('.blog select').bind('change', function (index) {
			var url = $(this).val(); // get selected value

			if (url) { // require a URL
				window.location = url; // redirect
			}

			link = url;
			//return false;
		});
		var root = window.location.protocol + '//' + window.location.host;
		var fulllink = root+link;

		$('select option[value="'+ fulllink +'"]').attr('selected',true);
	});
});


function createCookie(name,value,days) {

	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path='http://hcafestivals.dk/da/timeline'";
	console.log(document.cookie);
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}
