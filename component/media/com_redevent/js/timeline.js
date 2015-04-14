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
				$('.timeline-sessions').css('left', '-' + (hourWidth * ui.value) + 'px');
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

			var hiddenInfor = $(this).next('.session-infor-hidden');
			var rowIndex = hiddenInfor.attr('data-row');
			var parentBase = $(this).parent().parent();
			var topPos = parentBase.height() + baseHeight;
			var targetInfor = $('#' + $(hiddenInfor).attr('data-target'));
			var targetVenueFake = $('#timeline-venues-fake-' + rowIndex);

			$('#timeline-session-information').hide();

			$('.time-venues-base-information').each(function(index){
				if ($(this).attr('id') != targetInfor.attr('id')) {
					$(this).hide();
				}
			});

			$('.timeline-venues-fake').each(function(index){
				if ($(this).attr('id') != targetVenueFake.attr('id')) {
					$(this).hide();
				}
			});

			parentBase.prevAll('.time-venues-base').each(function(index){
				topPos += $(this).height();
			});

			$('#timeline-session-information').find('.col-left').html($(hiddenInfor).find('.session-left-infor').html());
			$('#timeline-session-information').find('.col-right').html($(hiddenInfor).find('.session-right-infor').html());
			$('#timeline-session-information').css('top', topPos + 'px');

			targetVenueFake.slideToggle('slow');

			targetInfor.slideToggle('slow', function(){
				if (targetInfor.css('display') != 'none') {
					$('#timeline-session-information').fadeIn();
				}
			});
		});
	});
})(jQuery);
