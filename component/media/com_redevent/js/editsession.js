/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2015 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */
(function($){
	$(function(){
		document.formvalidator.setHandler('futuredate', function(value, $el) {
			if (!value) {
				return true;
			}

			var today = new Date();
			today = new Date(today.toDateString());
			var val = new Date(value);

			return val >= today;
		});

		document.formvalidator.setHandler('futuretime', function(value, $el) {
			var regexTime = /[0-2][0-9]:[0-5][0-9](:[0-5][0-9])*/;

			if (!value || value == '00:00:00' || value == '00:00') {
				return true;
			}

			if (!regexTime.test(value)) {
				return false;
			}

			var now = new Date();

			var startDate = $('#jform_dates').val();
			var val = new Date(startDate + 'T' + value);

			return val >= now;
		});

		document.formvalidator.setHandler('enddate', function(value, $el) {
			var regex = /201[0-9]-[0-1][0-9]-[0-3][0-9]/;
			if (!regex.test(value)) {
				return true;
			}

			var start = $('#jform_dates').val();

			if (!regex.test(start))
			{
				$('#jform_enddates').get(0).setCustomValidity(Joomla.JText._("LIB_REDEVENT_JS_VALIDATION_END_DATE_REQUIRES_START_DATE"));
				return false;
			}

			var startDate = new Date(start);
			var endDate = new Date(value);

			if (startDate > endDate)
			{
				$('#jform_enddates').get(0).setCustomValidity(Joomla.JText._("LIB_REDEVENT_JS_VALIDATION_END_DATE_BEFORE_START_DATE_ERROR"));
				return false;
			}

			$('#jform_enddates').get(0).setCustomValidity('');

			return true;
		});

		document.formvalidator.setHandler('endtime', function(value, $el) {
			var regexDate = /201[0-9]-[0-1][0-9]-[0-3][0-9]/;
			var regexTime = /[0-2][0-9]:[0-5][0-9](:[0-5][0-9])*/;

			var startDate = $('#jform_dates').val();
			var startTime = $('#jform_times').val();

			if (!(regexDate.test(startDate) || !regexTime.test(startTime)))
			{
				$('#jform_endtimes').get(0).setCustomValidity('');

				return true;
			}

			var endDate = $('#jform_enddates').val();
			var endTime = $('#jform_endtimes').val();

			if (!regexDate.test(endDate))
			{
				endDate = startDate;
			}

			if (!regexTime.test(endTime))
			{
				endTime = '00:00:00';
			}

			var fullStart = new Date(startDate + 'T' + startTime);
			var fullEnd = new Date(endDate + 'T' + endTime);

			if (fullStart > fullEnd)
			{
				$('#jform_endtimes').get(0).setCustomValidity(Joomla.JText._("LIB_REDEVENT_JS_VALIDATION_END_TIME_BEFORE_START_TIME_ERROR"));
				return false;
			}

			$('#jform_endtimes').get(0).setCustomValidity('');

			return true;
		});
	});
})(jQuery);
