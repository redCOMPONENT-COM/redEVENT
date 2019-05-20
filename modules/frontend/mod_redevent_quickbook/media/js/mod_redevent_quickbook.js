/**
 * redevent quickbook module javascript
 */
var modRedeventQuickbook = (function($) {
	return function($form, prices, dummy) {
		var priceElement = null;

		$form.find('[name=xref]').change(function() {
			var xref = $(this).val();
			var options = [];

			for (var i = 0; i < prices.length; i++) {
				if (prices[i].xref == xref) {
					options.push($('<option>').val(prices[i].id).attr('price', prices[i].price).text(prices[i].name));
				}
			}

			if (priceElement)
			{
				priceElement.remove();
				priceElement = null;
			}

			if (options.length) {
				if (options.length > 1)
				{
					priceElement = $('<select>')
						.attr('name', 'sessionprice_1');

					for (var i = 0; i < options.length; i++) {
						priceElement.append(options[i]);
					}
				}
				else {
					priceElement = $('<input>')
						.attr('name', 'sessionprice_1')
						.type('name', 'hidden')
						.val(options[0].value);
				}

				$(this).after(priceElement);
			}
		}).change();
	};
})(jQuery);