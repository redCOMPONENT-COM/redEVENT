/**
 * @version 1.0 $Id$
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


function changegdMode(mode)
{
	if(document.getElementById) {
		switch (mode) {
			case 0:
				document.getElementById('gd1').style.display = 'none';
				break;
			default:
				document.getElementById('gd1').style.display = '';
		} // switch
	} // if
}

document.switcher = null;
window.addEvent('domready', function() {
 	toggler = $('submenu');
  	element = $('elconfig-document');
  	if(element) {
  		document.switcher = new JSwitcher(toggler, element, {cookieName: toggler.getAttribute('class')});
  	}
});