<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

$submission_types = explode(',', $this->item->submission_types);
?>

<div class="section-intro">
<?php echo JText::_('COM_REDEVENT_SUBMISSION_TAB_DESC' ); ?>
</div>

<div class="panel panel-default">
	<div class="panel-heading">
		<input type="checkbox" class="reg-type"
		       id="submission_type_external" name="submission_types[]" value="external"
			<?php if (in_array('external', $submission_types)) echo ' checked="checked"'; ?>
			/>
		<label for="submission_type_external"><?php echo JText::_('COM_REDEVENT_EXTERNAL'); ?></label>
	</div>

	<div class="panel-body">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('submission_type_external'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('submission_type_external'); ?>
			</div>
		</div>
	</div>
</div>

<div class="type-params">
	<div class="type-checkbox">
		<input type="checkbox" class="reg-type"
		       id="submission_type_phone_check" name="submission_types[]" value="phone"
			<?php if (in_array('phone', $submission_types)) echo ' checked="checked"'; ?>
			/>
		<label for="submission_type_phone_check"><?php echo JText::_('COM_REDEVENT_PHONE'); ?></label>
	</div>

	<fieldset id="phone-params">
		<legend><?php echo JText::_('COM_REDEVENT_PHONE'); ?></legend>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('submission_type_phone'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('submission_type_phone'); ?>
			</div>
		</div>
	</fieldset>
</div>

<div class="type-params">
	<div class="type-checkbox">
		<input type="checkbox" class="reg-type"
		       id="submission_type_webform" name="submission_types[]" value="webform"
			<?php if (in_array('webform', $submission_types)) echo ' checked="checked"'; ?>
			/>
		<label for="submission_type_webform"><?php echo JText::_('COM_REDEVENT_WEBFORM'); ?></label>
	</div>

	<fieldset id="phone-params">
		<legend><?php echo JText::_('COM_REDEVENT_WEBFORM'); ?></legend>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('submission_type_webform'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('submission_type_webform'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('review_message'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('review_message'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('confirmation_message'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('confirmation_message'); ?>
			</div>
		</div>
	</fieldset>
</div>

<div class="type-params">
	<div class="type-checkbox">
		<input type="checkbox" class="reg-type"
		       id="submission_type_email_check" name="submission_types[]" value="email"
			<?php if (in_array('email', $submission_types)) echo ' checked="checked"'; ?>
			/>
		<label for="submission_type_email_check"><?php echo JText::_('COM_REDEVENT_EMAIL'); ?></label>
	</div>

	<fieldset id="phone-params">
		<legend><?php echo JText::_('COM_REDEVENT_EMAIL'); ?></legend>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('submission_type_email'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('submission_type_email'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('submission_type_email_subject'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('submission_type_email_subject'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('submission_type_email_body'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('submission_type_email_body'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('send_pdf_form'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('send_pdf_form'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('pdf_form_data'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('pdf_form_data'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('submission_type_email_pdf'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('submission_type_email_pdf'); ?>
			</div>
		</div>
	</fieldset>
</div>


<div class="type-params">
	<div class="type-checkbox">
		<input type="checkbox" class="reg-type"
		       id="submission_type_formaloffer_check" name="submission_types[]" value="formaloffer"
			<?php if (in_array('formaloffer', $submission_types)) echo ' checked="checked"'; ?>
			/>
		<label for="submission_type_formaloffer_check"><?php echo JText::_('COM_REDEVENT_FORMALOFFER'); ?></label>
	</div>

	<fieldset id="phone-params">
		<legend><?php echo JText::_('COM_REDEVENT_FORMALOFFER'); ?></legend>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('submission_type_formal_offer'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('submission_type_formal_offer'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('submission_type_formal_offer_subject'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('submission_type_formal_offer_subject'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('submission_type_formal_offer_body'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('submission_type_formal_offer_body'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('show_submission_type_webform_formal_offer'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('show_submission_type_webform_formal_offer'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('submission_type_webform_formal_offer'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('submission_type_webform_formal_offer'); ?>
			</div>
		</div>
	</fieldset>
</div>
