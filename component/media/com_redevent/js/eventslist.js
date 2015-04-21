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

	$$('.dynfilter').addEvent('change', function() {
		redhint.removehint();
		this.form.submit();
		return true;
	});


	// show/hide filters in views
	if ($('el-events-filters'))
	{
		if ($('f-showfilters') && $('f-showfilters').value > 0) {
			$('el-events-filters').setStyle('display', 'block');
		}
		else {
			$('el-events-filters').setStyle('display', 'none');
		}
		if ($('filters-toggle'))
		{
			$('filters-toggle').addEvent('click', function(){
				if ($('el-events-filters').getStyle('display') == 'none')
				{
					$('el-events-filters').setStyle('display', 'block');
					$('f-showfilters').value = 1;
				}
				else
				{
					$('el-events-filters').setStyle('display', 'none');
					$('f-showfilters').value = 0;
				}
			});
		}
	}

	if ($('filters-reset'))
	{
		$('filters-reset').addEvent('click', function(){
			$('el-events-filters').getElements('input').each(function(el){
				el.value = '';
			});
			$('el-events-filters').getElements('select').each(function(el){
				el.value = '';
			});
			redhint.removehint();
			this.form.submit();
			return true;
		});
	}
});

function tableOrdering( order, dir, view )
{
	var form = document.getElementById("adminForm");

	// remove the hint from the filter
	redhint.removehint();

	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	form.submit( view );
}

