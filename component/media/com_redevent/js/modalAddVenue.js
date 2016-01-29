/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2015 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */

var updateRevenuelist = (function($){

	var update = function(field, venueid, name) {
		var $select = $('#' + field);
		$select.append($('<option value="' + venueid + '">' + name + '</option>'));
		$select.val(venueid).trigger('change');
	};

	return update;
})(jQuery);
