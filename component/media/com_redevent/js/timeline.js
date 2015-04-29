/**
 * For timeline view
 */

(function($){

	function setCurrentTimeLine() {
		var timelineWidth = parseInt($('.timeline-sessions').css('width'));

		// the timeline covers 15 hours, from 9 to 24, let's compute pixels per minute from there
		var timelinePixelPerMinute = timelineWidth / (15 * 60);

		var currentDate = new Date();

		// Only display if after 9 am
		if (currentDate.getHours() < 9)
		{
			return;
		}

		var offset = timelinePixelPerMinute * ((currentDate.getHours() - 9) * 60 + currentDate.getMinutes());

		// Draw the line
		var lineDiv = $('<div id="currentTime"/>')
			.css('position', 'absolute')
			.css('left', offset + 'px')
			.css('top', '0px')
			.css('height', '100%')
			.css('background-color', 'red')
			.css('width', '2px');

		lineDiv.appendTo($('.timeline-sessions'));
	}

	$(document).ready(function(){

		var timelineWidth = parseInt($('.timeline-sessions').css('width'));
		var hourWidth = timelineWidth / 15;

		// There is a conflict between mootools more and jQuery UI slider (makes the div disappear). next lines is a workaround
		$('#timeslider').slide = null;
		if ($('#timeslider')[0]) {
			$('#timeslider')[0].slide = null;
		}
		$('#timeslider').removeAttr('slide');

		// Now we can use it
		$('#timeslider').slider({
			min: 0,
			max: 14,
			slide: function(event, ui) {
				var l = hourWidth * ui.value;
				$('.timeline-sessions').css('left', '-' + (hourWidth * ui.value) + 'px');

				$('.timeline-venues').each(function(){
					var bW = parseInt($(this).attr('relw'));
					var bL = parseInt($(this).attr('rell'));

					var newW = bW + bL - l;

					if (newW < 50 || l == 0)
					{
						$(this).css('width', bW);
						$(this).css('left', bL);
					}
					else if (l >= bL)
					{
						$(this).css('width', newW);
						$(this).css('left', l);
					}
				});
			}
		});

		// Sort time checkbox
		$('#timeline-sort-venue-checkbox').change(function(event){
			event.preventDefault();

			if ($(this).is(':checked')) {
				$('#timeline-filter-order').val('l.venue');
				$('#timeline-filter-direction').val('asc');
			}
			else {
				$('#timeline-filter-order').val('x.dates');
				$('#timeline-filter-direction').val('ASC');
			}

			$('#adminForm').submit();
		});

		$('.timeline-venues').click(function(event){
			event.preventDefault();

			$('.timeline-venues.active').each(function(){
				$(this).removeClass('active');
			});

			$(this).addClass('active');

			var hiddenInfor = $(this).next('.session-infor-hidden');
			var rowIndex = hiddenInfor.attr('data-row');

			var parentBase = $(this).parents('div.time-venues-base');

			var topPos = parentBase.height() + baseHeight;

			var targetInfor = $('#' + $(hiddenInfor).attr('data-target'));
			var targetVenueFake = $('#timeline-venues-fake-' + rowIndex);

			$('#timeline-session-information').hide();

			// Show information only for clicked session
			$('.time-venues-base-information').each(function(index){
				if ($(this).attr('id') != targetInfor.attr('id')) {
					$(this).hide();
				}
			});

			// Use an empty div to shift the venues name
			$('.timeline-venues-fake').each(function(index){
				if ($(this).attr('id') != targetVenueFake.attr('id')) {
					$(this).hide();
				}
			});

			// Replace information for current clicked session
			$('#timeline-session-information').find('.col-left').html($(hiddenInfor).find('.session-left-infor').html());
			$('#timeline-session-information').find('.col-right').html($(hiddenInfor).find('.session-right-infor').html());

			// Calculate top so we display the information after the previous 'rows'
			parentBase.prevAll('.time-venues-base').each(function(index){
				topPos += $(this).height();
			});

			// Set top position
			$('#timeline-session-information').css('top', topPos + 'px');

			targetVenueFake.slideToggle('slow');

			targetInfor.slideToggle('slow', function(){
				if (targetInfor.css('display') != 'none') {
					$('#timeline-session-information').fadeIn();
				}
			});
		});

		setCurrentTimeLine();
	});
})(jQuery);
