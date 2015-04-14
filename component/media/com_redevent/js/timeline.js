/**
 * For timeline view
 */

(function($){
	$(document).ready(function(){

		var timelineWidth = parseInt($('.timeline-sessions').css('width'));
		var hourWidth = timelineWidth / 15;
		$('#timeslider').slider({
			min: 0,
			max: 14,
			slide: function(event, ui) {
				$('#timeval').html(ui.value);
				$('.timeline-sessions').css('left', '-' + (hourWidth * ui.value) + 'px');
			}
		});
	});
})(jQuery);
