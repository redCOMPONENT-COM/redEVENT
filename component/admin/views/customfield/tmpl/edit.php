<?php
/**
 * @package    Redevent.admin
 *
 * @copyright  Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

JHtml::_('rjquery.chosen', 'select');
?>
<script type="text/javascript">
	jQuery(document).ready(function()
	{
		// Disable click function on btn-group
		jQuery(".btn-group").each(function(index){
			if (jQuery(this).hasClass('disabled'))
			{
				jQuery(this).find("label").off('click');
			}
		});
	});
</script>
<form
	action="index.php?option=com_redevent&task=customfield.edit&id=<?php echo $this->item->id; ?>"
	method="post" name="adminForm" class="form-validate form-horizontal" id="adminForm">
	<div class="row-fluid">
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
				<?php echo $this->form->getLabel('type'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('type'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('tag'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('tag'); ?>
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
				<?php echo $this->form->getLabel('object_key'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('object_key'); ?>
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
				<?php echo $this->form->getLabel('tips'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('tips'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('searchable'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('searchable'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('in_lists'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('in_lists'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('frontend_edit'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('frontend_edit'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('required'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('required'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('min'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('min'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('max'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('max'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('options'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('options'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('default_value'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('default_value'); ?>
			</div>
		</div>
	</div>
	<?php echo $this->form->getInput('id'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
