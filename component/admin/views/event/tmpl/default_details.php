<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) ); ?>
<?php $k = 0; ?>
<table class="adminform">
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="course_price">
				<?php echo JText::_( 'COURSE_PRICE' ).':'; ?>
			</label>
		</td>
		<td>
			<input type="text" class="inputbox" name="course_price" value="<?php echo $this->row->course_price; ?>" size="15" id="course_price" />
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="course_credit">
				<?php echo JText::_( 'COURSE_CREDIT' ).':'; ?>
			</label>
		</td>
		<td>
			<input type="text" class="inputbox" name="course_credit" value="<?php echo $this->row->course_credit; ?>" size="15" id="course_credit" />
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="max_multi_signup">
				<?php echo JText::_( 'MAX_MULTI_SIGNUP' ).':'; ?>
			</label>
		</td>
		<td>
			<input type="text" class="inputbox" name="max_multi_signup" value="<?php echo $this->row->max_multi_signup; ?>" size="15" id="max_multi_signup" />
			<span class="editlinktip hasTip" title="<?php echo JText::_( 'MAX_MULTI_SIGNUP_TIP' ); ?>">
				<?php echo $infoimage; ?>
			</span>
		</td>
	</tr>
</table>
