/**
 * @version    3.1
 * @package    redEVENT
 * @copyright  redEVENT (C) 2008-2016 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL
 */
(function($){
	var toggleTimes = function() {
		if ($('#jform_allday0').prop('checked')) {
			$('.timefield').show();
		}
		else {
			$('.timefield').hide();
		}
	};

	$(function(){
		$('#jform_dates_v').change(function(){
			if ($(this).val()) {
				$('#jform_enddates_v').datepicker('option', 'minDate', $(this).val());
			}
		}).change();

		$('#jform_allday0, #jform_allday1').click(toggleTimes);
		toggleTimes();
	});
})(jQuery);
