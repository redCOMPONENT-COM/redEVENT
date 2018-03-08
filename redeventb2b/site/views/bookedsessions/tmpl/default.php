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
<div id="bookings-header" class="panel-heading">
	<h2 class="panel-title">
		<?php echo JText::sprintf('COM_REDEVENT_FRONTEND_ORGANIZATION_S_BOOKINGS', $this->organization); ?>
	</h2>
</div>

<div id="bookings-result">
	<table class="table">
		<thead>
		<tr>
			<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_DATE'), 'x.dates', $this->bookings_order_dir, $this->bookings_order); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_TIME'); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_EVENT_DURATION'); ?></th>
			<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TITLE'), 'a.title', $this->bookings_order_dir, $this->bookings_order); ?></th>
			<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_VENUE'), 'l.venue', $this->bookings_order_dir, $this->bookings_order); ?></th>
			<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_CATEGORY'), 'c.name', $this->bookings_order_dir, $this->bookings_order); ?></th>
			<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_LANGUAGE'), 'x.session_language', $this->bookings_order_dir, $this->bookings_order); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_B2B_SEATS'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php if ($this->bookings): ?>
			<?php foreach ($this->bookings as $row): ?>
				<tr>
					<td><?php echo RedeventHelperOutput::formatdate($row->dates, false); ?></td>
					<td><?php echo RedeventHelperOutput::formattime($row->dates, $row->times); ?></td>
					<td><?php echo RedeventHelper::getEventDuration($row); ?></td>
					<td><?php echo $row->title; ?></td>
					<td><?php echo $row->venue; ?></td>
					<td class="re_category">
						<?php $cats = array();
						foreach ($row->categories as $cat)
						{
							if ($this->params->get('catlinklist', 1) == 1)
							{
								$cats[] = JHTML::link(RedeventHelperRoute::getCategoryEventsRoute($cat->slug), $cat->name);
							}
							else
							{
								$cats[] = $this->escape($cat->name);
							}
						}
						echo implode("<br/>", $cats);
						?>
					</td>
					<td><?php echo RedeventHelperLanguages::getFormattedIso1($row->session_language); ?></td>
					<td>
						<?php if (!$this->isFull($row)): ?>
							<?php echo $this->manageBookingButton($row->xref); ?><?php echo $this->printPlaces($row, false); ?>
						<?php else: ?>
							<?php echo $this->printInfoIcon($row); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach;?>
		<?php endif; ?>
		</tbody>
	</table>

	<!--pagination-->
	<div class="pagination">
		<div class="pagination-label"><?php echo JText::_('COM_REDEVENT_FRONTADMIN_PAGINATION_SELECT_LIMIT'); ?></div>
		<div class="styled-select-admin">
			<?php echo $this->getLimitBox(); ?>
		</div>
		<?php if (($this->bookings_pagination->get('pages.total') > 1)) : ?>
			<?php echo $this->bookings_pagination->getPagesLinks(); ?>
		<?php  endif; ?>
	</div>
	<!-- pagination end -->
</div>
