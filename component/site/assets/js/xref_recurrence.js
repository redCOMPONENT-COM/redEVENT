/**
 * @version 1.0 $Id: settings.js 30 2009-05-08 10:22:21Z roland $
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

  $$('input[name="recurrence_type"]').each(function(el) {
	  el.addEvent('change', toggleRtype.bind(el));
  });

  $('recurrence_repeat_until').addEvent('click', function(){
	  $('rcount').removeProperty('checked');
	  $('runtil').setProperty('checked', 'checked');
  });

  if ($('recurrence_repeat_until_img')) {
	  $('recurrence_repeat_until_img').addEvent('click', function(){
		  $('rcount').removeProperty('checked');
		  $('runtil').setProperty('checked', 'checked');
	  });
  }
  
  $('recurrence_repeat_count').addEvent('click', function(){
	  $('runtil').removeProperty('checked');
	  $('rcount').setProperty('checked', 'checked');
  });

  if ($('repeat').value > 0) {
	  $('recurrence').getElements('input').each(function(el){
		  el.setProperty('disabled', 'disabled');
	  });
  }
  
  toggleRtype();
  
});

function toggleRtype()
{
	var elements = $$('input[name=recurrence_type]');
	hideall();
	var selected = getRadioCheckedValue(elements);
	
	if (selected == 'DAILY') {
		$('xref_recurrence_repeat_common').setStyle('display', 'block');
	}
	if (selected == 'WEEKLY') {
		$('xref_recurrence_repeat_common').setStyle('display', 'block');
		$('recurrence_repeat_weekly').setStyle('display', 'block');
	}
	if (selected == 'MONTHLY') {
		$('xref_recurrence_repeat_common').setStyle('display', 'block');
		$('recurrence_repeat_monthly').setStyle('display', 'block');
	}
	if (selected == 'YEARLY') {
		$('xref_recurrence_repeat_common').setStyle('display', 'block');
		$('recurrence_repeat_yearly').setStyle('display', 'block');
	}
}

function getRadioCheckedValue(elements) 
{
	for(var i = 0; i < elements.length; i++) {

		if(elements[i].checked) {
		return elements[i].value;
		}
	}
	return '';
} 

function hideall() {
	$('xref_recurrence_repeat_common').setStyle('display', 'none');
	$('recurrence_repeat_weekly').setStyle('display', 'none');
	$('recurrence_repeat_monthly').setStyle('display', 'none');
	$('recurrence_repeat_yearly').setStyle('display', 'none');
}