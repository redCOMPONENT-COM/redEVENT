/**
 * redevent globase module javascript
 */

document.addEvent('domready', function(){

	$$('.globasesubmit-btn').addEvent('click', function(){
		var el = this;

		if (!CheckSubmit(el.getParent('form')))
		{
			return false;
		}

		var req = new Request({
			url: el.getParent('form').getProperty('action'),
			data: el.getParent('form'),
			onRequest : function(){
				el.getParent('form').set('spinner').spin();
				el.removeEvents('click');
			},
			onSuccess : function(response) {
				el.getParent('form').unspin();
				var resp = new Element('div').set('html', response);
				SqueezeBox.initialize();
				SqueezeBox.open(resp, {
					handler: 'adopt',
					size: {x: 300, y: 450}
				});
			}
		});
		req.send();
	});
});
