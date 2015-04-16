/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */
(function($){
	$(document).ready(function() {
		$('#add-price').click(function(){
			var group = $('#trnewprice').find('select.price-group');
			var price = $('#trnewprice').find('input.price-val');
			var currency = $('#trnewprice').find('select.price-currency');

			if (group.val() == 0) {
				return true;
			}

			// Value ok, add new row
			var newrow = $('#trnewprice').clone(false).removeAttr('id');
			newrow.find('.price-group').removeAttr('id').val(group.val());
			newrow.find('.price-val').removeAttr('id').val(price.val());
			newrow.find('.price-currency').removeAttr('id').val(currency.val());

			newrow.find('button').removeAttr('name').addClass('remove-price').text(Joomla.JText._("COM_REDEVENT_REMOVE"));
			$('#trnewprice').before(newrow);

			// Reset values
			group.val(0);
			price.val(0);

			$("#re-prices").trigger("chosen:updated");
		});

		$('#re-prices').on('click' , 'button.remove-price', function(){
			$(this).parents('tr').remove();
		});
	});
})(jQuery);
