/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2015 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */
(function($){
	$(document).ready(function(){
		$('#mod-redevent-searchword').autocomplete({
			serviceUrl: 'index.php?option=com_redevent&task=ajax.eventsuggestions&format=json',
			paramName: 'filter',
			minChars: 1,
			maxHeight: 400,
			width: 300,
			zIndex: 9999,
			deferRequestBy: 500
		});
	});
})(jQuery);
