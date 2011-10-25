<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_('COM_REDEVENT_NOTES' ) ); ?>
<?php $k = 0; ?>
<table class="adminform">
<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="redform_id">
				<?php echo JText::_('COM_REDEVENT_REDFORM_FORM_ID' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo $this->lists['redforms']; ?>
	</td>
</tr>
<tr class="row<?php echo $k = 1 - $k; ?>">
	<td>
		<label for="max_multi_signup">
			<?php echo JText::_('COM_REDEVENT_MAX_MULTI_SIGNUP' ).':'; ?>
		</label>
	</td>
	<td>
		<input type="text" class="inputbox" name="max_multi_signup" value="<?php echo $this->row->max_multi_signup; ?>" size="15" id="max_multi_signup" />
		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_MAX_MULTI_SIGNUP_TIP' ); ?>">
			<?php echo $infoimage; ?>
		</span>
	</td>
</tr>
<?php if (count($this->formfields) > 0) { ?>
<tr class="row<?php echo $k = 1 - $k; ?>">
	<td colspan="2">
	<hr />
	</td>
</tr>
<tr class="row<?php echo $k = 1 - $k; ?>">
	<td>
		<label for="redform_fields">
			<?php echo JText::_('COM_REDEVENT_REDFORM_FORM_SELECT_FIELDS' ).':'; ?>
		</label>
		</td>
		<td>
			<table>
			<?php
				$showfields = explode(",", $this->row->showfields);
				foreach ($this->formfields as $id => $field) {
					echo '<tr><td>'.$field->field_header.'</td>';
					if (in_array($field->id, $showfields)) {
						echo '<td>'.JHTML::_('select.booleanlist', 'showfield'.$field->id, '', 1).'</td></tr>';
					}
					else echo '<td>'.JHTML::_('select.booleanlist', 'showfield'.$field->id, '', 0).'</td></tr>';
				}
			?>
			</table>
	</td>
</tr>
<?php } ?>
</table>
