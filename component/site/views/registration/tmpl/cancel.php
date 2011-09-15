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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div id="confirmation_message">
	<?php echo JText::sprintf('COM_REDEVENT_REGISTRATION_CONFIRM_CANCEL_TXT', $this->course->full_title, $this->course->venue, $this->course->dateinfo); ?>
</div>
<form name="cancelreg" id="cancelreg" method="post" action="<?php echo $this->action; ?>">
<button type="submit" id="submitbt"><?php echo JText::_('COM_REDEVENT_BUTTON_LABEL_CONFIRM'); ?></button>
<input name="task" type="hidden" value="delreguser" />
</form>
<div><?php echo JHTML::_('link', JRoute::_(RedEventHelperRoute::getDetailsRoute($this->course->slug, JRequest::getInt('xref'))), JText::_('RETURN_EVENT_DETAILS'), array('class' => 're-back')); ?></div>