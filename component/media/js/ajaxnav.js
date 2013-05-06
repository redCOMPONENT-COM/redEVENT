/**
 * javascript for ajax navigation
 */

window.addEvent('domready', function() {	
	
	$$('.itemnav').addEvent('click', red_ajaxnav.navigate);
	
	$$('.ajaxsortcolumn').addEvent('click', red_ajaxnav.sortcolumn);
});

var red_ajaxnav = {
	navigate : function(e) {
		e.stop();
		var form = this.getParent('form');
		
		if (!form.limitstart) {
			new Element('input', {'name' : 'limitstart', 'value': '', 'type' : 'hidden'}).inject(form);
		}
		form.limitstart.value = this.getProperty('startvalue');
				
		red_ajaxnav.submitForm(form);
	},
	
	sortcolumn : function(e) {
		e.stop();
		var form = this.getParent('form');
		form.getElement('.redajax_order').set('value', this.getProperty('ordercol'));
		form.getElement('.redajax_order_dir').set('value', this.getProperty('orderdir'));
		
		red_ajaxnav.submitForm(form);	
	},
	
	submitForm : function(form)
	{
		if (!form.format) {
			new Element('input', {'name' : 'format', 'value': 'raw', 'type' : 'hidden'}).inject(form);
		}
		var req = new Request({
			url: form.action,
			data: form,
			method: 'post',
			onRequest : function(){
				form.set('spinner').spin();
			},
			onSuccess : function(response) {
				form.unspin();
				var newdiv = new Element('div').set('html', response).replaces(form);
				newdiv.getElements('.itemnav').addEvent('click', red_ajaxnav.navigate);	
				newdiv.getElements('.ajaxsortcolumn').addEvent('click', red_ajaxnav.sortcolumn);			
			}
		});
		req.send();		
	}
};
