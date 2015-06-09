/**
 * @version    3.0
 * @package    reEVENT
 * @copyright  redEVENT (C) 2015 redCOMPONENT.com
 */

/**
 * Script for redMEMBEr date field override
 */
window.addEvent('domready', function() {
	(function(){
		document.addEvent('change:relay(.rm_3boxdaypick)', function(){
			var topdiv = this.getParent('div');
			var input = topdiv.getElement('input');

			var date = topdiv.getElement('.day_select').get('value')
				+ '-' + topdiv.getElement('.month_select').get('value')
				+ '-' + topdiv.getElement('.year_select').get('value');

			input.set('value', date);
		});
	})();
});
