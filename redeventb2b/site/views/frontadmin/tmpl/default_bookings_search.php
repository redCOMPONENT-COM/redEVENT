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
<h2 id="bookings-search-title"><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_BOOKINGS_SEARCH_TITLE')?></h2>
<form id="bookings-search-form" name="bookings-search-form" action="index.php?option=com_redevent&controller=frontadmin&task=searchbookings" method="post">
	<div class="styled-select-admin">
		<?php echo JHtml::_('select.genericlist', $this->categories_options, 'filter_category'
			, array('class' => 'input-medium')
			, 'value', 'text', $this->state->get('filter_category'), 'bookings_filter_category'); ?>
	</div>
	<div class="styled-select-admin">
		<?php echo JHtml::_('select.genericlist', $this->venues_options, 'filter_venue'
			, array('class' => 'input-medium')
			, 'value', 'text', $this->state->get('filter_venue'), 'bookings_filter_venue'); ?>
	</div>
	<div class="styled-select-admin">
		<?php echo JHtml::_('select.genericlist', $this->events_options, 'filter_event'
			, array('class' => 'input-medium')
			, 'value', 'text', $this->state->get('filter_event'), 'bookings_filter_event'); ?>
	</div>

	<div class="styled-select-admin">
		<?php echo JHtml::_('select.genericlist',
			array(
				JHtml::_('select.option', 1, JText::_('COM_REDEVENT_FRONTEND_ADMIN_ACTIVE_COURSES')),
				JHtml::_('select.option', -1, JText::_('COM_REDEVENT_FRONTEND_ADMIN_COURSES_HISTORY'))
			), 'filter_bookings_state'
			, array('class' => 'input-medium')
			, 'value', 'text', $this->state->get('filter_bookings_state')); ?>
	</div>

	<div class="date-filter-label"><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_COURSE_SEARCH_DATE_FILTER'); ?></div>
	<div>
		<?php echo JHtml::calendar($this->bookings_filter_from, 'filter_from', 'bookings_filter_from', '%Y-%m-%d',
			array('class' => 'input-small', 'placeholder' => JText::_('COM_REDEVENT_FROM'))); ?>
	</div>
	<div>
		<?php echo JHTML::calendar($this->bookings_filter_from, 'filter_to', 'bookings_filter_to', '%Y-%m-%d',
			array('class' => 'input-small', 'placeholder' => JText::_('COM_REDEVENT_TO'))); ?>
	</div>

	<input type="hidden" name="filter_order" value="<?php echo $this->order; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->order_Dir; ?>"/>
	<input type="hidden" name="limitstart" value="<?php echo $this->limitstart; ?>"/>
	<input type="hidden" name="limit" value="<?php echo $this->state->get('limit'); ?>"/>

	<button type="button" id="search-bookings" class="btn"><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_COURSE_BUTTON_SEARCH'); ?></button>
	<button type="button" id="search-bookings-reset" class="btn"><?php echo JText::_('COM_REDEVENT_RESET'); ?></button>

</form>
