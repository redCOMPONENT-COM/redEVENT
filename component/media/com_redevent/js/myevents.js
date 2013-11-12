/**
 * javascript for ajax navigation
 */

window.addEvent('domready', function() {

	document.id('redevent').addEvent('change:relay(#el_filter input)', function(e) {
		e.stop();
		red_ajaxnav.submitForm(this.getParent('form'));
	});

	document.id('redevent').addEvent('change:relay(#el_filter select)', function(e) {
		e.stop();
		red_ajaxnav.submitForm(this.getParent('form'));
	});

	document.id('redevent').addEvent('click:relay(#filter-go)', function(e) {
		e.stop();
		red_ajaxnav.submitForm(this.getParent('form'));
	});

	document.id('redevent').addEvent('click:relay(#filter-reset)', function(e) {
		e.stop();
		$$('#el_filter select').set('value', '0');
		$$('#el_filter input').set('value', '');
		red_ajaxnav.submitForm(this.getParent('form'));
	});

	$$('.unreg-btn').addEvent('click', function(){
		if (confirm(Joomla.JText._("COM_REDEVENT_MYEVENTS_CANCEL_REGISTRATION_WARNING"))) {
			var id = this.id.substr(6);
			var xref = this.getProperty('xref');
			var element = this;

			element.set('spinner').spin();

			var req = new Request.JSON({
				url : 'index.php?option=com_redevent&task=ajaxcancelregistration&tmpl=component',
				data : {'rid': id, 'xref': xref},
				method : 'post',
				onSuccess : function(resp){
					element.set('spinner').unspin();
					if (resp.status == 1) {
						element.getParent('tr').dispose();
					}
					else {
						alert(resp.error);
					}
				}
			});
			req.send();
		};
	});
});

