/**
 * redevent quickbook module javascript
 */
(function($) {
	$(function() {
		$('#qbsubmit-btn').click(function() {
			var form = $(this).parents('form');
			if (document.redformvalidator.isValid(form)) {
				form.submit();
			}
		});
	});
})(jQuery);
