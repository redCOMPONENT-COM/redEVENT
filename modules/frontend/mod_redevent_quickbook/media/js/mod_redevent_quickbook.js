/**
 * redevent quickbook module javascript
 */
(function($) {
	$(function() {
		$('#xref').change(function() {
			var xref = $(this).val();
			var options = [];

			for (var i = 0; i < prices.length; i++) {
				if (prices[i].xref == xref) {
					options.push($('<option>').val(prices[i].id).attr('price', prices[i].price).text(prices[i].name));
				}
			}

			if (options.length) {
				if ($('#sessionpricegroup_id'))
				{
					$('#sessionpricegroup_id').remove();
				}

				if (options.length > 1)
				{
					var sel = $('<select>').attr('id', 'sessionpricegroup_id')
						.attr('name', 'sessionpricegroup_id[]');

					for (var i = 0; i < options.length; i++) {
						sel.append(options[i]);
					}
				}
				else {

					var sel = $('<input>').attr('id', 'sessionpricegroup_id')
						.attr('name', 'sessionpricegroup_id[]')
						.type('name', 'hidden')
						.val(options[0].value);
				}

				$(this).after(sel);
			}
		}).change();
	});
})(jQuery);