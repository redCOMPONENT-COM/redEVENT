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
<?php if ($this->sessions): ?>
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
				<th colspan="3"><?php echo JText::_('COM_REDEVENT_ACTIONS'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->sessions as $row): ?>
				<tr>
					<td><?php echo REOutput::formatEventDateTime($row, false); ?></td>
					<td><?php echo redEVENTHelper::getEventDuration($row); ?></td>
					<td><?php echo $row->full_title; ?></td>
					<td><?php echo $row->venue; ?></td>
					<td><?php echo $row->city; ?></td>
					<td class="re_category">
						<?php $cats = array();
						foreach ($row->categories as $cat)
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
					<td><?php echo $row->language_sef; ?></td>
					<td><?php echo $this->bookbutton($row->xref); ?><?php echo $this->printPlaces($row); ?></td>
					<td><?php echo RedeventViewMyevents::eventeditbutton($row->slug, $row->xref); ?></td>
					<td>
					<?php if ($row->published == '1'): ?>
						<?php if ($this->useracl->canEditXref($row->xref)): ?>
							<?php echo JHTML::link('index.php?option=com_redevent&task=unpublishxref&xref='. $row->xref, JHTML::_('image', 'components/com_redevent/assets/images/ok.png', JText::_('COM_REDEVENT_Published' ))); ?>
						<?php else: ?>
							<?php echo JHTML::_('image', 'components/com_redevent/assets/images/ok.png', JText::_('COM_REDEVENT_Published' )); ?>
						<?php endif; ?>
					<?php elseif ($row->published == '0'):?>
						<?php if ($this->useracl->canEditXref($row->xref)): ?>
							<?php echo JHTML::link('index.php?option=com_redevent&task=publishxref&xref='. $row->xref, JHTML::_('image', 'components/com_redevent/assets/images/no.png', JText::_('COM_REDEVENT_Unpublished' ))); ?>
						<?php else: ?>
							<?php echo JHTML::_('image', 'components/com_redevent/assets/images/no.png', JText::_('COM_REDEVENT_Unpublished' )); ?>
						<?php endif; ?>
					<?php endif;?>
					</td>
					<td>
						<?php if ($this->useracl->canEditXref($row->xref)): ?>
							<?php echo RedeventViewMyevents::xrefdeletebutton($row->xref); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>
<?php endif; ?>