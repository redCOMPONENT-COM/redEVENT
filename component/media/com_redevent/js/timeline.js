/**
 * For timeline view
 */

(function($){
	$(document).ready(function(){

		var timelineWidth = parseInt($('.timeline-sessions').css('width'));
		var hourWidth = timelineWidth / 15;

		// There is a conflict between mootools more and jQuery UI slider (makes the div disappear). next line is a workaround
		$('#timeslider')[0].slide = null;

		// Now we can use it
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

		/**
		 * active session
		 * @type object
		 */
		var active = false;

		$('.timeline-venues').click(function(event){
			event.preventDefault();

			if (active) {
				active.removeClass('active');
				active = null;
			}
			else {
				active = $(this).addClass('active');
			}

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


		// Su check date

/*		$('ul#divselectdate').before('<div class="date-filter">Dag<span class="valuedatefilter">Alle</span></div>');
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

		// Add option for li on filter_date
		$('#filter_date option').each(function(i){
			var index = i + 1;
			var additionClass = ($(this).is(':selected')) ? ' active' : '';
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

			$('<li>').attr('id', index)
				.addClass('option' + index + ' ' + additionClass)
				.attr('val', $(this).text())
				.append($('<div>').addClass('img-type-session ' + additionClass))
				.append($('<span>').html(fulldate))
				.appendTo($('#divselectdate'));
		});

		$('ul#divselectdate').on("click", "li", function (event) {
			$('#filter_date').val($(this).attr('val'));
			$('#adminForm').submit();
		});

		var selecteddagtest = [];
	    $('#filter_date :selected').each(function(i, selected){
			  selecteddagtest[i] = $(selected).text();

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

		});*/
	});
})(jQuery);
