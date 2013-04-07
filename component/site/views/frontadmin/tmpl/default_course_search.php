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
<h3 id="session-form-title"><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_COURSE_SEARCH_TITLE')?></h3>
<form id="course-search-form" name="course-search-form" action="index.php?option=com_redevent&controller=frontadmin&task=searchsessions" method="post">
	<div>
		<?php echo JHtml::_('select.genericlist', $this->events_options, 'filter_event'
			, array('class' => 'input-medium')
			, 'value', 'text', $this->state->get('filter_event')); ?>
	</div>
	<div>
		<?php echo JHtml::_('select.genericlist', $this->sessions_options, 'filter_session'
			, array('class' => 'input-medium')
			, 'value', 'text', $this->state->get('filter_session')); ?>
	</div>
	<div>
		<?php echo JHtml::_('select.genericlist', $this->venues_options, 'filter_venue'
			, array('class' => 'input-medium')
			, 'value', 'text', $this->state->get('filter_venue')); ?>
	</div>
	<div>
		<?php echo JHtml::_('select.genericlist', $this->categories_options, 'filter_category'
			, array('class' => 'input-medium')
			, 'value', 'text', $this->state->get('filter_category')); ?>
	</div>
	<div>
		<?php echo JHtml::calendar($this->filter_from, 'filter_from', 'filter_from', '%Y-%m-%d', array('class' => 'input-small')); ?>
	</div>
	<div>
		<?php echo JHTML::calendar($this->filter_to, 'filter_to', 'filter_to', '%Y-%m-%d', array('class' => 'input-small')); ?>
	</div>
	<button type="submit" id="search-course" class="btn"><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_COURSE_BUTTON_SEARCH'); ?></button>
	<button type="button" id="search-course-reset" class="btn"><?php echo JText::_('COM_REDEVENT_RESET'); ?></button>

</form>