	<table class="noshow">
      <tr>
        <td width="50%">
        
        	<fieldset class="adminform">
			<legend><?php echo JText::_( 'EVENTS' ); ?></legend>
				<table class="admintable" cellspacing="1">
				<tbody>	  				
					<tr>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_( 'DEFAULT FORM ID' ); ?>::<?php echo JText::_('DEFAULT FORM ID TIP'); ?>">
								<?php echo JText::_( 'DEFAULT FORM ID' ); ?>
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
			<legend><?php echo JText::_( 'COMMENTS' ); ?></legend>
				<table class="admintable" cellspacing="1">
				<tbody>
					<tr>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_( 'SUPPORTED COMMENT SOLUTIONS' ); ?>::<?php echo JText::_('SUPPORTED COMMENT SOLUTIONS TIP'); ?>">
								<?php echo JText::_( 'SUPPORTED COMMENT SOLUTIONS' ); ?>
							</span>
						</td>
       					<td valign="top">
       		 				<?php
							$config			= &JFactory::getConfig();
							$checkjcomment 	= file_exists(JPATH_SITE.DS.'components'.DS.'com_jcomments'.DS.'jcomments.php');
							$checkjomcomment= file_exists(JPATH_SITE.DS.'plugins'.DS.'content'.DS.'jom_comment_bot.php');
							
							if ($checkjcomment) {
								echo '<strong>'.JText::_( 'JCOMMENTS' ).': </strong><font color="green">'.JText::_( 'AVAILABLE' ).'</font><br />';
							} else {
								echo '<strong>'.JText::_( 'JCOMMENTS' ).': </strong><font color="red">'.JText::_( 'UNAVAILABLE' ).'</font><br />';
							}
							
							if ( $checkjomcomment){
								echo '<strong>'.JText::_( 'JOMCOMMENT' ).': </strong><font color="green">'.JText::_( 'AVAILABLE' ).'</font><br />';
							} else {
								echo '<strong>'.JText::_( 'JOMCOMMENT' ).': </strong><font color="red">'.JText::_( 'UNAVAILABLE' ).'</font><br />';
							}
							?>
       	 				</td>
      				</tr>
					<tr>
	          			<td width="300" class="key">
							<span class="editlinktip hasTip" title="<?php echo JText::_( 'SELECT 3PD COMMENT COMP' ); ?>::<?php echo JText::_('SELECT 3PD COMMENT COMP TIP'); ?>">
								<?php echo JText::_( 'SELECT 3PD COMMENT COMP' ); ?>
							</span>
						</td>
       					<td valign="top">
       						<?php
		   					$commentsystem = array();
							$commentsystem[] = JHTML::_('select.option', '0', JText::_( 'NO COMMENTS' ) );
							
							if ( $checkjcomment ){
								$commentsystem[] = JHTML::_('select.option', '1', JText::_( 'JCOMMENTS' ) );
							}
							if ( $checkjomcomment ){
								$commentsystem[] = JHTML::_('select.option', '2', JText::_( 'JOMCOMMENT' ) );
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