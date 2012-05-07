<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_('COM_REDEVENT_NOTES' ) ); ?>
<?php $k = 0; ?>
<table class="editevent" id="act-table">
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td class="key">
			<?php echo $this->form->getLabel('notify'); ?>
		</td>
		<td>
			<?php echo $this->form->getInput('notify'); ?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td class="key">
			<?php echo $this->form->getLabel('activate'); ?>
		</td>
		<td>
			<?php echo $this->form->getInput('activate'); ?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td class="key">
			<?php echo $this->form->getLabel('notify_subject'); ?>
		</td>
		<td>
			<?php echo $this->form->getInput('notify_subject'); ?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td class="key">
			<?php echo $this->form->getLabel('notify_body'); ?>
			<br/><?php echo JText::_('COM_REDEVENT_NOTIFY_BODY_NOTE'); ?>
		</td>
		<td>
			<?php echo $this->printTags('notify_body'); ?>
			<?php echo $this->form->getInput('notify_body'); ?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?> activation-field">
		<td class="key">
			<?php echo $this->form->getLabel('enable_activation_confirmation'); ?>
		</td>
		<td>
			<?php echo $this->form->getInput('enable_activation_confirmation'); ?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?> activation-field">
		<td class="key">
			<?php echo $this->form->getLabel('notify_confirm_subject'); ?>
		</td>
		<td>
			<?php echo $this->form->getInput('notify_confirm_subject'); ?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?> activation-field">
		<td class="key">
			<?php echo $this->form->getLabel('notify_confirm_body'); ?>
		</td>
		<td>
			<?php echo $this->printTags('notify_confirm_body'); ?>
			<?php echo $this->form->getInput('notify_confirm_body'); ?>
		</td>
	</tr>
</table>
