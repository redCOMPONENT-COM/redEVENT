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

defined('_JEXEC') or die('Restricted access');
?>

<script language="javascript" type="text/javascript">
	function submitbutton(task)
	{
		var form = document.adminForm;
		var locdescription = <?php echo $this->editor->getContent( 'locdescription' ); ?>
		var needaddress = false; // if map is set in global configuration and user did not entrer coordinates

    // check if map is set to yes
    var map = $$('input[name=map]');

		if (map.length > 1 && map[1].checked == true) {			
			var longitude = $('longitude').getValue();
			var latitude  = $('latitude').getValue();
			if (!longitude || longitude == 0 || !latitude || latitude == 0) {
				needaddress = true;
			}
		}

		if (task == 'cancel') {
			submitform( task );
		} else if (form.venue.value == ""){
			alert( "<?php echo JText::_( 'ADD VENUE' ); ?>" );
			form.venue.focus();
		} else if (form.city.value == "" && needaddress){
			alert( "<?php echo JText::_( 'VENUE_MAP_NEED_DETAILS' ) .'\n'. JText::_( 'ADD CITY' ); ?>" );
			form.city.focus();
		} else if (form.street.value == "" && needaddress){
			alert( "<?php echo JText::_( 'VENUE_MAP_NEED_DETAILS' ) .'\n'. JText::_( 'ADD STREET' ); ?>" );
			form.street.focus();
		} else if (form.plz.value == "" && needaddress){
			alert( "<?php echo JText::_( 'VENUE_MAP_NEED_DETAILS' ) .'\n'. JText::_( 'ADD ZIP' ); ?>" );
			form.plz.focus();
		} else if (form.country.value == "" && needaddress){
			alert( "<?php echo JText::_( 'VENUE_MAP_NEED_DETAILS' ) .'\n'. JText::_( 'ADD COUNTRY' ); ?>" );
			form.country.focus();
		} else {
			<?php
			echo $this->editor->save( 'locdescription' );
			?>
			submitform( task );
		}
	}
	
    var sApply = "<?php echo JText::_('APPLY'); ?>";
    var sClose = "<?php echo JText::_('CLOSE'); ?>";
    var sMove = "<?php echo JText::_('MOVEMARKERHERE'); ?>";
    var sLatitude = "<?php echo JText::_('LATITUDE'); ?>";
    var sLongitude = "<?php echo JText::_('LONGITUDE'); ?>";
    var sTitle = "<?php echo JText::_('PINPOINTTITLE'); ?>";
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td valign="top">

	<table  class="adminform">
		<tr>
			<td>
				<label for="venue">
					<?php echo JText::_( 'VENUE' ).':'; ?>
				</label>
			</td>
			<td>
				<input class="inputbox" name="venue" id= "venue" value="<?php echo $this->row->venue; ?>" size="40" maxlength="100" />
			</td>
			<td>
				<label for="published">
					<?php echo JText::_( 'PUBLISHED' ).':'; ?>
				</label>
			</td>
			<td>
				<?php
				$html = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $this->row->published );
				echo $html;
				?>
			</td>
		</tr>
		<tr>
			<td>
				<label for="alias">
					<?php echo JText::_( 'Alias' ).':'; ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="alias" id="alias" size="40" maxlength="100" value="<?php echo $this->row->alias; ?>" />
			</td>
		</tr>
    <tr>
      <td>
        <label for="categories">
          <?php echo JText::_( 'CATEGORY' ).':'; ?>
        </label>
      </td>
      <td>
        <?php
        echo $this->lists['categories'];
        ?>
      </td>
    </tr>
	</table>
			<table class="adminform">
				<tr>
					<td>
						<?php
						echo $this->editor->display( 'locdescription',  $this->row->locdescription, '100%;', '550', '75', '20', array('pagebreak', 'readmore') ) ;
						?>
					</td>
				</tr>
				</table>
			</td>
			<td valign="top" width="320px" style="padding: 7px 0 0 5px">
		<?php
		echo $this->pane->startPane('det-pane');
		$infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) );
		$title = JText::_( 'ADDRESS' );
		echo $this->pane->startPanel( $title, 'address' );

		//Set the info image
		$infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) );
		?>
	<table>
		<tr>
			<td>
				<label for="street">
					<?php echo JText::_( 'STREET' ).':'; ?>
				</label>
			</td>
			<td>
				<input class="inputbox" name="street" id="street" value="<?php echo $this->row->street; ?>" size="35" maxlength="50" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="plz">
					<?php echo JText::_( 'ZIP' ).':'; ?>
				</label>
			</td>
			<td>
				<input class="inputbox" name="plz" id="plz" value="<?php echo $this->row->plz; ?>" size="15" maxlength="10" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="city">
					<?php echo JText::_( 'CITY' ).':'; ?>
				</label>
			</td>
			<td>
				<input class="inputbox" name="city" id="city" value="<?php echo $this->row->city; ?>" size="35" maxlength="50" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="state">
					<?php echo JText::_( 'STATE' ).':'; ?>
				</label>
			</td>
			<td>
				<input class="inputbox" name="state" id="state" value="<?php echo $this->row->state; ?>" size="35" maxlength="50" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="country">
					<?php echo JText::_( 'COUNTRY' ).':'; ?>
				</label>
			</td>
			<td>
				<?php echo $this->lists['countries']; ?>

				<span class="editlinktip hasTip" title="<?php echo JText::_( 'NOTES' ); ?>::<?php echo JText::_('COUNTRY HINT'); ?>">
					<?php echo $infoimage; ?>
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<label for="url">
					<?php echo JText::_( 'WEBSITE' ).':'; ?>
				</label>
			</td>
			<td>
				<input class="inputbox" name="url" id="url" value="<?php echo $this->row->url; ?>" size="30" maxlength="199" />&nbsp;

				<span class="editlinktip hasTip" title="<?php echo JText::_( 'NOTES' ); ?>::<?php echo JText::_('WEBSITE HINT'); ?>">
					<?php echo $infoimage; ?>
				</span>
			</td>
		</tr>
  </table>
  <?php if ( $this->settings->showmapserv != 0 ) { ?>
  <div id="setmap">
  <table>
    <tr>
      <td>
        <label for="map">
          <?php echo JText::_( 'ENABLE MAP' ).':'; ?>
        </label>
      </td>
      <td>
        <?php
                echo JHTML::_('select.booleanlist', 'map', 'class="inputbox"', $this->row->map );
              ?>
              &nbsp;
              <span class="editlinktip hasTip" title="<?php echo JText::_( 'NOTES' ); ?>::<?php echo JText::_('ADDRESS NOTICE'); ?>">
          <?php echo $infoimage; ?>
        </span>
      </td>
    </tr>
    <tr>
      <td>
        <label for="latitude">
          <?php echo JText::_( 'LATITUDE' ).':'; ?>
        </label>
      </td>
      <td>
        <input class="inputbox" name="latitude" id="latitude" value="<?php echo $this->row->latitude; ?>" size="14" maxlength="25" />
              <span class="editlinktip hasTip" title="<?php echo JText::_( 'NOTES' ); ?>::<?php echo JText::_('LATITUDE HINT'); ?>">
          <?php echo $infoimage; ?>
        </span>
      </td>
    </tr>
    <tr>
      <td>
        <label for="longitude">
          <?php echo JText::_( 'LONGITUDE' ).':'; ?>
        </label>
      </td>
      <td>
        <input class="inputbox" name="longitude" id="longitude" value="<?php echo $this->row->longitude; ?>" size="14" maxlength="25" />
              <span class="editlinktip hasTip" title="<?php echo JText::_( 'NOTES' ); ?>::<?php echo JText::_('LONGITUDE HINT'); ?>">
          <?php echo $infoimage; ?>
        </span>
      </td>
    </tr>
  </table>
  <div id="pinpointicon">
    <?php echo $this->pinpointicon; ?>
  </div>
  </div>
		<?php } ?>
	<?php
	echo $this->pane->endPanel();
	$title = JText::_( 'IMAGE' );
	echo $this->pane->startPanel( $title, 'image' );
	?>
	<table>
		<tr>
			<td>
				<label for="locimage">
					<?php echo JText::_( 'CHOOSE IMAGE' ).':'; ?>
				</label>
			</td>
			<td>
				<?php
					echo $this->imageselect;
				?>
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
				<img src="../images/M_images/blank.png" name="imagelib" id="imagelib" width="80" height="80" border="2" alt="Preview" />
				<script language="javascript" type="text/javascript">
				if ($('a_imagename').value !=''){
					var imname = $('a_imagename').value;
					jsimg='../images/redevent/venues/' + imname;
					$('imagelib').src= jsimg;
				}
				</script>
				<br />
				<br />
			</td>
		</tr>
	</table>
	<?php
	echo $this->pane->endPanel();
	$title = JText::_( 'ACCESS' );
	echo $this->pane->startPanel( $title, 'access' );
	?>
	<table>
		<tr>
			<td>
				<label for="private" class="hasTip" title="<?php echo JText::_('COM_REDEVENT_VENUE_PRIVATE_LABEL').'::'.JText::_('COM_REDEVENT_VENUE_PRIVATE_TIP'); ?>">
					<?php echo JText::_( 'COM_REDEVENT_VENUE_PRIVATE_LABEL' ).':'; ?>
				</label>
			</td>
			<td>
				<?php
				echo JHTML::_('select.booleanlist', 'private', '', $this->row->private);
				?>
			</td>
		</tr>
	</table>
	<?php
	echo $this->pane->endPanel();
	
	$title = JText::_( 'METADATA INFORMATION' );
	echo $this->pane->startPanel( $title, 'metadata' );
	?>
	<table>
		<tr>
			<td>
				<label for="metadesc">
					<?php echo JText::_( 'META DESCRIPTION' ); ?>:
				</label>
				<br />
				<textarea class="inputbox" cols="40" rows="5" name="meta_description" id="metadesc" style="width:300px;"><?php echo str_replace('&','&amp;',$this->row->meta_description); ?></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<label for="metakey">
					<?php echo JText::_( 'META KEYWORDS' ); ?>:
				</label>
				<br />
				<textarea class="inputbox" cols="40" rows="5" name="meta_keywords" id="metakey" style="width:300px;"><?php echo str_replace('&','&amp;',$this->row->meta_keywords); ?></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<input type="button" class="button" value="<?php echo JText::_( 'ADD VENUE CITY' ); ?>" onclick="f=document.adminForm;f.metakey.value=f.venue.value+', '+f.city.value+f.metakey.value;" />
			</td>
		</tr>
	</table>

		<?php
		echo $this->pane->endPanel();
		echo $this->pane->endPane();
		?>
		</td>
	</tr>
</table>

<?php
if ( $this->settings->showmapserv == 0 ) { ?>
	<input type="hidden" name="map" value="0" />
<?php
}
?>
	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_redevent" />
	<input type="hidden" name="controller" value="venues" />
	<input type="hidden" name="view" value="venue" />
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="created" value="<?php echo $this->row->created; ?>" />
	<input type="hidden" name="author_ip" value="<?php echo $this->row->author_ip; ?>" />
	<input type="hidden" name="created_by" value="<?php echo $this->row->created_by; ?>" />
	<input type="hidden" name="task" value="" />
</form>

<p class="copyright">
	<?php echo ELAdmin::footer( ); ?>
</p>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>