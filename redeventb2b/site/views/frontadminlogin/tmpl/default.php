<?php
/**
 * @package    Redevent.site
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');
?>
<div id="b2b-login" class="akeeba-bootstrap">
	<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_B2B_LOGIN_TITLE'); ?></h2>

	<form action="<?php echo JRoute::_('index.php', true, $this->params->get('secure', 0)); ?>" method="post" id="login-form" >

		<fieldset class="userdata">
			<p id="form-login-username">
				<label for="username"><?php echo JText::_('COM_REDEVENT_USERNAME') ?></label>
				<input id="username" type="text" name="username" class="inputbox"  size="18" />
			</p>
			<p id="form-login-password">
				<label for="passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>
				<input id="passwd" type="password" name="password" class="inputbox" size="18"  />
			</p>
			<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
				<p id="form-login-remember">
					<label for="remember"><?php echo JText::_('COM_REDEVENT_B2B_LOGIN_REMEMBER_ME') ?></label>
					<input id="remember" type="checkbox" name="remember" class="inputbox" value="yes"/>
				</p>
			<?php endif; ?>
			<input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGIN') ?>" />
			<input type="hidden" name="option" value="com_redeventb2b" />
			<input type="hidden" name="task" value="frontadminlogin.login" />
			<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
		<ul>
			<li>
				<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
					<?php echo JText::_('COM_REDEVENT_B2B_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
			</li>
			<li>
				<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
					<?php echo JText::_('COM_REDEVENT_B2B_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
			</li>
			<?php
			$usersConfig = JComponentHelper::getParams('com_users');
			if ($usersConfig->get('allowUserRegistration')) : ?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
						<?php echo JText::_('COM_REDEVENT_B2B_LOGIN_REGISTER'); ?></a>
				</li>
			<?php endif; ?>
		</ul>
	</form>
</div>
