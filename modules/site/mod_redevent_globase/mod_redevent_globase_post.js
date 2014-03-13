/**
 * redevent globase module javascript
 */

document.addEvent('domready', function(){
	$$('.globasesubmit-btn').addEvent('click', function() {
		var form = this.getParent('form');
		if (CheckSubmit(form)) {
			form.submit();
		}
	});
});
