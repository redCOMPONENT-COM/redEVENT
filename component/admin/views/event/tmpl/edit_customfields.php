<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_('COM_REDEVENT_NOTES' ) ); ?>
<?php $k = 0; ?>
	<?php foreach ($this->customfields as $field): ?>

	<div class="control-group">
		<div class="control-label">
			<?php echo $field->getLabel(); ?>
		</div>
		<div class="controls">
			<?php echo $field->render(); ?>
		</div>
	</div>

	<?php endforeach; ?>
<?php
