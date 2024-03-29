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
<div id="main-content" class="row-fluid">
	<div id="main-results" class="span9 panel-group">

		<div id="main-attendees" class="panel panel-default">
			<?php echo $this->loadTemplate('attendees'); ?>
		</div>

		<div id="main-bookings" class="panel panel-default">
			<?php echo $this->loadTemplate('bookings_search_results'); ?>
		</div>

		<div id="main-course-results" class="panel panel-default">
			<?php echo $this->loadTemplate('search_results'); ?>
		</div>
	</div>

	<div id="main-right" class="span3">
		<?php echo $this->loadTemplate('course_search'); ?>

		<?php echo $this->loadTemplate('selected_users'); ?>

		<hr/>
		<div>
		<?php if ($this->useracl->canAddEvent()): ?>
			<?php echo JHtml::link(RedeventHelperRoute::getEditEventRoute().'&tmpl=component',
				Jtext::_('COM_REDEVENT_FRONTEND_ADMIN_COURSE_BUTTON_ADD_EVENT'),
				array('class' => 'btn xrefmodal')); ?>
		<?php endif; ?>
		</div>

		<div>
		<?php if ($this->useracl->canAddSession()): ?>
			<?php echo JHtml::link(RedeventHelperRoute::getAddSessionTaskRoute().'&tmpl=component',
				Jtext::_('COM_REDEVENT_FRONTEND_ADMIN_COURSE_BUTTON_ADD_SESSION'),
				array('class' => 'btn xrefmodal')); ?>
		<?php endif; ?>
		</div>
	</div>
</div>
<div class="clear"></div>
