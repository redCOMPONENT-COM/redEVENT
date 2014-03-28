/**
 * @version    2.5
 * @package    redEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL
 */

/**
 * this file manages updating redform form price when selecting session price
 */

window.addEvent('domready', function() {

	$$('input.updateCurrency').addEvent('click', function(){
		document.id(this.form).getElement('[name=currency]').set('value', this.getProperty('currency'));
	});

	$$('select.updateCurrency').addEvent('change', function(){
		document.id(this.form).getElement('[name=currency]').set('value', this.getProperty('currency'));
	});
});
