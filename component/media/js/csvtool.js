/**
 * @version 2.0
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

window.addEvent('domready', function(){
	
	$('csv-export-button').addEvent('click', function(){
		var form = $('export-form');
		var format = new Element('input', {'type':'hidden', 'name':'format', 'value':'raw'});
		form.adopt(format);
		submitform('csvexport');
	});
	
	$('form_filter').addEvent('change', function(){
		if (this.value == 0)
		{
			$('export-category-row').addClass('hide-row');
			$('export-venue-row').addClass('hide-row');
			$('export-state-row').addClass('hide-row');
			$('export-event-row').addClass('hide-row');
			$('export-button-row').addClass('hide-row');
		}
		else
		{
			$('export-category-row').removeClass('hide-row');
			$('export-venue-row').removeClass('hide-row');
			$('export-state-row').removeClass('hide-row');
			$('export-event-row').removeClass('hide-row');
			$('export-button-row').removeClass('hide-row');
			
			updateEvents();
		}
	});

	$('venue_filter').addEvent('change', updateEvents);
	$('category_filter').addEvent('change', updateEvents);
	
	$('form_filter').fireEvent('change');
});

// fills the event selector with an ajax query
function updateEvents()
{
	$('events-select').empty().addClass('loading');
	
	var query = new Request( {
		url: 'index.php?option=com_redevent&controller=csvtool&task=eventoptions&format=raw',
		method: 'post',
		data : {'venue_id':$('venue_filter').value, 'category_id': $('category_filter').value}
		});
	
	query.addEvent('onSuccess', function(response) {
		var select = new Element('select', {'name':'events_filter[]', 'id':'events_filter', 'multiple':'multiple', 'size':'20'});
		var events = eval('(' + response + ')');
		for (var i = 0; i < events.length ; i++ ) {
			new Element('option', {'value':events[i].id}).appendText(events[i].title).injectInside(select);
		}
		$('events-select').adopt(select).removeClass('loading');
	});
	query.send();
}