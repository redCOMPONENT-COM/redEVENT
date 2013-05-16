/**
 * @version 2.5
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

window.addEvent('domready', function() {
	if ($('add-price'))
	{
		$('add-price').addEvent('click', function(){
			var sel = $('trnewprice').getElement('select');
			if (! (sel.value > 0)) {
				return true;
			}
			var price = $('trnewprice').getElement('.price-val');
			// value ok, add new row
			var newrow = $('trnewprice').clone().removeProperty('id');
			newrow.getElement('select').removeProperty('id').value = sel.value;
			newrow.getElement('.price-val').removeProperty('id').value = price.value;
			newrow.getElement('button').removeProperty('name').set('text', txt_remove).addEvent('click', removePrice);
			newrow.injectBefore($('trnewprice'));
			sel.value = 0;
			price.value = 0;
		});

		$$('button.remove-price').addEvent('click', removePrice);
	}
});

function removePrice()
{
	this.getParent().getParent().dispose();
}