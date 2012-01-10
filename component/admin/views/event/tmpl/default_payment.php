<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_('COM_REDEVENT_NOTES' ) ); ?>
<table class="editevent">	
	<tr>
		<td class="key">
			<label for="paymentprocessing">
				<?php echo JText::_('COM_REDEVENT_PAYMENTPROCESSING' ).':'; ?>
			</label><br/><?php echo JText::_('COM_REDEVENT_PAYMENTPROCESSING_INFO'); ?>
		</td>
		<td>
				<?php echo $this->printTags('paymentprocessing'); ?>
				<?php echo $this->editor->display( 'paymentprocessing',  $this->row->paymentprocessing, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="paymentaccepted">
				<?php echo JText::_('COM_REDEVENT_PAYMENTACCEPTED' ).':'; ?>
			</label><br/><?php echo JText::_('COM_REDEVENT_PAYMENTACCEPTED_INFO'); ?>
		</td>
		<td>
				<?php echo $this->printTags('paymentaccepted'); ?>
				<?php echo $this->editor->display( 'paymentaccepted',  $this->row->paymentaccepted, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
		</td>
	</tr>
</table>
