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

defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.formvalidation');
JHTML::_('behavior.tooltip');
$app = JFactory::getApplication();
?>
<h2><?php echo $this->session->title. '@'. $this->session->venue. ' ' . (RedeventHelper::isValidDate($this->session->dates) ? strftime($this->settings->get('formatdate', '%d.%m.%Y'), strtotime($this->session->dates)) : ''); ?></h2>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal" >

	<div class="row-fluid">
		<div class="control-group">
			<div class="control-label">
				<label>
					<?php echo JText::_( 'COM_REDEVENT_EMAIL_ATTENDEES_TO' ).':'; ?>
				</label>
			</div>
			<div class="controls">
				<?php foreach ($this->emails as $email): ?>
				<?php echo (isset($email['fullname']) ? $email['fullname']. ' ' : '').htmlspecialchars('<').$email['email'].htmlspecialchars('>').'<br/>'; ?>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label for="from">
					<?php echo JText::_( 'COM_REDEVENT_EMAIL_ATTENDEES_FROM' ).':'; ?>
				</label>
			</div>
			<div class="controls">
					<input name="from" id="from" value="<?php echo $app->getCfg('mailfrom'); ?>" class="validate-email required" size="50" maxlength="100" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label for="fromname">
					<?php echo JText::_( 'COM_REDEVENT_EMAIL_ATTENDEES_FROMNAME' ).':'; ?>
				</label>
			</div>
			<div class="controls">
				<input name="fromname" id="fromname" value="<?php echo $app->getCfg('sitename'); ?>" size="50" maxlength="100" class="required"/>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label for="subject" class="hasTooltip" title="<?php echo JText::_( 'COM_REDEVENT_EMAIL_ATTENDEES_SUBJECT' ).'::'.JText::_( 'COM_REDEVENT_EMAIL_ATTENDEES_SUBJECT_DESC' ); ?>">
					<?php echo JText::_( 'COM_REDEVENT_EMAIL_ATTENDEES_SUBJECT' ).':'; ?>
				</label>
			</div>
			<div class="controls">
				<input name="subject" id="subject" value="" class="required" size="50" maxlength="100" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label for="body">
					<?php echo JText::_( 'COM_REDEVENT_EMAIL_ATTENDEES_BODY' ).':'; ?>
				</label>
			</div>
			<div class="controls">
				<?php echo $this->editor->display('body', '', '100%', '350', '75', '20'); ?>
			</div>
		</div>
	</div>

	<?php echo JHTML::_('form.token'); ?>
	<?php foreach ($this->state->get('cids') as $cid) :?>
	<input type="hidden" name="cid[]" value="<?php echo $cid; ?>"/>
	<?php endforeach; ?>
	<input type="hidden" name="option" value="com_redevent" />
	<input type="hidden" name="filter[session]" value="<?php echo $this->state->get('sessionId'); ?>" />
	<input type="hidden" name="filter[confirmed]" value="<?php echo $this->state->get('confirmed'); ?>" />
	<input type="hidden" name="filter[cancelled]" value="<?php echo $this->state->get('cancelled'); ?>" />
	<input type="hidden" name="filter[waiting]" value="<?php echo $this->state->get('waiting'); ?>" />
	<input type="hidden" name="view" value="emailattendees" />
	<input type="hidden" name="task" value="" />
</form>
