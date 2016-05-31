<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');
?>
<div class="span9">
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('name'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('name'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('language'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('language'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('enable_ical'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('enable_ical'); ?>
		</div>
	</div>

	<?php if (file_exists(JPATH_SITE . '/libraries/redmailflow') && JComponentHelper::isEnabled('com_redmailflow')): ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('mailflow_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('mailflow_id'); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('details_layout'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('details_layout'); ?>
		</div>
	</div>

</div>
<div class="span3">
	<?php echo RedeventLayoutHelper::render('joomla.edit.metadata', $this); ?>
</div>
