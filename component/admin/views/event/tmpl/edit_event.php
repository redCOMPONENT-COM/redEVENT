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
			<?php echo $this->form->getLabel('title'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('title'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('alias'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('alias'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('course_code'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('course_code'); ?>
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
			<?php echo $this->form->getLabel('published'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('published'); ?>
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
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('created_by'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('created_by'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('categories'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('categories'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('datimage'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('datimage'); ?>
		</div>
	</div>

	<?php if (file_exists(JPATH_SITE . '/components/com_redmailflow') && JComponentHelper::isEnabled('com_redmailflow')): ?>
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

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('datdescription'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('datdescription'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('summary'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('summary'); ?>
		</div>
	</div>
</div>
<div class="span3">
	<?php echo RLayoutHelper::render('joomla.edit.metadata', $this); ?>
</div>
