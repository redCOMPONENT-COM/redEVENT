(function($) {

	var allvisible = true;

	function toggleEvents()
	{
		allvisible = true;
		var visible = new Array();
		var i = 0;
		$('.eventCat').each(function(index) {
			if (!$(this).hasClass('catoff')) {
				visible[i++] = 'cat' + $(this).attr('catid');
			}
			else {
				allvisible = false;
			}
		});

		$('div.eventcontent div').each(function(index){
			var show = false;
			for ( i in visible ) {
				if ($(this).hasClass(visible[i])) {
					show = true;
					break;
				}
			}
			if (show == true) {
				$(this).css('display', 'block');
			}
			else {
				$(this).css('display', 'none');
			}
		});
	}

	$(document).ready(function() {
		/* categories filtering */
		$('.eventCat').click(function(event) {
			if (allvisible) {
				// Make only this one visible
				allvisible = false;
				$('.eventCat').addClass('catoff');
				this.removeClass('catoff');
			}
			else {
				this.toggleClass('catoff');
			}
			toggleEvents();
		});

		$('#buttonshowall').click(function(event) {
			$('.eventCat').removeClass('catoff');
			toggleEvents();
		});

		$('#buttonhideall').click(function(event) {
			$('.eventCat').addClass('catoff');
			toggleEvents();
		});
	});
})(jQuery)

