/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2008-2015 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */

var attendeesMove = (function($){

	var form;

	var selectDestination = function(formDom, redformId) {
		form = formDom;
		var url = 'index.php?option=com_redevent&view=sessions&layout=element&tmpl=component&form_id=' + redformId + '&function=attendeesMove.selectXref';

		SqueezeBox.open(url, {handler: 'iframe', size: {x: 800, y: 400}});
	};

	var selectXref = function(sessionId, sessionTitle) {
		var input = $('<input type="hidden" name="dest" value="' + sessionId + '"/>');
		$(form).append(input);
		form.task.value = 'attendees.move';
		form.submit();
	}

	return {
		selectDestination : selectDestination,
		selectXref : selectXref
	};
})(jQuery);
