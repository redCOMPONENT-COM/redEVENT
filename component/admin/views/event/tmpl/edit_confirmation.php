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
		<?php echo $this->form->getLabel('notify_off_list_subject'); ?>
	</div>
	<div class="controls">
		<div class="tags-info"><?php echo RedeventHelperOutput::getTagsModalLink('notify_off_list_subject'); ?></div>
		<?php echo $this->form->getInput('notify_off_list_subject'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('notify_off_list_body'); ?>
		<em><?php echo JText::_('COM_REDEVENT_NOTIFY_ATTENDING_NOTE'); ?></em>
	</div>
	<div class="controls">
		<div class="tags-info"><?php echo RedeventHelperOutput::getTagsModalLink('notify_off_list_body'); ?></div>
		<?php echo $this->form->getInput('notify_off_list_body'); ?>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('notify_on_list_subject'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('notify_on_list_subject'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('notify_on_list_body'); ?>
		<em><?php echo JText::_('COM_REDEVENT_NOTIFY_ON_LIST_NOTE'); ?></em>
	</div>
	<div class="controls">
		<div class="tags-info"><?php echo RedeventHelperOutput::getTagsModalLink('notify_on_list_body'); ?></div>
		<?php echo $this->form->getInput('notify_on_list_body'); ?>
	</div>
</div>
