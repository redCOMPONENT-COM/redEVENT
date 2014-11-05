<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');
?>

<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('notify'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('notify'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('activate'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('activate'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('notify_subject'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('notify_subject'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('notify_body'); ?>
		<em><?php echo JText::_('COM_REDEVENT_NOTIFY_BODY_NOTE'); ?></em>
	</div>
	<div class="controls">
		<?php echo $this->printTags('notify_body'); ?>
		<?php echo $this->form->getInput('notify_body'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('enable_activation_confirmation'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('enable_activation_confirmation'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('notify_confirm_subject'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('notify_confirm_subject'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('notify_confirm_body'); ?>
	</div>
	<div class="controls">
		<?php echo $this->printTags('notify_confirm_body'); ?>
		<?php echo $this->form->getInput('notify_confirm_body'); ?>
	</div>
</div>
