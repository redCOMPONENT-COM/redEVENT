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
<div id="bookings-header">
	<h2><?php echo JText::sprintf('COM_REDEVENT_FRONTEND_ORGANIZATION_S_BOOKINGS', $this->organization); ?></h2>

	<ul class="inline bookings-filter">
		<li><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_SEARCH_IN'); ?></li>
		<li><label class="checkbox"><input name="filter_person_active" id="filter_person_active0" type="checkbox" value="1"
			<?php echo $this->state->get('filter_person_active') ? ' checked="checked"' : ''; ?>/> <?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_ACTIVE_COURSES'); ?></label></li>
		<li><label class="checkbox"><input name="filter_person_active" id="filter_person_active1" type="checkbox" value="-1"
			<?php echo $this->state->get('filter_person_archive') ? ' checked="checked"' : ''; ?> /> <?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_COURSES_HISTORY'); ?></label></li>
	</ul>
</div>


	<table class="table">
		<thead>
			<tr>
				<th><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_SELECT_SESSION'); ?></th>
				<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_DATE'), 'x.dates', $this->bookings_order_dir, $this->bookings_order); ?></th>
				<th><?php echo JText::_('COM_REDEVENT_TIME'); ?></th>
				<th><?php echo JText::_('COM_REDEVENT_EVENT_DURATION'); ?></th>
				<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_TITLE'), 'a.title', $this->bookings_order_dir, $this->bookings_order); ?></th>
				<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_VENUE'), 'l.venue', $this->bookings_order_dir, $this->bookings_order); ?></th>
				<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_CATEGORY'), 'c.catname', $this->bookings_order_dir, $this->bookings_order); ?></th>
				<th><?php echo JText::_('COM_REDEVENT_B2B_SEATS'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if ($this->bookings): ?>
			<?php foreach ($this->bookings as $row): ?>
				<tr>
					<td><input type="radio" name="select-session" value="<?php echo $row->xref; ?>" class="select-session-radio"/></td>
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
					<td><?php echo $this->bookbutton($row->xref); ?><?php echo $this->printPlaces($row, false); ?></td>
				</tr>
			<?php endforeach;?>
		<?php endif; ?>
		</tbody>
	</table>

	<!--pagination-->
	<?php if (($this->bookings_pagination->get('pages.total') > 1)) : ?>
	<div class="pagination">
		<?php echo $this->bookings_pagination->getPagesLinks(); ?>
	</div>
	<?php  endif; ?>
	<!-- pagination end -->
