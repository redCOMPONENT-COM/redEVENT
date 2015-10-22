/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2015 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */

var updateRevenuelist = (function($){

	var update = function(field, venueid, name) {
		var $select = $('#' + field);
		var parentDiv = $select.parents("div.controls");

		var hiddenInput = $('<input type="hidden" id="' + $select.prop('id') + '" name="' + $select.prop('name') + '" value="' + venueid + '"/>');

		$("#" + field + "-modal").modal('toggle');

		parentDiv.empty();
		parentDiv.append(hiddenInput);
		parentDiv.append(name);
	};

	return update;
})(jQuery);
