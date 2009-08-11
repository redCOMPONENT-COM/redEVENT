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

// requires mootools
window.addEvent('domready', function(){
	
	blurClass = 'blur';

	$input = $('filter');
	var title = $input.title;
	
	$form = $('adminForm');
	
	// only apply logic if the element has the attribute
	if (title) {
		// on blur, set value to title attr if text is blank
		$input.addEvent('blur', function(){
	        if (this.value === '') {
	            $input.setProperty('value', title).addClass(blurClass);
	          }
		}).addEvent('focus', removehint).fireEvent('blur'); // now change input to title
	}
	
	// clear the pre-defined text when form is submitted
    $form.addEvent('submit', removehint);
    
//    // for some reason, the stop doesn't seem to work...
//    $('limit').addEvent('change', function(event){
//    	alert('before'+event.name);
//    	event.stop();
//    	alert('test');
//    });
    $('limit').addEvent('click', removehint);
    window.addEvent('unload', removehint); // handles Firefox's autocomplete
});

function removehint() {
	if ($input.value === $input.title && $input.hasClass(blurClass)) {
		$input.setProperty('value','').removeClass(blurClass);
	}
}

function tableOrdering( order, dir, view )
{
	var form = document.getElementById("adminForm");

	// remove the hint from the filter
	removehint();

	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	form.submit( view );
}