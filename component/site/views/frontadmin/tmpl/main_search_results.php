<?php
/**
 * @package    Redevent
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
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

defined('_JEXEC') or die('Restricted access');
?>
<?php if ($this->events): ?>
	<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_ALL_EVENTS'); ?></h2>

	<table class="table">
		<thead>
			<tr>
				<th><?php echo JHtml::_('grid.sort', JText::_('COM_REDEVENT_DATE'), 'x.dates', $this->order_Dir, $this->order); ?></th>
				<th><?php echo JText::_('COM_REDEVENT_EVENT_DURATION'); ?></th>
				<th><?php echo JHtml::_('grid.sort', JText::_('COM_REDEVENT_TITLE'), 'a.title', $this->order_Dir, $this->order); ?></th>
				<th><?php echo JHtml::_('grid.sort', JText::_('COM_REDEVENT_VENUE'), 'l.venue', $this->order_Dir, $this->order); ?></th>
				<th><?php echo JHtml::_('grid.sort', JText::_('COM_REDEVENT_CITY'), 'l.city', $this->order_Dir, $this->order); ?></th>
				<th><?php echo JHtml::_('grid.sort', JText::_('COM_REDEVENT_CATEGORY'), 'c.catname', $this->order_Dir, $this->order); ?></th>
				<th><?php echo JHtml::_('grid.sort', JText::_('COM_REDEVENT_LANGUAGE'), 'a.language', $this->order_Dir, $this->order); ?></th>
				<th><?php echo JText::_('COM_REDEVENT_BOOKED'); ?></th>
				<th><?php echo JText::_('COM_REDEVENT_ACTIONS'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->events as $e): ?>
				<tr>
					<td><?php echo REOutput::formatEventDateTime($e, false); ?></td>
					<td><?php echo redEVENTHelper::getEventDuration($e); ?></td>
					<td><?php echo $e->full_title; ?></td>
					<td><?php echo $e->venue; ?></td>
					<td><?php echo $e->city; ?></td>
					<td class="re_category">
						<?php $cats = array();
						foreach ($e->categories as $cat)
						{
							if ($this->params->get('catlinklist', 1) == 1)
							{
								$cats[] = JHTML::link(RedeventHelperRoute::getCategoryEventsRoute($cat->slug), $cat->catname);
							}
							else
							{
								$cats[] = $this->escape($cat->catname);
							}
						}
						echo implode("<br/>", $cats);
						?>
					</td>
					<td><?php echo redEVENTHelper::getRemainingPlaces($e); ?></td>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>
<?php endif; ?>