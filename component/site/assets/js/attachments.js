/**
 * @version 1.0 $Id: recurrence.js 30 2009-05-08 10:22:21Z roland $
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

/**
 * this file manages the js script for adding/removing attachements in event
 */
window.addEvent('domready', function() {	
	
	$$('.attach-field').addEvent('change', addattach);
	
	$$('.attach-remove').addEvent('click', function(event){
		event = new Event(event); // for IE !
		if (removemsg) {
			if (!confirm(removemsg)) {
				return false;
			}
		}
		id = event.target.id.substr(13);
		var url = 'index.php?option=com_redevent&task=ajaxattachremove&format=raw&id='+id;
		var theAjax = new Ajax(url, {
			method: 'post',
			postBody : ''
			});
		
		theAjax.addEvent('onSuccess', function(response) {
			if (response == "1") {
				$(event.target).getParent().getParent().remove();
			}
			//this.venue = eval('(' + response + ')');
		}.bind(this));
		theAjax.request();
	});
});

function addattach()
{
	var tbody = $('re-attachments').getElement('tbody');
	var rows = tbody.getElements('tr');
	var row = rows[rows.length-1].clone();
	row.getElement('.attach-field').addEvent('change', addattach).value = '';
	row.injectInside(tbody);
}