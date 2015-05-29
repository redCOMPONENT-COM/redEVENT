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
	action="index.php?option=com_redevent&task=organization.edit&id=<?php echo $this->item->id; ?>"
	method="post" name="adminForm" class="form-validate form-horizontal" id="adminForm">
	<div class="row-fluid">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('b2b_attendee_notification_mailflow'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('b2b_attendee_notification_mailflow'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('b2b_orgadmin_mailflow_confirmation_subject_tag'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('b2b_orgadmin_mailflow_confirmation_subject_tag'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('b2b_orgadmin_mailflow_cancellation_subject_tag'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('b2b_orgadmin_mailflow_cancellation_subject_tag'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('b2b_orgadmin_mailflow_confirmation_body_tag'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('b2b_orgadmin_mailflow_confirmation_body_tag'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('b2b_orgadmin_mailflow_cancellation_body_tag'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('b2b_orgadmin_mailflow_cancellation_body_tag'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('b2b_cancellation_period'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('b2b_cancellation_period'); ?>
			</div>
		</div>
	</div>
	<?php echo $this->form->getInput('id'); ?>
	<?php echo $this->form->getInput('organization_id'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
