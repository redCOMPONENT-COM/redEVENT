/**
 * javascript for ajax navigation
 */
(function($){
	$(document).ready(function() {
		$('#redevent').on('click' , '#filter-go', function(event){
			event.preventDefault();
			red_ajaxnav.submitForm($(this).parents('form'));
		});

		$('#redevent').on('change' , '#filter_event, #limit, #filter', function(event){
			event.preventDefault();
			red_ajaxnav.submitForm($(this).parents('form'));
		});

		$('#redevent').on('keypress keydown keyup', '#filter', function(event){
			if (event.keyCode == 13) {
				event.preventDefault();
				red_ajaxnav.submitForm($(this).parents('form'));
			}
		});

		$('#redevent').on('click' , '#filter-reset', function(event){
			event.preventDefault();
			$('#el_filter select').val('0');
			$('#el_filter input').val('');
			red_ajaxnav.submitForm($(this).parents('form'));
		});


		$('.unreg-btn').click(function() {
			if (confirm(Joomla.JText._("COM_REDEVENT_MYEVENTS_CANCEL_REGISTRATION_WARNING")))
			{
				var id = $(this).attr('id').substr(6);
				var xref = $(this).attr('xref');
				var element = this;

				// Perform the ajax request
				$.ajax({
					url: 'index.php?option=com_redevent&task=registration.ajaxcancelregistration&tmpl=component',
					data : {'rid': id, 'xref': xref},
					dataType: 'json',
					beforeSend: function (xhr) {
						$(element).parents('tr').addClass('loading');
					}
				}).done(function(data) {
					$(element).parents('tr').removeClass('loading');

					if (data.status == 1) {
						$(element).parents('tr').remove();
					}
					else {
						alert(data.error);
					}
				});
			};
		});

		$('.deletelink').on('click', function(event){
			if (confirm(Joomla.JText._('COM_REDEVENT_CONFIRM_DELETE_DATE'))) {
				return true;
			}
			else {
				if (event.preventDefault) {
					event.preventDefault();
				} else {
					event.returnValue = false;
				}
				return false;
			}
		});

	});
})(jQuery);
