<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.calendar');
?>

<script type="text/javascript">
		Window.onDomReady(function(){
			document.formvalidator.setHandler('categories',
				function (value) {
					if(value=="") {
						return false;
					} else {
					  return true;
					}
				}
			);
		});

		function submitbutton( pressbutton ) {

			if (pressbutton == 'cancelevent' || pressbutton == 'addvenue') {
				elsubmitform( pressbutton );
				return false;
			}

			var form = document.getElementById('adminForm');
			var validator = document.formvalidator;
			var title = $(form.title).getValue();
			title.replace(/\s/g,'');

			if ( title.length==0 ) {
   				alert("<?php echo JText::_( 'ADD TITLE', true ); ?>");
   				validator.handleResponse(false,form.title);
   				return false;
			} else if ( validator.validate(form.categories) === false ) {
    			alert("<?php echo JText::_( 'SELECT CATEGORY', true ); ?>");
    			validator.handleResponse(false,form.categories);
    			return false;
  			} else {
  			<?php
			// JavaScript for extracting editor text
				echo $this->editor->save( 'datdescription' );
			?>
				// submit_unlimited();
				elsubmitform(pressbutton);

				return false;
			}
		}
		
		//joomla submitform needs form name
		function elsubmitform(pressbutton){
			
			var form = document.getElementById('adminForm');
			if (pressbutton) {
				form.task.value=pressbutton;
			}
			if (typeof form.onsubmit == "function") {
				form.onsubmit();
			}
			form.submit();
		}


		var tastendruck = false
		function rechne(restzeichen)
		{

			maximum = <?php echo $this->elsettings->datdesclimit; ?>

			if (restzeichen.datdescription.value.length > maximum) {
				restzeichen.datdescription.value = restzeichen.datdescription.value.substring(0, maximum)
				links = 0
			} else {
				links = maximum - restzeichen.datdescription.value.length
			}
			restzeichen.zeige.value = links
		}

		function berechne(restzeichen)
   		{
  			tastendruck = true
  			rechne(restzeichen)
   		}
	</script>


<div id="eventlist" class="el_editevent">

    <?php if ($this->params->def( 'show_page_title', 1 )) : ?>
    <h1 class="componentheading">
        <?php echo $this->params->get('page_title'); ?>
    </h1>
    <?php endif; ?>

    <form enctype="multipart/form-data" id="adminForm" action="<?php echo JRoute::_('index.php') ?>" method="post" class="form-validate">
        <div class="el_save_buttons floattext">
            <button type="submit" class="submit" onclick="return submitbutton('saveevent')">
        	    <?php echo JText::_('SAVE') ?>
        	</button>
        	<button type="reset" class="button cancel" onclick="submitbutton('cancelevent')">
        	    <?php echo JText::_('CANCEL') ?>
        	</button>
        </div>

        <p class="clear"></p>
        
    	<fieldset class="el_fldst_details">
    	
        	<legend><?php echo JText::_('NORMAL INFO'); ?></legend>

          <div class="el_title floattext">
              <label for="title">
                  <?php echo JText::_( 'TITLE' ).':'; ?>
              </label>

              <input class="inputbox required" type="text" id="title" name="title" value="<?php echo $this->escape($this->row->title); ?>" size="65" maxlength="60" />
          </div>
          
          <div class="el_category floattext">
          		<label for="categories" class="catsid">
                  <?php echo JText::_( 'CATEGORY' ).':';?>
              </label>
          		<?php	echo $this->lists['categories']; ?>
          </div>
		
		<?php echo $this->lists['venueselectbox']; ?>
		<?php echo $this->loadTemplate('jsscript'); ?>
		
        </fieldset>
		
    	<fieldset class="el_fldst_redform">

          <legend><?php echo JText::_('FORM'); ?></legend>

      		<div class="el_redformid floattext">
        			<p><strong><?php echo JText::_( 'REDFORM FORM ID' ).':'; ?></strong></p>
					<?php echo $this->lists['redforms']; ?>
      		</div>
      		<?php
      		//redform id end
      		?>
			<br clear="all" />
			
				<?php if (count($this->formfields) > 0) { ?>
				<p><strong><?php echo JText::_( 'REDFORM FORM SELECT FIELDS' ).':'; ?></strong></p>
					<?php
						$showfields = explode(",", $this->row->showfields);
						echo '<div class="formfields">';
						foreach ($this->formfields as $id => $field) {
							// echo '<tr><td>'.$field->field.'</td>';
							echo $field->field.'<br />';
							if (in_array($field->id, $showfields)) { ?>
								<label for="redform0"><?php echo JText::_( 'NO' ); ?></label>
									<input type="radio" name="showfield<?php echo $id; ?>" id="showfield<?php echo $field->id; ?>" value="0"  />
					
									<br class="clear" />
					
							    <label for="redform1"><?php echo JText::_( 'YES' ); ?></label>
								<input type="radio" name="showfield<?php echo $id; ?>" id="showfield<?php echo $field->id; ?>" value="1" checked="checked" />
							<?php }
							else { ?>
								<label for="redform0"><?php echo JText::_( 'NO' ); ?></label>
									<input type="radio" name="showfield<?php echo $id; ?>" id="showfield<?php echo $field->id; ?>" value="0" checked="checked" />
					
									<br class="clear" />
					
							    <label for="redform1"><?php echo JText::_( 'YES' ); ?></label>
								<input type="radio" name="showfield<?php echo $id; ?>" id="showfield<?php echo $field->id; ?>" value="1" />
							<?php }
							echo '<br clear="all" />';
						}
						echo '</div>';
					?>
				<?php } ?>
			
    	</fieldset>
	
	<fieldset class="el_fldst_submission">

          <legend><?php echo JText::_('SUBMISSION'); ?></legend>

          <div class="el_register floattext">
              <p><strong><?php echo JText::_( 'ENABLE ACTIVATION' ).':'; ?></strong></p>

              <?php if ($this->row->activate == 0) { ?>
					<label for="activate0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="activate" id="activate0" value="0" checked="checked" />
					
					<br class="clear" />
	
			    <label for="activate1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="activate" id="activate1" value="1" />
				<?php } 
				else {?>
        			<label for="activate0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="activate" id="activate0" value="0" />
					
					<br class="clear" />
	
			    <label for="activate1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="activate" id="activate1" value="1" checked="checked" />
				<?php } ?>

          </div>
      		<?php
      		//redform usage end

      		?>
      		<div class="el_register floattext">
        			<p><strong><?php echo JText::_( 'ENABLE NOTIFICATION' ).':'; ?></strong></p>
				<?php if ($this->row->notify == 0) { ?>
					<label for="notify0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="notify" id="notify0" value="0" checked="checked" />
					
					<br class="clear" />
	
			    <label for="notify1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="notify" id="notify1" value="1" />
				<?php } 
				else {?>
        			<label for="notify0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="notify" id="notify0" value="0" />
					
					<br class="clear" />
	
			    <label for="notify1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="notify" id="notify1" value="1" checked="checked" />
				<?php } ?>
      		</div>
      		<?php
      		//redform id end
      		?>
			<br clear="all" />
			
			<div class="el_notify_subject floattext">
        			<p><strong><?php echo JText::_( 'NOTIFY SUBJECT' ).':'; ?></strong></p>

        			<input class="inputbox" name="notify_subject" value="<?php echo $this->row->notify_subject; ?>" size="45" id="notify_subject" />
        			
      		</div>
			
			<div class="el_notify_body floattext">
        			<p><strong><?php echo JText::_( 'NOTIFY BODY' ).':'; ?></strong></p>
				
				<?php echo $this->editor->display( 'notify_body',  $this->row->notify_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
        			
      		</div>
			
			<div class="el_notify_confirm_subject floattext">
        			<p><strong><?php echo JText::_( 'NOTIFY CONFIRM SUBJECT' ).':'; ?></strong></p>

        			<input class="inputbox" name="notify_confirm_subject" value="<?php echo $this->row->notify_confirm_subject; ?>" size="45" id="notify_confirm_subject" />
        			
      		</div>
			
			<div class="el_notify_confirm_body floattext">
        			<p><strong><?php echo JText::_( 'NOTIFY CONFIRM BODY' ).':'; ?></strong></p>
				
				<?php echo $this->editor->display( 'notify_confirm_body',  $this->row->notify_confirm_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
        			
      		</div>
    	</fieldset>
	
	<fieldset class="el_fldst_waitinglist">

          <legend><?php echo JText::_('WAITINGLIST'); ?></legend>
          
      	<div class="el_show_attendants floattext">
              <p><strong><?php echo JText::_( 'SHOW_ATTENDANTS_EDIT' ).':'; ?></strong></p>

              <?php if ($this->row->show_attendants == 0) { ?>
					<label for="registra0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="show_attendants" id="show_attendants0" value="0" checked="checked" />
					
					<br class="clear" />
	
			    <label for="show_attendants1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="show_attendants" id="show_attendants1" value="1" />
				<?php } 
			else {?>
        			<label for="show_attendants0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="show_attendants" id="show_attendants0" value="0" />
					
					<br class="clear" />
	
			    <label for="show_attendants1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="show_attendants" id="show_attendants1" value="1" checked="checked" />
			<?php } ?>
          </div>
          
          <div class="el_show_waitinglist floattext">
              <p><strong><?php echo JText::_( 'SHOW_WAITINGLIST_EDIT' ).':'; ?></strong></p>

              <?php if ($this->row->show_waitinglist == 0) { ?>
					<label for="registra0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="show_waitinglist" id="show_waitinglist0" value="0" checked="checked" />
					
					<br class="clear" />
	
			    <label for="show_waitinglist1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="show_waitinglist" id="show_waitinglist1" value="1" />
				<?php } 
			else {?>
        			<label for="show_waitinglist0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="show_waitinglist" id="show_waitinglist0" value="0" />
					
					<br class="clear" />
	
			    <label for="show_waitinglist1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="show_waitinglist" id="show_waitinglist1" value="1" checked="checked" />
			<?php } ?>
          </div>

			<div class="el_notify_on_list_subject floattext">
        			<p><strong><?php echo JText::_( 'NOTIFY ON LIST SUBJECT' ).':'; ?></strong></p>

        			<input class="inputbox" name="notify_on_list_subject" value="<?php echo $this->row->notify_on_list_subject; ?>" size="45" id="notify_on_list_subject" />
        			
      		</div>
			
			<div class="el_notify_on_list_body floattext">
        			<p><strong><?php echo JText::_( 'NOTIFY ON LIST BODY' ).':'; ?></strong></p>
				
				<?php echo $this->editor->display( 'notify_on_list_body',  $this->row->notify_on_list_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
        			
      		</div>
			
			<div class="el_notify_off_list_subject floattext">
        			<p><strong><?php echo JText::_( 'NOTIFY OFF LIST SUBJECT' ).':'; ?></strong></p>

        			<input class="inputbox" name="notify_off_list_subject" value="<?php echo $this->row->notify_off_list_subject; ?>" size="45" id="notify_off_list_subject" />
        			
      		</div>
			
			<div class="el_notify_off_list_body floattext">
        			<p><strong><?php echo JText::_( 'NOTIFY OFF LIST BODY' ).':'; ?></strong></p>
				
        			<?php echo $this->editor->display( 'notify_off_list_body',  $this->row->notify_off_list_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
      		</div>
    	</fieldset>
	
    	<fieldset class="el_fldst_registration">

          <legend><?php echo JText::_('REGISTRATION'); ?></legend>

          <div class="el_register floattext">
              <p><strong><?php echo JText::_( 'SUBMIT REGISTER' ).':'; ?></strong></p>

              <?php if ($this->row->registra == 0) { ?>
					<label for="registra0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="registra" id="registra0" value="0" checked="checked" />
					
					<br class="clear" />
	
			    <label for="registra1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="registra" id="registra1" value="1" />
				<?php } 
			else {?>
        			<label for="registra0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="registra" id="registra0" value="0" />
					
					<br class="clear" />
	
			    <label for="registra1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="registra" id="registra1" value="1" checked="checked" />
			<?php } ?>
          </div>
      	<div class="el_unregister floattext">
        			<p><strong><?php echo JText::_( 'SUBMIT UNREGISTER' ).':'; ?></strong></p>

			<?php if ($this->row->unregistra == 0) { ?>
					<label for="unregistra0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="unregistra" id="unregistra0" value="0" checked="checked" />
					
					<br class="clear" />
	
			    <label for="unregistra1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="unregistra" id="unregistra1" value="1" />
				<?php } 
			else {?>
        			<label for="unregistra0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="unregistra" id="unregistra0" value="0" />
					
					<br class="clear" />
	
			    <label for="unregistra1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="unregistra" id="unregistra1" value="1" checked="checked" />
			<?php } ?>
		</div>
		<br clear="all" />
          <div class="el_register floattext">
              <p><strong><?php echo JText::_( 'CREATE JOOMLA USER' ).':'; ?></strong></p>

             <?php if ($this->row->juser == 0) { ?>
					<label for="juser0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="juser" id="juser0" value="0" checked="checked" />
					
					<br class="clear" />
	
			    <label for="juser1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="juser" id="juser1" value="1" />
				<?php } 
				else {?>
        			<label for="juser0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="juser" id="juser0" value="0" />
					
					<br class="clear" />
	
			    <label for="juser1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="juser" id="juser1" value="1" checked="checked" />
			<?php } ?>
          </div>
      		<div class="el_unregister floattext">
        			<p><strong><?php echo JText::_( 'SHOW NAMES FRONTEND' ).':'; ?></strong></p>

            	<?php if ($this->row->show_names == 0) { ?>
					<label for="show_names0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="show_names" id="show_names0" value="0" checked="checked" />
					
					<br class="clear" />
	
			    <label for="show_names1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="show_names" id="show_names1" value="1" />
				<?php } 
				else {?>
        			<label for="show_names0"><?php echo JText::_( 'NO' ); ?></label>
					<input type="radio" name="show_names" id="show_names0" value="0" />
					
					<br class="clear" />
	
			    <label for="show_names1"><?php echo JText::_( 'YES' ); ?></label>
				<input type="radio" name="show_names" id="show_names1" value="1" checked="checked" />
				<?php } ?>
      		</div>
    	</fieldset>
		<?php if (0) { ?>
			<fieldset class="el_fldst_recurrence">
	
			  <legend><?php echo JText::_('RECURRENCE'); ?></legend>
	
			  <div class="recurrence_select floattext">
				  <label for="recurrence_select"><?php echo JText::_( 'RECURRENCE' ); ?>:</label>
					<select id="recurrence_select" name="recurrence_select" size="1">
					  <option value="0"><?php echo JText::_( 'NOTHING' ); ?></option>
						<option value="1"><?php echo JText::_( 'DAYLY' ); ?></option>
						<option value="2"><?php echo JText::_( 'WEEKLY' ); ?></option>
						<option value="3"><?php echo JText::_( 'MONTHLY' ); ?></option>
						<option value="4"><?php echo JText::_( 'WEEKDAY' ); ?></option>
					</select>
			  </div>
	
			  <div class="recurrence_output floattext">
					<label id="recurrence_output">&nbsp;</label>
				  <div id="counter_row" style="display:none;">
					  <label for="recurrence_counter"><?php echo JText::_( 'RECURRENCE COUNTER' ); ?>:</label>
					  <div class="el_date>"><?php echo JHTML::_('calendar', ($this->row->recurrence_counter <> '0000-00-00') ? $this->row->recurrence_counter : JText::_( 'UNLIMITED' ), "recurrence_counter", "recurrence_counter"); ?>
						<a href="#" onclick="include_unlimited('<?php echo JText::_( 'UNLIMITED' ); ?>'); return false;"><img src="components/com_redevent/assets/images/unlimited.png" width="16" height="16" alt="<?php echo JText::_( 'UNLIMITED' ); ?>" /></a>
					</div>
				  </div>
			  </div>
	
				<input type="hidden" name="recurrence_number" id="recurrence_number" value="<?php echo $this->row->recurrence_number; ?>" />
				<input type="hidden" name="recurrence_type" id="recurrence_type" value="<?php echo $this->row->recurrence_type; ?>" />
		<?php } ?>

          <script type="text/javascript">
        	<!--
        	  var $select_output = new Array();
        		$select_output[1] = "<?php echo JText::_( 'OUTPUT DAY' ); ?>";
        		$select_output[2] = "<?php echo JText::_( 'OUTPUT WEEK' ); ?>";
        		$select_output[3] = "<?php echo JText::_( 'OUTPUT MONTH' ); ?>";
        		$select_output[4] = "<?php echo JText::_( 'OUTPUT WEEKDAY' ); ?>";

        		var $weekday = new Array();
        		$weekday[0] = "<?php echo JText::_( 'MONDAY' ); ?>";
        		$weekday[1] = "<?php echo JText::_( 'TUESDAY' ); ?>";
        		$weekday[2] = "<?php echo JText::_( 'WEDNESDAY' ); ?>";
        		$weekday[3] = "<?php echo JText::_( 'THURSDAY' ); ?>";
        		$weekday[4] = "<?php echo JText::_( 'FRIDAY' ); ?>";
        		$weekday[5] = "<?php echo JText::_( 'SATURDAY' ); ?>";
        		$weekday[6] = "<?php echo JText::_( 'SUNDAY' ); ?>";

        		var $before_last = "<?php echo JText::_( 'BEFORE LAST' ); ?>";
        		var $last = "<?php echo JText::_( 'LAST' ); ?>";

        		// start_recurrencescript();
        	-->
            </script>

    	</fieldset>

    	<?php if (( $this->elsettings->imageenabled == 2 ) || ($this->elsettings->imageenabled == 1)) : ?>
    	<fieldset class="el_fldst_image">
      	  <legend><?php echo JText::_('IMAGE'); ?></legend>
      		<?php
          if ($this->row->datimage) :
      		    echo ELOutput::flyer( $this->row, $this->dimage, 'event' );
      		else :
      		    echo JHTML::_('image', 'components/com_redevent/assets/images/noimage.png', JText::_('NO IMAGE'), array('class' => 'modal'));
      		endif;
        	?>
          <label for="userfile"><?php echo JText::_('IMAGE'); ?></label>
      		<input class="inputbox <?php echo $this->elsettings->imageenabled == 2 ? 'required' : ''; ?>" name="userfile" id="userfile" type="file" />
      		<small class="editlinktip hasTip" title="<?php echo JText::_( 'NOTES' ); ?>::<?php echo JText::_('MAX IMAGE FILE SIZE').' '.$this->elsettings->sizelimit.' kb'; ?>">
      		    <?php echo $this->infoimage; ?>
      		</small>
              <!--<div class="el_cur_image"><?php echo JText::_( 'CURRENT IMAGE' ); ?></div>
      		<div class="el_sel_image"><?php echo JText::_( 'SELECTED IMAGE' ); ?></div>-->
    	</fieldset>
    	<?php endif; ?>


    	<fieldset class="description">
      		<legend><?php echo JText::_('DESCRIPTION'); ?></legend>

      		<?php
      		//if usertyp min editor then editor else textfield
      		if ($this->editoruser) :
      			echo $this->editor->display('datdescription', $this->row->datdescription, '100%', '400', '70', '15', array('pagebreak', 'readmore') );
      		else :
      		?>
      		<textarea style="width:100%;" rows="10" name="datdescription" class="inputbox" wrap="virtual" onkeyup="berechne(this.form)"><?php echo $this->row->datdescription; ?></textarea><br />
      		<?php echo JText::_( 'NO HTML' ); ?><br />
      		<input disabled value="<?php echo $this->elsettings->datdesclimit; ?>" size="4" name="zeige" /><?php echo JText::_( 'AVAILABLE' ); ?><br />
      		<a href="javascript:rechne(document.adminForm);"><?php echo JText::_( 'REFRESH' ); ?></a>
      		<?php endif; ?>
    	</fieldset>

      <div class="el_save_buttons floattext">
          <button type="submit" class="submit" onclick="return submitbutton('saveevent')">
        	    <?php echo JText::_('SAVE') ?>
        	</button>
        	<button type="reset" class="button cancel" onclick="submitbutton('cancelevent')">
        	    <?php echo JText::_('CANCEL') ?>
        	</button>
      </div>
      
		<p class="clear">
    	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
    	<input type="hidden" name="returnid" value="<?php echo JRequest::getInt('returnid'); ?>" />
    	<input type="hidden" name="referer" value="<?php echo @$_SERVER['HTTP_REFERER']; ?>" />
    	<input type="hidden" name="created" value="<?php echo $this->row->created; ?>" />
    	<input type="hidden" name="author_ip" value="<?php echo $this->row->author_ip; ?>" />
    	<input type="hidden" name="created_by" value="<?php echo $this->row->created_by; ?>" />
    	<input type="hidden" name="curimage" value="<?php echo $this->row->datimage; ?>" />
    	<?php echo JHTML::_( 'form.token' ); ?>
    	<input type="hidden" name="task" value="" />
    	</p>
    </form>

    <p class="copyright">
    	<?php echo ELOutput::footer( ); ?>
    </p>

</div>
<script type="text/javascript" charset="utf-8">
	jQuery("input[name='adddatetime']").bind('click', function() {
		/* Get some values */
		var random = jQuery.random(<?php echo time(); ?>);
		var parentid = 'locid1';
		var childvalue = 1;
		
		/* Create the div to hold the fields */
		var datetime = '<div id="datetimecontainer'+random+'" style="display: block;">';
		datetime += '<input type="button" name="removedatetime" value="<?php echo JText::_('SHOW_HIDE_DATE_TIME'); ?>" onClick=\'jQuery("#datetime'+childvalue+'-'+random+'").toggle("slideUp");\'/>';
		datetime += '<input type="button" name="removedatetime" value="<?php echo JText::_('REMOVE_DATE_TIME'); ?>" onClick=\'removeDateTimeFields('+random+');\'/>';
		datetime += '<br />';
		datetime += '<div id="datetime'+childvalue+'-'+random+'"></div>';
		jQuery(datetime).appendTo("div#locid"+childvalue);

		var dates = '<div class="el_startdate floattext"><label for="dates'+random+'"><?php echo JText::_('DATE'); ?></label>';
		dates += '<input type="text" id="dates'+random+'" name="'+parentid+'['+random+'][dates]" value="" /><img id="dates'+random+'_img" class="calendar" alt="calendar" src="/templates/system/images/calendar.png"/>';
		dates += '</div>';
		dates += '<div class="el_enddate floattext"><label for="enddates'+random+'"><?php echo JText::_('ENDDATE'); ?></label>';
		dates += '<input type="text" id="enddates'+random+'" name="'+parentid+'['+random+'][enddates]" value="" /><img id="enddates'+random+'_img" class="calendar" alt="calendar" src="/templates/system/images/calendar.png"/>';
		dates += '</div>';
		dates += '<div class="el_date el_starttime floattext"><label for="times'+random+'"><?php echo JText::_('TIME'); ?></label>';
		dates += '<input type="text" id="times'+random+'" name="'+parentid+'['+random+'][times]" value="" />';
		dates += '</div>';
		dates += '<div class="el_date el_endtime floattext"><label for="endtimes'+random+'"><?php echo JText::_('ENDTIME'); ?></label>';
		dates += '<input type="text" id="endtimes'+random+'" name="'+parentid+'['+random+'][endtimes]" value="" />';
		dates += '</div>';

		/* Add the fields */
		jQuery("div#datetime"+childvalue+"-"+random).append(dates);
		
		/* Add the date picker */
		createDatePicker("dates"+random);
		createDatePicker("enddates"+random);
	});
	
	function createDatePicker(id) {
		Calendar.setup({
			inputField     :    id,     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    id+"_img",  // trigger for the calendar (button ID)
			align          :    "Tl",           // alignment (defaults to "Bl")
			singleClick    :    true
			});
	}

	function removeDateTimeFields(childvalue) {
		if (confirm('<?php echo JText::_('REMOVE_DATE_TIME_BLOCK'); ?>')) {
			jQuery("#datetimecontainer"+childvalue).remove();
		}
	}

	jQuery("input[name='showalldatetime']").bind('click', function() {
		var parentid = jQuery(this).parent().parent().attr("id");
		var childitem = jQuery("#"+parentid).children().get(0);
		var childvalue = jQuery(childitem).val();
		jQuery("[id^='datetime"+childvalue+"']").each(function(i) {
			jQuery(this).toggle();

		})

	});
</script>
<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>