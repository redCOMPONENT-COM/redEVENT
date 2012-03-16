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
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<script type="text/javascript">
	Window.onDomReady(function() {
		var form = document.getElementById('venueForm');
		var map = form.getElementById('map1');
		
		if(map.checked) {
			addrequired();
		}
	});
	
	function addrequired() 
	{		
		var form = document.getElementById('venueForm');
		
		$(form.street).addClass('required');
		$(form.plz).addClass('required');
		$(form.city).addClass('required');
		$(form.country).addClass('required');
	}
	
	function removerequired() 
	{		
		var form = document.getElementById('venueForm');
		
		$(form.street).removeClass('required');
		$(form.plz).removeClass('required');
		$(form.city).removeClass('required');
		$(form.country).removeClass('required');
	}

	function submitbutton( pressbutton ) 
	{
		if (pressbutton == 'cancelvenue') {
			elsubmitform( pressbutton );
			return;
		}

		var form = document.getElementById('venueForm');
		var validator = document.formvalidator;
		var venue = $(form.venue).getValue();
		venue.replace(/\s/g,'');
		
		var map = form.getElementById('map1');
		var streetcheck = $(form.street).hasClass('required');
	
		//workaround cause validate strict doesn't allow and operator
		//and ie doesn't understand CDATA properly
		if (map.checked) {
			if(!streetcheck) {  
				addrequired();
			}
		}

		if (!map.checked) {
			if(streetcheck) {  
				removerequired();
			}
		}

		if ( venue.length==0 ) {
   			alert("<?php echo JText::_('COM_REDEVENT_ERROR_ADD_VENUE', true ); ?>");
   			validator.handleResponse(false,form.venue);
   			form.venue.focus();
   			return false;
   		} else if ( validator.validate(form.street) === false) {
   			alert("<?php echo JText::_('COM_REDEVENT_ERROR_ADD_STREET', true ); ?>");
   			validator.handleResponse(false,form.street);
   			form.street.focus();
   			return false;
		} else if ( validator.validate(form.plz) === false) {
   			alert("<?php echo JText::_('COM_REDEVENT_ERROR_ADD_ZIP', true ); ?>");
   			validator.handleResponse(false,form.plz);
   			form.plz.focus();
   			return false;
  		} else if ( validator.validate(form.city) === false) {
  			alert("<?php echo JText::_('COM_REDEVENT_ERROR_ADD_CITY', true ); ?>");
  			validator.handleResponse(false,form.city);
  			form.city.focus();
  			return false;
		} else if ( validator.validate(form.country) === false) {
   			alert("<?php echo JText::_('COM_REDEVENT_ERROR_ADD_COUNTRY', true ); ?>");
   			validator.handleResponse(false,form.country);
   			form.country.focus();
   			return false;
  		} else {
  			<?php
			// JavaScript for extracting editor text
			echo $this->editor->save( 'locdescription' );
			?>
			elsubmitform(pressbutton);

			return true;
		}
	}
	
	//joomla submitform needs form name
	function elsubmitform(pressbutton)
	{			
		var form = document.getElementById('venueForm');
		if (pressbutton) {
			form.task.value=pressbutton;
		}
		if (typeof form.onsubmit == "function") {
			form.onsubmit();
		}
		form.submit();
	}

	var tastendruck = false;
	
	function rechne(restzeichen)
	{
		maximum = <?php echo $this->params->get('max_description', 1000); ?>;

		if (restzeichen.locdescription.value.length > maximum) {
			restzeichen.locdescription.value = restzeichen.locdescription.value.substring(0, maximum);
			links = 0;
		} else {
    	links = maximum - restzeichen.locdescription.value.length;
    }
	    
 		restzeichen.zeige.value = links;
  }

  function berechne(restzeichen)
  {
  	tastendruck = true;
  	rechne(restzeichen);
	}

	// for pinpoint map
	
    var sApply = "<?php echo JText::_('COM_REDEVENT_APPLY'); ?>";
    var sClose = "<?php echo JText::_('COM_REDEVENT_CLOSE'); ?>";
    var sMove = "<?php echo JText::_('COM_REDEVENT_MOVEMARKERHERE'); ?>";
    var sLatitude = "<?php echo JText::_('COM_REDEVENT_LATITUDE'); ?>";
    var sLongitude = "<?php echo JText::_('COM_REDEVENT_LONGITUDE'); ?>";
    var sTitle = "<?php echo JText::_('COM_REDEVENT_PINPOINTTITLE'); ?>";
</script>


<div id="redevent" class="el_editvenue">

    <?php if ($this->params->def( 'show_page_title', 1 )) : ?>
    <h1 class="componentheading">
        <?php echo $this->params->get('page_title'); ?>
    </h1>
    <?php endif; ?>

    <form enctype="multipart/form-data" id="venueForm" action="<?php echo JRoute::_('index.php') ?>" method="post" class="form-validate">

        <div class="el_save_buttons floattext">
  			<button type="button" onclick="return submitbutton('savevenue')">
  				<?php echo JText::_('COM_REDEVENT_SAVE') ?>
  			</button>
  			<button type="reset" onclick="return submitbutton('cancelvenue')">
  				<?php echo JText::_('COM_REDEVENT_CANCEL') ?>
  			</button>
		</div>

		 <p class="clear"></p>

      	<fieldset class="el_fldst_address">

            <legend><?php echo JText::_('COM_REDEVENT_ADDRESS'); ?></legend>

						<table class="fieldstable">
							<tbody>
								<tr>
									<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_VENUE'); ?>">
                		<label for="venue"><?php echo JText::_('COM_REDEVENT_VENUE' ).':'; ?></label>
                	</td>
                	<td>
                		<input class="inputbox required" type="text" name="venue" id="venue" value="<?php echo $this->escape($this->row->venue); ?>" size="55" maxlength="50" />
                	</td>
                </tr>     
                  
								<tr>
									<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_Category'); ?>">
                		<label for="categories"><?php echo JText::_('COM_REDEVENT_Category' ).':'; ?></label>
                	</td>
                	<td>
                		<?php echo $this->lists['categories']; ?>
                	</td>
                </tr>    
                                   
						<?php if ($this->canpublish): ?>
								<tr>
									<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_Published'); ?>">
                		<label for="published"><?php echo JText::_('COM_REDEVENT_Published' ).':'; ?></label>
                	</td>
                	<td>
										<?php echo $this->lists['published'] ?>
                	</td>
                </tr>                        
						<?php endif; ?>

								<tr>
									<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_VENUE_EDIT_COMPANY_LABEL').'::'.JText::_('COM_REDEVENT_VENUE_EDIT_COMPANY_TIP'); ?>">
                		<label for="company"><?php echo JText::_( 'COM_REDEVENT_VENUE_EDIT_COMPANY_LABEL' ).':'; ?></label>
                	</td>
                	<td>
                		<input class="inputbox" type="text" name="company" id="company" value="<?php echo $this->escape($this->row->company); ?>" size="55" maxlength="200" />
                	</td>
                </tr>

								<tr>
									<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_STREET'); ?>">
                		<label for="street"><?php echo JText::_('COM_REDEVENT_STREET' ).':'; ?></label>
                	</td>
                	<td>
                		<input class="inputbox" type="text" name="street" id="street" value="<?php echo $this->escape($this->row->street); ?>" size="55" maxlength="50" />
                	</td>
                </tr>

								<tr>
									<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_ZIP'); ?>">
										<label for="plz"><?php echo JText::_('COM_REDEVENT_ZIP' ).':'; ?></label>
                	</td>
                	<td>
                		<input class="inputbox" type="text" name="plz" id="plz" value="<?php echo $this->escape($this->row->plz); ?>" size="15" maxlength="10" />
                	</td>
                </tr>

								<tr>
									<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_CITY'); ?>">
										<label for="city"><?php echo JText::_('COM_REDEVENT_CITY' ).':'; ?></label>
                	</td>
                	<td>
                		<input class="inputbox" type="text" name="city" id="city" value="<?php echo $this->escape($this->row->city); ?>" size="55" maxlength="50" />
                	</td>
                </tr>

								<tr>
									<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_STATE'); ?>">
										<label for="state"><?php echo JText::_('COM_REDEVENT_STATE' ).':'; ?></label>
                	</td>
                	<td>
                		<input class="inputbox" type="text" name="state" id="state" value="<?php echo $this->escape($this->row->state); ?>" size="55" maxlength="50" />
                	</td>
                </tr>

								<tr>
									<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_COUNTRY'); ?>">
										<label for="country"><?php echo JText::_('COM_REDEVENT_COUNTRY' ).':'; ?></label>
                	</td>
                	<td>
                		<input class="inputbox" type="text" name="country" id="country" value="<?php echo $this->row->country; ?>" size="3" maxlength="2" />
                		<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_NOTES' ); ?>::<?php echo JText::_('COM_REDEVENT_COUNTRY_HINT'); ?>">
                		<?php echo $this->infoimage; ?>
                		</span>
                	</td>
                </tr>

								<tr>
									<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_WEBSITE'); ?>">
										<label for="url"><?php echo JText::_('COM_REDEVENT_WEBSITE' ).':'; ?></label>
                	</td>
                	<td>
                		<input class="inputbox" name="url" id="url" type="text" value="<?php echo $this->escape($this->row->url); ?>" size="55" maxlength="199" />&nbsp;
		                <span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_NOTES' ); ?>::<?php echo JText::_('COM_REDEVENT_WEBSITE_HINT'); ?>">
		                		<?php echo $this->infoimage; ?>
		                </span>
                	</td>
                </tr>

		            <?php if ( $this->params->get('showmapserv', 1) ) : ?>
								<tr>
									<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_ENABLE_MAP'); ?>">
										<label for="map"><?php echo JText::_('COM_REDEVENT_ENABLE_MAP' ).':'; ?></label>
                	</td>
                	<td>
                		<label for="map0"><?php echo JText::_('COM_REDEVENT_no' ); ?></label>
		                <input type="radio" name="map" id="map0" onchange="removerequired();" value="0" <?php echo $this->row->map == 0 ? 'checked="checked"' : ''; ?> class="inputbox" />
		                <br class="clear" />
		              	<label for="map1"><?php echo JText::_('COM_REDEVENT_yes' ); ?></label>
		              	<input type="radio" name="map" id="map1" onchange="addrequired();" value="1" <?php echo $this->row->map == 1 ? 'checked="checked"' : ''; ?> class="inputbox" />
                    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_NOTES' ); ?>::<?php echo JText::_('COM_REDEVENT_ADDRESS_NOTICE'); ?>">
                        <?php echo $this->infoimage; ?>
                    </span>
                	</td>
                </tr>
						    <tr>
						      <td>
						        <label for="latitude">
						          <?php echo JText::_( 'COM_REDEVENT_LATITUDE' ).':'; ?>
						        </label>
						      </td>
						      <td>
						        <input class="inputbox" name="latitude" id="latitude" value="<?php echo $this->row->latitude; ?>" size="14" maxlength="25" />
						              <span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_NOTES' ); ?>::<?php echo JText::_('COM_REDEVENT_LATITUDE_TIP'); ?>">
						          <?php echo JHTML::image('components/com_redevent/assets/images/marker_16.png', 'pinpoint', array('class' => 'pinpoint')); ?>
						          <?php echo $this->infoimage; ?>
						        </span>
						      </td>
						    </tr>
						    <tr>
						      <td>
						        <label for="longitude">
						          <?php echo JText::_( 'COM_REDEVENT_LONGITUDE' ).':'; ?>
						        </label>
						      </td>
						      <td>
						        <input class="inputbox" name="longitude" id="longitude" value="<?php echo $this->row->longitude; ?>" size="14" maxlength="25" />
						              <span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_NOTES' ); ?>::<?php echo JText::_('COM_REDEVENT_LONGITUDE_TIP'); ?>">
						          <?php echo JHTML::image('components/com_redevent/assets/images/marker_16.png', 'pinpoint', array('class' => 'pinpoint')); ?>
						          <?php echo $this->infoimage; ?>
						        </span>
						      </td>
						    </tr>
            		<?php endif; ?>
            	</tbody>
            </table>

        </fieldset>

      	<?php	if (( $this->params->get('edit_image', 1) == 2 ) || ($this->params->get('edit_image', 1) == 1)) :	?>
      	<fieldset class="el_fldst_image">

            <legend><?php echo JText::_('COM_REDEVENT_IMAGE'); ?></legend>

    		<?php
            if ($this->row->locimage) :
    				echo ELOutput::flyer( $this->row, $this->limage );
    		else :
      		    echo JHTML::_('image', 'components/com_redevent/assets/images/noimage.png', JText::_('COM_REDEVENT_NO_IMAGE'), array('class' => 'modal'));
    		endif;
      		?>

            <label for="userfile"><?php echo JText::_('COM_REDEVENT_IMAGE'); ?></label>
      			<input class="inputbox <?php echo $this->params->get('edit_image', 1) == 2 ? 'required' : ''; ?>" name="userfile" id="userfile" type="file" />
      			<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_NOTES' ); ?>::<?php echo JText::_('COM_REDEVENT_MAX_IMAGE_FILE_SIZE').' '.$this->elsettings->get('sizelimit', '100').' kb'; ?>">
      				<?php echo $this->infoimage; ?>
      			</span>

      			<!--<?php echo JText::_('COM_REDEVENT_CURRENT_IMAGE' );	?>
      			<?php echo JText::_('COM_REDEVENT_SELECTED_IMAGE' ); ?>-->

      	</fieldset>
      	<?php endif; ?>
          
      	<fieldset class="el_fldst_description">

          	<legend><?php echo JText::_('COM_REDEVENT_DESCRIPTION'); ?></legend>

        		<?php
        		//wenn usertyp min editor wird editor ausgegeben ansonsten textfeld
        		if ( $this->editoruser ) :
        			echo $this->editor->display('locdescription', $this->row->locdescription, '655', '400', '70', '15', array('pagebreak', 'readmore') );
        		else :
        		?>
      			<textarea style="width:100%;" rows="10" name="locdescription" class="inputbox" wrap="virtual" onkeyup="berechne(this.form)"></textarea><br />
      			<?php echo JText::_('COM_REDEVENT_NO_HTML'); ?><br />
      			<input disabled="disabled" value="<?php echo $this->params->get('max_description', 1000); ?>" size="4" name="zeige" /><?php echo JText::_('COM_REDEVENT_AVAILABLE')." "; ?><br />
      			<a href="javascript:rechne(document.venueForm);"><?php echo JText::_('COM_REDEVENT_REFRESH'); ?></a>

        		<?php	endif; ?>

      	</fieldset>
      	
				<?php if ($this->params->get('allow_attachments', 1)): ?>
				<?php echo $this->loadTemplate('attachments'); ?>
				<?php endif; ?>
				
      	<fieldset class="el_fldst_meta">

          	<legend><?php echo JText::_('COM_REDEVENT_METADATA_INFORMATION'); ?></legend>

						<table class="fieldstable">
							<tbody>
								<tr>
									<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_META_DESCRIPTION'); ?>">
              			<label for="metadesc"><?php echo JText::_('COM_REDEVENT_META_DESCRIPTION' ); ?></label>
                	</td>
                	<td>
                		<textarea class="inputbox" cols="40" rows="5" name="meta_description" id="metadesc" style="width:250px;"><?php echo  $this->row->meta_description; ?></textarea>
                	</td>
                </tr>
                
								<tr>
									<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_META_DESCRIPTION'); ?>">
              			<label for="metakey"><?php echo JText::_('COM_REDEVENT_META_KEYWORDS' ); ?></label>
                	</td>
                	<td>
                		<textarea class="inputbox" cols="40" rows="5" name="meta_keywords" id="metakey" style="width:250px;"><?php echo  $this->row->meta_keywords; ?></textarea>
                </tr>
							</tbody>
						</table>
            
    		<input type="button" class="button el_fright" value="<?php echo JText::_('COM_REDEVENT_ADD_VENUE_CITY' ); ?>" onclick="f=document.getElementById('venueForm');f.metakey.value=f.venue.value+', '+f.city.value+f.metakey.value;" />

      	</fieldset>

      	<div class="el_save_buttons floattext">
    		<button type="button" onclick="return submitbutton('savevenue')">
    			<?php echo JText::_('COM_REDEVENT_SAVE') ?>
    		</button>
    		<button type="reset" onclick="return submitbutton('cancelvenue')">
    			<?php echo JText::_('COM_REDEVENT_CANCEL') ?>
    		</button>
		</div>
		
		<p class="clear">
      	<input type="hidden" name="option" value="com_redevent" />
      	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
      	<input type="hidden" name="referer" value="<?php echo @$_SERVER['HTTP_REFERER']; ?>" />
      	<input type="hidden" name="created" value="<?php echo $this->row->created; ?>" />
      	<input type="hidden" name="curimage" value="<?php echo $this->row->locimage; ?>" />
      	<?php echo JHTML::_( 'form.token' ); ?>
      	<input type="hidden" name="task" value="" />
      	</p>

    </form>

</div>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>