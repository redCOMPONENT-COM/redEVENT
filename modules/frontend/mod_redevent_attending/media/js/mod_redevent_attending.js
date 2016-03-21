/**
 * @version 1.0
 * @package Joomla
 * @subpackage redEVENT attending module
 * @copyright redEVENT (C) 2008-2011 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
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
 * js file for mod_redevent_attending
 */
window.addEvent('domready', function(){
	$$('.reattoffset').addEvent('change', function(){
		var form = this.form;
		var uri = form.currenturi.value;
		uri = uri.replace(/([?&]reattoffset=)([-]*[0-9]+)/, "$1"+this.value);
		window.location = uri;
	});
});