/**
 * redevent quickbook module javascript
 */

document.addEvent('domready', function(){
	document.id('xref').addEvent('change', function() {
		var xref = this.get('value');
		var options = new Array();

		for (var i = 0; i < prices.length; i++) {
			if (prices[i].xref == xref) {
				options.push(new Element('option', {'value': prices[i].id, 'price': prices[i].price}).appendText(prices[i].name));
			}
		}

		if (options.length) {
			if (document.id('sessionpricegroup_id'))
			{
				document.id('sessionpricegroup_id').dispose();
			}

			if (options.length > 1)
			{
				var sel = new Element('select', {
					'id': 'sessionpricegroup_id',
					'name': 'sessionpricegroup_id[]'
				});

				for (var i = 0; i < options.length; i++) {
					sel.adopt(options[i]);
				}
			}
			else {
				var sel = new Element('input', {
					'id': 'sessionpricegroup_id',
					'name': 'sessionpricegroup_id[]',
					'type': 'hidden',
					'value': options[0].value
				})
			}

			sel.inject(this, 'after');
		}
	}).fireEvent('change');
});
