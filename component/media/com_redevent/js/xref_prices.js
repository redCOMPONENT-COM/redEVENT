/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2008-2016 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */
(function($){
	$(document).ready(function() {
		$('#add-price').click(function(){
			var group = $('#trnewprice').find('select.price-group');
			var price = $('#trnewprice').find('input.price-val');
			var vatrate = $('#trnewprice').find('input.price-vatrate');
			var sku = $('#trnewprice').find('input.price-sku');
			var currency = $('#trnewprice').find('select.price-currency');

			if (group.val() == 0) {
				return true;
			}

			// Value ok, add new row
			var newrow = $('#trnewprice').clone(false).removeAttr('id');
			newrow.find('.price-group').removeAttr('id').val(group.val());
			newrow.find('.price-val').removeAttr('id').val(price.val());
			newrow.find('.price-vatrate').removeAttr('id').val(vatrate.val());
			newrow.find('.price-sku').removeAttr('id').val(sku.val());
			newrow.find('.price-currency').removeAttr('id').val(currency.val());

			newrow.find('button').removeAttr('name').addClass('remove-price').text(Joomla.JText._("COM_REDEVENT_REMOVE"));
			$('#trnewprice').before(newrow);

			// Reset values
			price.val('');
			vatrate.val('');
			sku.val('');

			$("#re-prices").trigger("chosen:updated");
		});

		$('#re-prices').on('click' , 'button.remove-price', function(){
			$(this).parents('tr').remove();
		});
	});
})(jQuery);
