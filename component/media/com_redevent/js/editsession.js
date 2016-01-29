/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2015 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */
window.addEvent('domready', function(){
	document.formvalidator.setHandler('futuredate', function(value) {
		if (!value) {
			return true;
		}

		var today = new Date();
		today = new Date(today.toDateString());
		var val = new Date(value);

		return val >= today;
	});

	document.formvalidator.setHandler('futuretime', function(value) {
		var regexTime = /[0-2][0-9]:[0-5][0-9](:[0-5][0-9])*/;

		if (!value || value == '00:00:00' || value == '00:00') {
			return true;
		}

		if (!regexTime.test(value)) {
			return false;
		}

		var now = new Date();

		var startDate = document.id('jform_dates').get('value');
		var val = new Date(startDate + 'T' + value);

		return val >= now;
	});

	// Make sure end date is not before start date
	document.formvalidator.setHandler('enddate', function(value) {
		var regex = /201[0-9]-[0-1][0-9]-[0-3][0-9]/;
		if (!regex.test(value)) {
			return true;
		}

		var start = document.id('jform_dates').get('value');

		if (!regex.test(start))
		{
			document.id('jform_enddates').setCustomValidity(Joomla.JText._("LIB_REDEVENT_JS_VALIDATION_END_DATE_REQUIRES_START_DATE"));
			return false;
		}

		var startDate = new Date(start);
		var endDate = new Date(value);

		if (startDate > endDate)
		{
			document.id('jform_enddates').setCustomValidity(Joomla.JText._("LIB_REDEVENT_JS_VALIDATION_END_DATE_BEFORE_START_DATE_ERROR"));
			return false;
		}

		document.id('jform_enddates').setCustomValidity('');

		return true;
	});

	document.formvalidator.setHandler('endtime', function(value) {
		var regexDate = /201[0-9]-[0-1][0-9]-[0-3][0-9]/;
		var regexTime = /[0-2][0-9]:[0-5][0-9](:[0-5][0-9])*/;

		var startDate = document.id('jform_dates').get('value');
		var startTime = document.id('jform_times').get('value');

		if (!(regexDate.test(startDate) && regexTime.test(startTime)))
		{
			document.id('jform_enddates').setCustomValidity(Joomla.JText._("LIB_REDEVENT_JS_VALIDATION_END_TIME_REQUIRES_START_TIME"));
			return false;
		}

		var endDate = document.id('jform_enddates').get('value');
		var endTime = document.id('jform_endtimes').get('value');

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
			document.id('jform_endtimes').setCustomValidity(Joomla.JText._("LIB_REDEVENT_JS_VALIDATION_END_TIME_BEFORE_START_TIME_ERROR"));
			return false;
		}

		document.id('jform_endtimes').setCustomValidity('');

		return true;
	});
});
