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
<form id="course-search-form" name="course-search-form">
	<div>
		<?php echo JHtml::_('select.genericlist', $this->events_options, 'filter_event', array('class' => 'input-medium')); ?>
	</div>
	<div>
		<?php echo JHtml::_('select.genericlist', $this->sessions_options, 'filter_session'); ?>
	</div>
	<div>
		<?php echo JHtml::_('select.genericlist', $this->venues_options, 'filter_venue'); ?>
	</div>
	<div>
		<?php echo JHtml::_('select.genericlist', $this->categories_options, 'filter_category'); ?>
	</div>
	<div>
		<?php echo JHtml::calendar($this->filter_from, 'filter_from', 'filter_from'); ?>
	</div>
	<div>
		<?php echo JHTML::calendar($this->filter_to, 'filter_to', 'filter_to'); ?>
	</div>
	<button><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_COURSE_BUTTON_SEARCH'); ?></button>

</form>