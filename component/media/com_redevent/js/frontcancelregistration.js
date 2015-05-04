/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2015 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */
window.addEvent('domready', function(){
	$$('.unreglink').addEvent('click', function(event){
		if (confirm(JText._('COM_REDEVENT_CONFIRM_CANCEL_REGISTRATION'))) {
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
