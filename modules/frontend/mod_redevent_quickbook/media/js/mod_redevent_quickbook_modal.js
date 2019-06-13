/**
 * redevent quickbook module javascript
 */
jQuery(function($) {
	$('#qbsubmit-btn').click(function() {
		var form = $(this).parents('form');

		if (!document.redformvalidator.isValid(form)) {
			return false
		}

		form.addClass('loading');

		$.ajax({
			url: form.prop('action'),
			data: form.serialize(),
			method : 'POST'
		})
		.done(function(response){
			form.removeClass('loading');

			var $resp = $('<div></div>').html(response);

			SqueezeBox.initialize();
			SqueezeBox.open($resp[0], {
				handler: 'adopt',
				size: {x: 300, y: 450}
			});
		})
	});
});
