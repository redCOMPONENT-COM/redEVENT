	<table class="noshow">
      <tr>
        <td width="50%">

		<fieldset class="adminform">
			<legend><?php echo JText::_( 'IMAGE UPLOAD OPTIONS' ); ?></legend>
			<table class="admintable" cellspacing="1">
				<tbody>
	  				<tr>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_( 'IMAGE UPLOAD OPTIONS' ); ?>::<?php echo JText::_('IMAGE UPLOAD OPTIONS TIP'); ?>">
								<?php echo JText::_( 'IMAGE UPLOAD OPTIONS' ); ?>
							</span>
						</td>
       					<td valign="top">
							<select name="imageenabled" size="1" class="inputbox">
  								<option value="0"<?php if ($this->elsettings->imageenabled == 0) { ?> selected="selected"<?php } ?>><?php echo JText::_( 'DISABLED' ); ?></option>
  								<option value="1"<?php if ($this->elsettings->imageenabled == 1) { ?> selected="selected"<?php } ?>><?php echo JText::_( 'OPTIONAL' ); ?></option>
  								<option value="2"<?php if ($this->elsettings->imageenabled == 2) { ?> selected="selected"<?php } ?>><?php echo JText::_( 'REQUIRED' ); ?></option>
							</select>
       	 				</td>
      				</tr>
			</table>
		</fieldset>

		<fieldset class="adminform">
			<legend><?php echo JText::_( 'DESCRIPTION' ); ?></legend>
			<table class="admintable" cellspacing="1">
				<tbody>
	  				<tr>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_( 'DESCRIPTION LIMIT' ); ?>::<?php echo JText::_('DESCRIPTION LIMIT TIP'); ?>">
								<?php echo JText::_( 'DESCRIPTION LIMIT' ); ?>
							</span>
						</td>
       					<td valign="top">
							<input type="text" name="datdesclimit" value="<?php echo $this->elsettings->datdesclimit; ?>" size="5" maxlength="4" />
       	 				</td>
      				</tr>
				</tbody>
			</table>
		</fieldset>

		</td>
        <td width="50%">

		</td>
      </tr>
    </table>