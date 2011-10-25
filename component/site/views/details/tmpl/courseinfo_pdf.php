<?php
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

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<table width="100px">
<thead>
	<tr>
		<th><?php echo JText::_('COM_REDEVENT_EVENT_NAME'); ?></th>
		<th><?php echo JText::_('COM_REDEVENT_EVENT_WHERE'); ?></th> 
		<th><?php echo JText::_('COM_REDEVENT_EVENT_DATE'); ?>&nbsp;<?php echo JText::_('COM_REDEVENT_EVENT_VENUE'); ?></th>
		<th><?php echo JText::_('COM_REDEVENT_EVENT_PRICE'); ?></th>
	</tr>
</thead>
<tbody>
<?php
foreach ($this->_eventlinks as $key => $event) {
	$event_url = JURI::current().JRoute::_('index.php?option=com_redevent&view=details&xref='.$event->xref);
	$venue_url = JURI::current().JRoute::_('index.php?option=com_redevent&view=venueevents&id='.$event->venueid);
	?>
	<tr>
		<td><?php echo JHTML::_('link', $event_url, $event->full_title); ?></td>
		<td><?php echo $event->location; ?></td>
		<td><?php echo ELOutput::formatdate($event->dates, $event->times); ?> 
		<?php echo redEVENTHelper::getEventDuration($event); ?> 
		<?php echo JHTML::_('link', $venue_url, $event->venue); ?></td>
		<td class="re-price"><?php echo ELOutput::formatListPrices($event->prices); ?></td>
	</tr>
<?php }
?>
</tbody>
</table>
