/**
 * redevent quickbook module javascript
 */
document.addEvent('domready', function(){

	document.id('qbsubmit-btn').addEvent('click', function(){
		var el = this;
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
