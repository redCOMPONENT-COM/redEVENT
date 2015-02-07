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

<div class="tabbable">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#typewebform" data-toggle="tab">
			<?php echo JText::_('COM_REDEVENT_WEBFORM'); ?></a>
		</li>
		<li><a href="#typeexternal" data-toggle="tab"><?php echo JText::_('COM_REDEVENT_EXTERNAL'); ?></a></li>
		<li><a href="#typephone" data-toggle="tab"><?php echo JText::_('COM_REDEVENT_PHONE'); ?></a></li>
		<li><a href="#typeemail" data-toggle="tab"><?php echo JText::_('COM_REDEVENT_EMAIL'); ?></a></li>
		<li><a href="#typeformaloffer" data-toggle="tab"><?php echo JText::_('COM_REDEVENT_FORMALOFFER'); ?></a></li>
	</ul>

	<div class="tab-content">

		<div class="tab-pane active" id="typewebform">
			<div class="type-tab-heading">
				<div class="control-group">
					<div class="control-label">
						<label for="submission_type_webform"><?php echo JText::_('JENABLED'); ?></label>
					</div>
					<div class="controls">
						<input type="checkbox" class="reg-type"
						       id="submission_type_webform" name="jform[submission_types][]" value="webform"
							<?php if (in_array('webform', $submission_types)) echo ' checked="checked"'; ?>
							/>
					</div>
				</div>
			</div>

			<div class="type-tab-body">
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
			</div>
		</div>

		<div class="tab-pane" id="typeexternal">
			<div class="type-tab-heading">
				<div class="control-group">
					<div class="control-label">
						<label for="submission_type_webform"><?php echo JText::_('JENABLED'); ?></label>
					</div>
					<div class="controls">
						<input type="checkbox" class="reg-type"
						       id="submission_type_external" name="jform[submission_types][]" value="external"
							<?php if (in_array('external', $submission_types)) echo ' checked="checked"'; ?>
							/>
					</div>
				</div>
			</div>

			<div class="type-tab-body">
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

		<div class="tab-pane active" id="typephone">
			<div class="type-tab-heading">
				<div class="control-group">
					<div class="control-label">
						<label for="submission_type_webform"><?php echo JText::_('JENABLED'); ?></label>
					</div>
					<div class="controls">
						<input type="checkbox" class="reg-type"
						       id="submission_type_phone_check" name="jform[submission_types][]" value="phone"
							<?php if (in_array('phone', $submission_types)) echo ' checked="checked"'; ?>
							/>
					</div>
				</div>
			</div>

			<div class="type-tab-body">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('submission_type_phone'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('submission_type_phone'); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="tab-pane" id="typeemail">
			<div class="type-tab-heading">
				<div class="control-group">
					<div class="control-label">
						<label for="submission_type_webform"><?php echo JText::_('JENABLED'); ?></label>
					</div>
					<div class="controls">
						<input type="checkbox" class="reg-type"
						       id="submission_type_email_check" name="jform[submission_types][]" value="email"
							<?php if (in_array('email', $submission_types)) echo ' checked="checked"'; ?>
							/>
					</div>
				</div>
			</div>

			<div class="type-tab-body">
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
			</div>
		</div>


		<div class="tab-pane" id="typeformaloffer">
			<div class="type-tab-heading">
				<div class="control-group">
					<div class="control-label">
						<label for="submission_type_webform"><?php echo JText::_('JENABLED'); ?></label>
					</div>
					<div class="controls">
						<input type="checkbox" class="reg-type"
						       id="submission_type_formaloffer_check" name="jform[submission_types][]" value="formaloffer"
							<?php if (in_array('formaloffer', $submission_types)) echo ' checked="checked"'; ?>
							/>
					</div>
				</div>
			</div>

			<div class="type-tab-body">
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
			</div>
		</div>
	</div>
</div>
