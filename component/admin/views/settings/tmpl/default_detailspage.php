	<table class="noshow">
      <tr>
        <td width="50%">
        
        	<fieldset class="adminform">
			<legend><?php echo JText::_('COM_REDEVENT_EVENTS' ); ?></legend>
				<table class="admintable" cellspacing="1">
				<tbody>	  				
					<tr>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_DEFAULT_FORM_ID' ); ?>::<?php echo JText::_('COM_REDEVENT_DEFAULT_FORM_ID_TIP'); ?>">
								<?php echo JText::_('COM_REDEVENT_DEFAULT_FORM_ID' ); ?>
							</span>
						</td>
       					<td valign="top">
          					<input type="text" name="defaultredformid" value="<?php echo $this->elsettings->defaultredformid; ?>" size="5" maxlength="4" />
       	 				</td>
      				</tr>
				</tbody>
			</table>
			</fieldset>

		</td>
        <td width="50%">
		
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

	</td>
  </tr>
</table>