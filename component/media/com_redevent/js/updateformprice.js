/**
 * @version    2.5
 * @package    redEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL
 */

/**
 * this file manages updating redform form price when selecting session price
 */

(function($) {

	$(function(){
		$('.updateCurrency').click(function() {
			// Get currency associated to this pricegroup
			var currency = $(this).attr('currency');

			// Set to form currency
			var currencyField = $(this).parents('form').find('input[name="currency"]');

			if (currencyField.val() != currency) {
				currencyField.val(currency);
				// Trigger event for redformPrice update
				$(this).change();
			}
		});
	});

})(jQuery);
