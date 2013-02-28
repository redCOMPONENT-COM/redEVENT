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
	if ($('add-role'))
	{
		$('add-role').addEvent('click', function(){
			var sel = $('trnewrole').getElements('select');
			var rrole = sel[0];
			var urole = sel[1];
			if (! (rrole.value > 0 && urole.value > 0)) {
				return true;
			}
			// value ok, add new row
			var newrow = $('trnewrole').clone().removeProperty('id');
			newrow.getElement('select.rrole').removeProperty('id').value = rrole.value;
			newrow.getElement('select[name^=urole]').removeProperty('id').value = urole.value;
			newrow.getElement('button').removeProperty('name').set('text', txt_remove).addEvent('click', removeRole);
			newrow.injectBefore($('trnewrole'));
			rrole.value = 0;
			urole.value = 0;
		});

		$$('button.remove-role').addEvent('click', removeRole);
	}
});

function removeRole()
{
	this.getParent().getParent().dispose();
}