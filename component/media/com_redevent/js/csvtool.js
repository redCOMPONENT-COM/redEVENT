/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

/**
 * this file manages the js script for attendees csv export options
 */

(function($){

	// fills the event selector with an ajax query
	function updateEvents()
	{
		$('#jform_event').empty().addClass('loading');

		$.ajax({
			url : 'index.php?option=com_redevent&task=attendeescsv.eventoptions&format=raw',
			method: 'post',
			data : {'venue_id':$('#jform_venue').val(), 'category_id': $('#jform_category').val()},
			dataType: 'json'
		}).done(function(data) {
			var select = $('#jform_event');
			select.empty();

			for (var i = 0; i < data.length ; i++ ) {
				$('<option value="' + data[i].id + '">' + data[i].title + '</option>').appendTo(select);
			}

			select.removeClass('loading');
		});
	}

	$(document).ready(function() {
		$('#jform_form').change(function() {
			if (this.value == 0)
			{
				$('.conditional-form').hide();
			}
			else
			{
				$('.conditional-form').show();

				updateEvents();
			}
		});

		$('#jform_venue, #jform_category').change(updateEvents);

		$('#jform_form').trigger('change');
	});
})(jQuery);
