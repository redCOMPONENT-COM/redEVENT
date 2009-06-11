<?php $k = 0; ?>
<table class="adminform">
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td class="redevent_settings">
			<label for="registra">
				<?php echo JText::_( 'ENABLE REGISTRATION' ).':'; ?>
			</label>
		</td>
		<td>
			<?php
			$html = JHTML::_('select.booleanlist', 'registra', '', $this->row->registra );
			echo $html;
			?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="registra">
				<?php echo JText::_( 'CREATE JOOMLA USER' ).':'; ?>
			</label>
		</td>
		<td>
			<?php
			echo JHTML::_('select.booleanlist', 'juser', '', $this->row->juser );
			?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="unregistra">
				<?php echo JText::_( 'ENABLE UNREGISTRATION' ).':'; ?>
			</label>
		</td>
		<td>
			<?php
			$html = JHTML::_('select.booleanlist', 'unregistra', '', $this->row->unregistra );
			echo $html;
			?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="show_names">
				<?php echo JText::_( 'SHOW REGISTERED FRONTEND' ).':'; ?>
			</label>
		</td>
		<td>
			<?php
			$html = JHTML::_('select.booleanlist', 'show_names', '', $this->row->show_names );
			echo $html;
			?>
		</td>
	</tr>
</table>
