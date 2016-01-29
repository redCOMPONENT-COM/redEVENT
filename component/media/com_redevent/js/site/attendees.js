/**
 * This scripts handles frontend attendees view
 */
window.addEvent('domready', function(){
	$$('.unreglink').addEvent('click', function(event){
		if (confirm(Joomla.JText._('COM_REDEVENT_CONFIRM_CANCEL_REGISTRATION'))) {
			return true;
		}
		else {
			if (event.preventDefault) {
				event.preventDefault();
			} else {
				event.returnValue = false;
			}
			return false;
		}
	});
});
