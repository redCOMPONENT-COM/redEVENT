/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */
(function($){
	$(document).ready(function() {
		$('#recurrence input[name*="[type]"]').click(function() {
			toggleRecurrenceType($(this).val());
		})

		$('#recurrence input[name*="[type]"]:checked').each(function(index, element){
			toggleRecurrenceType($(element).val());
		});

		$('#recurrence input[name*="[repeat_type]"]').click(function() {
			toggleLimitType($(this).val());
		})

		$('#recurrence input[name*="[repeat_type]"]:checked').each(function(index, element){
			toggleLimitType($(element).val());
		});

		$('#recurrence input[name*="[month_type]"]').click(function() {
			toggleMonthDaysSelect($(this).val());
		})

		$('#recurrence input[name*="[month_type]"]:checked').each(function(index, element){
			toggleMonthDaysSelect($(element).val())
		});
	});

	var toggleRecurrenceType = function(type)
	{
		if (type == 'NONE') {
			$('#recurrence-settings').hide();

			return;
		}

		$('#recurrence-settings .recurrence-type-options').hide();
		$('#recurrence-settings').show();

		if (type == 'WEEKLY') {
			$('#recurrence_repeat_weekly').show();
		}
		else if (type == 'MONTHLY') {
			$('#recurrence_repeat_monthly').show();
		}
		else if (type == 'YEARLY') {
			$('#recurrence_repeat_yearly').show();
		}
	};

	var toggleLimitType = function(type)
	{
		$('.repeat_type_option').hide();
		if (type == 'count') {
			$('#repeat_type_count').show();
		}
		else {
			$('#repeat_type_date').show();
		}
	};

	var toggleMonthDaysSelect = function(type)
	{
		$('.month-type-options').hide();
		if (type == 'bymonthday') {
			$('.month-type-options.bymonthdays').show();
		}
		else {
			$('.month-type-options.byweeks').show();
		}
	};
})(jQuery);
