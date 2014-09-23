<?php
/**
 * @package    Redevent.site
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');
?>
<div id="b2b-login" class="akeeba-bootstrap">
	<div class="img-wrapper"><img src="/templates/redweb/images/logo.png" /></div>
	
	<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_B2B_LOGIN_TITLE'); ?></h2>

	<form action="<?php echo JRoute::_('index.php', true, $this->params->get('secure', 0)); ?>" method="post" id="login-form" >
		<div class="login-box">
			<fieldset class="userdata">
				<p id="form-login-username">
					<label for="username"><?php echo JText::_('COM_REDEVENT_USERNAME') ?></label>
					<input id="username" type="text" name="username" class="inputbox"  size="18" />
				</p>
				<p id="form-login-password">
					<label for="passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>
					<input id="passwd" type="password" name="password" class="inputbox" size="18"  />
				</p>
				<div class="login-wrapper">
					<div class="green-center">
						<input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGIN') ?>" />
					</div>
				</div>
				<input type="hidden" name="option" value="com_redevent" />
				<input type="hidden" name="controller" value="frontadminlogin" />
				<input type="hidden" name="task" value="login" />
				<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</fieldset>
		</div>
		<ul class="nav-stacked">
			<li>
				<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
					<?php echo JText::_('COM_REDEVENT_B2B_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
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
