	<table class="noshow">
      <tr>
        <td width="50%" valign="top">
<fieldset class="adminform">
			<legend><?php echo JText::_('COM_REDEVENT_META_HANDLING' ); ?></legend>
				<table class="admintable" cellspacing="1">
				<tbody>
					<tr>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_META_KEYWORDS' ); ?>::<?php echo JText::_('COM_REDEVENT_META_KEYWORDS_TIP'); ?>">
								<?php echo JText::_('COM_REDEVENT_META_KEYWORDS' ); ?>
							</span>
						</td>
       					<td valign="top">
							<?php
								$meta_key = explode(", ", $this->elsettings->meta_keywords);
							?>
							<select name="meta_keywords[]" multiple="multiple" size="5" class="inputbox">
								<option value="[title]" <?php if(in_array("[title]",$meta_key)) { echo "selected=\"selected\""; } ?>>
								<?php echo JText::_('COM_REDEVENT_EVENT_TITLE' ); ?></option>
								<option value="[a_name]" <?php if(in_array("[a_name]",$meta_key)) { echo "selected=\"selected\""; } ?>>
								<?php echo JText::_('COM_REDEVENT_VENUE' ); ?></option>
								<!-- <option value="[locid]" <?php if(in_array("[locid]",$meta_key)) { echo "selected=\"selected\""; } ?>>
								<?php echo JText::_('COM_REDEVENT_CITY' ); ?></option> -->
								<option value="[catsid]" <?php if(in_array("[catsid]",$meta_key)) { echo "selected=\"selected\""; } ?>>
								<?php echo JText::_('COM_REDEVENT_CATEGORY' ); ?></option>
								<option value="[dates]" <?php if(in_array("[dates]",$meta_key)) { echo "selected=\"selected\""; } ?>>
								<?php echo JText::_('COM_REDEVENT_DATE' ); ?></option>
								<option value="[times]" <?php if(in_array("[times]",$meta_key)) { echo "selected=\"selected\""; } ?>>
								<?php echo JText::_('COM_REDEVENT_EVENT_TIME' ); ?></option>
								<option value="[enddates]" <?php if(in_array("[enddates]",$meta_key)) { echo "selected=\"selected\""; } ?>>
								<?php echo JText::_('COM_REDEVENT_ENDDATE' ); ?></option>
								<option value="[endtimes]" <?php if(in_array("[endtimes]",$meta_key)) { echo "selected=\"selected\""; } ?>>
								<?php echo JText::_('COM_REDEVENT_END_TIME' ); ?></option>
							</select>
       	 				</td>
      				</tr>
					<tr>
						<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_META_DESCRIPTION' ); ?>::<?php echo JText::_('COM_REDEVENT_META_DESCRIPTION_TIP'); ?>">
								<?php echo JText::_('COM_REDEVENT_META_DESCRIPTION' ); ?>
							</span>
						</td>
						<td>
							<script type="text/javascript">
							<!--
								function insert_keyword($keyword) {
									var meta_description = $("meta_description").value;
									meta_description += " "+$keyword;
									$("meta_description").value = meta_description;
								}

								function include_description() {
									$("meta_description").value = "<?php echo JText::_('COM_REDEVENT_META_DESCRIPTION_STANDARD' ); ?>";
								}
							-->
							</script>

							<input class="inputbox" type="button" onclick="insert_keyword('[title]')" value="<?php echo JText::_('COM_REDEVENT_EVENT_TITLE' ); ?>" />
							<input class="inputbox" type="button" onclick="insert_keyword('[a_name]')" value="<?php echo JText::_('COM_REDEVENT_VENUE' ); ?>" />
							<input class="inputbox" type="button" onclick="insert_keyword('[catsid]')" value="<?php echo JText::_('COM_REDEVENT_CATEGORY' ); ?>" />
							<input class="inputbox" type="button" onclick="insert_keyword('[dates]')" value="<?php echo JText::_('COM_REDEVENT_DATE' ); ?>" />
							<p>
								<input class="inputbox" type="button" onclick="insert_keyword('[times]')" value="<?php echo JText::_('COM_REDEVENT_EVENT_TIME' ); ?>" />
								<input class="inputbox" type="button" onclick="insert_keyword('[enddates]')" value="<?php echo JText::_('COM_REDEVENT_ENDDATE' ); ?>" />
								<input class="inputbox" type="button" onclick="insert_keyword('[endtimes]')" value="<?php echo JText::_('COM_REDEVENT_END_TIME' ); ?>" />
							</p>
							<textarea name="meta_description" id="meta_description" cols="35" rows="3" class="inputbox"><?php echo $this->elsettings->meta_description; ?></textarea>
							<br/>
							<input type="button" value="<?php echo JText::_('COM_REDEVENT_META_DESCRIPTION_BUTTON' ); ?>" onclick="include_description()" />
							&nbsp;
							<span class="error hasTip" title="<?php echo JText::_('COM_REDEVENT_WARNING' );?>::<?php echo JText::_('COM_REDEVENT_META_DESCRIPTION_WARN' ); ?>">
								<?php echo $this->WarningIcon(); ?>
							</span>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		
		</td>
        <td width="50%" valign="top">

		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_REDEVENT_COMMENTS' ); ?></legend>
				<table class="admintable" cellspacing="1">
				<tbody>
					<tr>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_SUPPORTED_COMMENT_SOLUTIONS' ); ?>::<?php echo JText::_('COM_REDEVENT_SUPPORTED_COMMENT_SOLUTIONS_TIP'); ?>">
								<?php echo JText::_('COM_REDEVENT_SUPPORTED_COMMENT_SOLUTIONS' ); ?>
							</span>
						</td>
       					<td valign="top">
       		 				<?php
							$config			= &JFactory::getConfig();
							$checkjcomment 	= file_exists(JPATH_SITE.DS.'components'.DS.'com_jcomments'.DS.'jcomments.php');
							$checkjomcomment= file_exists(JPATH_SITE.DS.'plugins'.DS.'content'.DS.'jom_comment_bot.php');
							
							if ($checkjcomment) {
								echo '<strong>'.JText::_('COM_REDEVENT_JCOMMENTS' ).': </strong><font color="green">'.JText::_('COM_REDEVENT_AVAILABLE' ).'</font><br />';
							} else {
								echo '<strong>'.JText::_('COM_REDEVENT_JCOMMENTS' ).': </strong><font color="red">'.JText::_('COM_REDEVENT_UNAVAILABLE' ).'</font><br />';
							}
							
							if ( $checkjomcomment){
								echo '<strong>'.JText::_('COM_REDEVENT_JOMCOMMENT' ).': </strong><font color="green">'.JText::_('COM_REDEVENT_AVAILABLE' ).'</font><br />';
							} else {
								echo '<strong>'.JText::_('COM_REDEVENT_JOMCOMMENT' ).': </strong><font color="red">'.JText::_('COM_REDEVENT_UNAVAILABLE' ).'</font><br />';
							}
							?>
       	 				</td>
      				</tr>
					<tr>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_SELECT_3PD_COMMENT_COMP' ); ?>::<?php echo JText::_('COM_REDEVENT_SELECT_3PD_COMMENT_COMP_TIP'); ?>">
								<?php echo JText::_('COM_REDEVENT_SELECT_3PD_COMMENT_COMP' ); ?>
							</span>
						</td>
       					<td valign="top">
       						<?php
		   					$commentsystem = array();
							$commentsystem[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_NO_COMMENTS' ) );
							
							if ( $checkjcomment ){
								$commentsystem[] = JHTML::_('select.option', '1', JText::_('COM_REDEVENT_JCOMMENTS' ) );
							}
							if ( $checkjomcomment ){
								$commentsystem[] = JHTML::_('select.option', '2', JText::_('COM_REDEVENT_JOMCOMMENT' ) );
							}
							$commentoptions = JHTML::_('select.genericlist', $commentsystem, 'commentsystem', 'size="1" class="inputbox"', 'value', 'text', $this->elsettings->commentsystem );
							echo $commentoptions;
        					?>
       	 				</td>
      				</tr>
				</tbody>
			</table>
		</fieldset>
		
       	<fieldset class="adminform">
			<legend><?php echo JText::_('COM_REDEVENT_IMAGE_HANDLING' ); ?></legend>
				<table class="admintable" cellspacing="1">
				<tbody>
					<tr>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_IMAGE_FILESIZE' ); ?>::<?php echo JText::_('COM_REDEVENT_IMAGE_FILESIZE_TIP'); ?>">
								<?php echo JText::_('COM_REDEVENT_IMAGE_FILESIZE' ); ?>
							</span>
						</td>
       					<td valign="top">
							<input type="text" name="sizelimit" value="<?php echo $this->elsettings->sizelimit; ?>" size="10" maxlength="10" />
       	 				</td>
      				</tr>
					<tr>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_IMAGE_HEIGHT' ); ?>::<?php echo JText::_('COM_REDEVENT_IMAGE_HEIGHT_TIP'); ?>">
								<?php echo JText::_('COM_REDEVENT_IMAGE_HEIGHT' ); ?>
							</span>
						</td>
       					<td valign="top">
         					<input type="text" name="imagehight" value="<?php echo $this->elsettings->imagehight; ?>" size="10" maxlength="10" />
       	 				</td>
      				</tr>
					<tr>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_IMAGE_WIDTH' ); ?>::<?php echo JText::_('COM_REDEVENT_IMAGE_WIDTH_TIP'); ?>">
								<?php echo JText::_('COM_REDEVENT_IMAGE_WIDTH' ); ?>
							</span>
						</td>
       					<td valign="top">
         					<input type="text" name="imagewidth" value="<?php echo $this->elsettings->imagewidth; ?>" size="10" maxlength="10" />
         					<span class="error hasTip" title="<?php echo JText::_('COM_REDEVENT_WARNING' );?>::<?php echo JText::_('COM_REDEVENT_WARNING_MAX_IMAGEWIDTH' ); ?>">
								<?php echo $this->WarningIcon(); ?>
							</span>
       	 				</td>
      				</tr>
					<tr>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_GD_LIBRARY' ); ?>::<?php echo JText::_('COM_REDEVENT_GD_LIBRARY_TIP'); ?>">
								<?php echo JText::_('COM_REDEVENT_GD_LIBRARY' ); ?>
							</span>
						</td>
       					<td valign="top">
         					<?php
							$mode = 0;
							if ($this->elsettings->gddisabled == 1) {
								$mode = 1;
							} // if

							//is the gd library installed on the server running EventList?
							if ($gdv = redEVENTImage::gdVersion()) {

								//is it Version two or higher? If yes let the user the choice
   								if ($gdv >= 2) {
   								?>
       								<input type="radio" id="gddisabled0" name="gddisabled" value="0" onclick="changegdMode(0)"<?php if (!$mode) echo ' checked="checked"'; ?>/><?php echo JText::_('COM_REDEVENT_NO' ); ?>
									<input type="radio" id="gddisabled1" name="gddisabled" value="1" onclick="changegdMode(1)"<?php if ($mode) echo ' checked="checked"'; ?>/><?php echo JText::_('COM_REDEVENT_YES' ); ?>
       							<?php
       								$note	= JText::_('COM_REDEVENT_GD_VERSION_TWO' );
       								$color	= 'green';

       							//No it is version one...disable thumbnailing
   								} else {
   								?>
   								<input type="hidden" name="gddisabled" value="0" />
   								<?php
   								$note	= JText::_('COM_REDEVENT_GD_VERSION_ONE' );
   								$color	= 'red';
   								}

   							//the gd library is not available on this server...disable thumbnailing
							} else {
							?>
								<input type="hidden" name="gddisabled" value="0" />
   							<?php
   								$note	= JText::_('COM_REDEVENT_NO_GD_LIBRARY' );
   								$color	= 'red';
							}
							?>
							<br />
							<strong><?php echo JText::_('COM_REDEVENT_STATUS' ).':'; ?></strong>
							<font color="<?php echo $color; ?>"><?php echo $note; ?></font>
						</td>
      				</tr>
      				<tr id="gd1"<?php if (!$mode) echo ' style="display:none"'; ?>>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_IMAGE_LIGHTBOX' ); ?>::<?php echo JText::_('COM_REDEVENT_IMAGE_LIGHTBOX_TIP'); ?>">
								<?php echo JText::_('COM_REDEVENT_IMAGE_LIGHTBOX' ); ?>
							</span>
						</td>
       					<td valign="top">
         					<?php
							$html = JHTML::_('select.booleanlist', 'lightbox', 'class="inputbox"', $this->elsettings->lightbox );
							echo $html;
							?>
       	 				</td>
      				</tr>
				</tbody>
			</table>
		</fieldset>

		

		</td>
      </tr>
	</table>