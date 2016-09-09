<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');
?>
<div class="span7">
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
			<?php echo $this->form->getLabel('course_code'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('course_code'); ?>
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
			<?php echo $this->form->getLabel('template_id'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('template_id'); ?>
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

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('datdescription'); ?>
		</div>
		<div class="controls">
			<div class="tags-info"><?php echo RedeventHelperOutput::getTagsEditorInsertModal($this->form->getField('datdescription')); ?></div>
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
<div class="span5">
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
			<?php echo $this->form->getLabel('created_by'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('created_by'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('modified_by'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('modified_by'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('modified'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('modified'); ?>
		</div>
	</div>
</div>
