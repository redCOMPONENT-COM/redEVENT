<?php $k = 0; ?>
<table class="adminform">
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<input class="inputbox" type="button" onclick="insert_keyword('[title]')" value="<?php echo JText::_( 'EVENT TITLE' ); ?>" />
			<input class="inputbox" type="button" onclick="insert_keyword('[a_name]')" value="<?php echo JText::_( 'VENUE' ); ?>" />
			<input class="inputbox" type="button" onclick="insert_keyword('[catsid]')" value="<?php echo JText::_( 'CATEGORY' ); ?>" />
			<input class="inputbox" type="button" onclick="insert_keyword('[dates]')" value="<?php echo JText::_( 'DATE' ); ?>" />
			<p><input class="inputbox" type="button" onclick="insert_keyword('[times]')" value="<?php echo JText::_( 'EVENT TIME' ); ?>" />
			<input class="inputbox" type="button" onclick="insert_keyword('[enddates]')" value="<?php echo JText::_( 'ENDDATE' ); ?>" />
			<input class="inputbox" type="button" onclick="insert_keyword('[endtimes]')" value="<?php echo JText::_( 'END TIME' ); ?>" /></p>
			<br/>
			<label for="meta_keywords">
				<?php echo JText::_( 'META KEYWORDS' ).':'; ?>
			</label>
			<br />

			<?php
			if (!empty($this->row->meta_keywords)) {
				$meta_keywords = $this->row->meta_keywords;
			} else {
				$meta_keywords = $this->elsettings->meta_keywords;
			}
			?>

			<textarea class="inputbox" name="meta_keywords" id="meta_keywords" rows="5" cols="40" maxlength="150" onfocus="get_inputbox('meta_keywords')" onblur="change_metatags()"><?php echo $meta_keywords; ?></textarea>
	</td>
</tr>
<tr class="row1">
	<td>
		<label for="meta_description">
			<?php echo JText::_( 'META DESCRIPTION' ).':'; ?>
		</label>
		<br />
		<?php
		if (!empty($this->row->meta_description)) {
			$meta_description = $this->row->meta_description;
		} else {
			$meta_description = $this->elsettings->meta_description;
		}
		?>

		<textarea class="inputbox" name="meta_description" id="meta_description" rows="5" cols="40" maxlength="200" onfocus="get_inputbox('meta_description')" onblur="change_metatags()"><?php echo $meta_description; ?></textarea>
	</td>
</tr>
<!-- include the metatags end-->
</table>
<script type="text/javascript">
<!--
starter("<?php echo JText::_( 'META ERROR' ); ?>");	// da window.onload schon belegt wurde, wird die Funktion 'manuell' aufgerufen
-->
</script>
