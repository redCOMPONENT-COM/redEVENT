<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) ); ?>
<table class="adminform">
	<tr>
		<td colspan="2">
			<span class="editlinktip hasTip" title="<?php echo JText::_( 'PAYMENTPROCESSING' ); ?>::<?php echo JText::_('PAYMENTPROCESSING_INFO'); ?>">
				<?php echo $infoimage; ?>
			</span><label for="paymentprocessing"><?php echo JText::_('PAYMENTPROCESSING'); ?></label>
			
			<div id="paymentprocessing_screen">
				<?php echo $this->printTags(); ?>
				<?php echo $this->editor->display( 'paymentprocessing',  $this->row->paymentprocessing, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</div>
		</td>
	</tr>
	
	<tr>
		<td colspan="2">
			<span class="editlinktip hasTip" title="<?php echo JText::_( 'PAYMENTACCEPTED' ); ?>::<?php echo JText::_('PAYMENTACCEPTED_INFO'); ?>">
				<?php echo $infoimage; ?>
			</span><label for="paymentaccepted"><?php echo JText::_('PAYMENTACCEPTED'); ?></label>
			
			<div id="paymentaccepted_screen">
				<?php echo $this->printTags(); ?>
				<?php echo $this->editor->display( 'paymentaccepted',  $this->row->paymentaccepted, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</div>
		</td>
	</tr>
</table>
