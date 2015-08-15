<?php
/**
 * @version 1.0 $Id: default.php 30 2009-05-08 10:22:21Z roland $
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

defined('_JEXEC') or die('Restricted access');
?>
<div id="redevent" class="el_eventlist">

<?php if ($this->params->def('show_page_title', 1)): ?>

	<h1 class="componentheading">
		<?php echo $this->escape($this->pagetitle); ?>
	</h1>

<?php endif; ?>

<?php if ($this->hasManagedEvents && $this->canAddXref): ?>

	<h2><?php echo JText::_('COM_REDEVENT_MYEVENTS_MANAGED_SESSIONS'); ?></h2>
	<div id="result_sessions">
	<?php echo $this->loadTemplate('sessions'); ?>
	</div>

<?php elseif (!empty($this->events)): ?>

	<h2><?php echo JText::_('COM_REDEVENT_MYEVENTS_MANAGED_EVENTS'); ?></h2>
	<div id="result_events">
		<?php echo $this->loadTemplate('events'); ?>
	</div>

<?php endif; ?>

<?php if ($this->canAddXref): ?>
	<div><?php echo JHTML::link('index.php?option=com_redevent&task=editsession.add', JText::_('COM_REDEVENT_MYEVENTS_ADD_NEW_EVENT_SESSION')); ?></div>
<?php endif; ?>

<?php if ($this->canAddEvent): ?>
	<div><?php echo JHTML::link('index.php?option=com_redevent&task=editevent.add', JText::_('COM_REDEVENT_MYEVENTS_ADD_NEW_EVENT')); ?></div>
<?php endif; ?>

<?php if (count((array) $this->venues)): ?>
	<h2><?php echo JText::_('COM_REDEVENT_MYEVENTS_MANAGED_VENUES'); ?></h2>
	<?php echo $this->loadTemplate('venues'); ?>
<?php endif; ?>

<?php if ($this->canAddVenue): ?>
	<div><?php echo JHTML::link(RedeventHelperRoute::getEditVenueRoute(), JText::_('COM_REDEVENT_MYEVENTS_ADD_NEW_VENUE')); ?></div>
<?php endif; ?>

<?php if (count((array)$this->attending)): ?>
	<h2><?php echo JText::_('COM_REDEVENT_ATTENDING'); ?></h2>
	<?php echo $this->loadTemplate('attending'); ?>
<?php endif; ?>

<?php if (count((array)$this->attended)): ?>
	<h2><?php echo JText::_('COM_REDEVENT_BOOKING_HISTORY'); ?></h2>
	<?php echo $this->loadTemplate('attended'); ?>
<?php endif; ?>

</div>
