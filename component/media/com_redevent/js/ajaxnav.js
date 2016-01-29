/**
 * javascript for ajax navigation
 */
var red_ajaxnav = (function($) {

	var submitForm = function(form) {
		if (!form.format) {
			$('<input name="format" value="raw" type="hidden"/>').appendTo(form);
		}
		$.ajax({
			url: form.action,
			data: $(form).serialize(),
			dataType: 'html',
			type: 'post',
			beforeSend: function (xhr) {
				$(form).addClass('loading');
			}
		}).done(function(data) {
			var newdiv = $('<div/>').html(data);
			form.replaceWith(newdiv);
		});
	};

	var navigate = function(event) {
		event.preventDefault();
		var form = $(this).parents('form');

		if (form)
		{
			form.find('.redajax_limitstart').val($(this).attr('startvalue'));
			submitForm(form);
		}
	};

	var sortcolumn = function(event) {
		event.preventDefault();
		var form = $(this).parents('form');

		if (form)
		{
			form.find('.redajax_order').val($(this).attr('ordercol'));
			form.find('.redajax_order_dir').val($(this).attr('orderdir'));
			submitForm(form);
		}
	};

	$(document).ready(function() {
		$('#redevent, .redevent-ajaxnav').on('click', '.pagenav', navigate);
		$('#redevent, .redevent-ajaxnav').on('click', '.ajaxsortcolumn', sortcolumn);
	});

	// Provide an interface
	return {
		submitForm : submitForm
	}
})(jQuery);
