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

JHTML::_('behavior.mootools');
JHTML::_('behavior.tooltip');

$options = array(
		'onActive' => 'function(title, description){
        description.setStyle("display", "block");
        title.addClass("open").removeClass("closed");
    }',
		'onBackground' => 'function(title, description){
        description.setStyle("display", "none");
        title.addClass("closed").removeClass("open");
    }',
		'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
		'useCookie' => false, // this must not be a string. Don't use quotes.
);
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
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
			alert( "<?php echo JText::_('COM_REDEVENT_ADD_VENUE' ); ?>" );
			form.venue.focus();
		} else if (form.city.value == "" && needaddress){
			alert( "<?php echo JText::_('COM_REDEVENT_VENUE_MAP_NEED_DETAILS' ) .'\n'. JText::_('COM_REDEVENT_ADD_CITY' ); ?>" );
			form.city.focus();
		} else if (form.street.value == "" && needaddress){
			alert( "<?php echo JText::_('COM_REDEVENT_VENUE_MAP_NEED_DETAILS' ) .'\n'. JText::_('COM_REDEVENT_ADD_STREET' ); ?>" );
			form.street.focus();
		} else if (form.plz.value == "" && needaddress){
			alert( "<?php echo JText::_('COM_REDEVENT_VENUE_MAP_NEED_DETAILS' ) .'\n'. JText::_('COM_REDEVENT_ADD_ZIP' ); ?>" );
			form.plz.focus();
		} else if (form.country.value == "" && needaddress){
			alert( "<?php echo JText::_('COM_REDEVENT_VENUE_MAP_NEED_DETAILS' ) .'\n'. JText::_('COM_REDEVENT_ADD_COUNTRY' ); ?>" );
			form.country.focus();
		} else {
			<?php
			echo $this->editor->save( 'locdescription' );
			?>
			Joomla.submitform(task, document.id('adminForm'));
		}
	}

    var sApply = "<?php echo JText::_('COM_REDEVENT_APPLY'); ?>";
    var sClose = "<?php echo JText::_('COM_REDEVENT_CLOSE'); ?>";
    var sMove = "<?php echo JText::_('COM_REDEVENT_MOVEMARKERHERE'); ?>";
    var sLatitude = "<?php echo JText::_('COM_REDEVENT_LATITUDE'); ?>";
    var sLongitude = "<?php echo JText::_('COM_REDEVENT_LONGITUDE'); ?>";
    var sTitle = "<?php echo JText::_('COM_REDEVENT_PINPOINTTITLE'); ?>";
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-validate" >

	<div class="width-60 fltlft">
		<?php echo JHtml::_('tabs.start', 'tab_venue_id-'.$this->row->id, $options); ?>
		<?php echo JHtml::_('tabs.panel', JText::_('COM_REDEVENT_EVENT_INFO_TAB'), 'details'); ?>
		<fieldset class="panelform">
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('venue'); ?>
				<?php echo $this->form->getInput('venue'); ?></li>

				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>

				<li><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>

				<li><?php echo $this->form->getLabel('company'); ?>
				<?php echo $this->form->getInput('company'); ?></li>

				<li>
					<label for="categories"><?php echo JText::_('COM_REDEVENT_CATEGORY' ).':'; ?></label>
				<?php echo $this->lists['categories']; ?></li>
			</ul>
			<div class="clr"></div>
			<?php echo $this->form->getLabel('locdescription'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('locdescription'); ?>
		</fieldset>

		<?php echo JHtml::_('tabs.panel', JText::_('COM_REDEVENT_EVENT_ATTACHMENTS_TAB'), 'attachments'); ?>
			<?php echo $this->loadTemplate('attachments'); ?>
		<?php echo JHtml::_('tabs.end'); ?>
	</div>

	<div class="width-40 fltrt">
		<?php echo JHtml::_('sliders.start', 'venues-sliders-'.$this->row->id, $options); ?>

			<?php echo JHtml::_('sliders.panel', JText::_('COM_REDEVENT_ADDRESS'), 'address'); ?>
			<fieldset class="panelform">
				<ul class="adminformlist">
					<li><?php echo $this->form->getLabel('street'); ?>
					<?php echo $this->form->getInput('street'); ?></li>

					<li><?php echo $this->form->getLabel('plz'); ?>
					<?php echo $this->form->getInput('plz'); ?></li>

					<li><?php echo $this->form->getLabel('city'); ?>
					<?php echo $this->form->getInput('city'); ?></li>

					<li><?php echo $this->form->getLabel('state'); ?>
					<?php echo $this->form->getInput('state'); ?></li>

					<li><?php echo $this->form->getLabel('country'); ?>
					<?php echo $this->form->getInput('country'); ?></li>

					<li><?php echo $this->form->getLabel('url'); ?>
					<?php echo $this->form->getInput('url'); ?></li>
				</ul>
			</fieldset>
			<?php if ( $this->params->get('showmapserv',1)): ?>
			<div id="setmap">
				<fieldset class="panelform">
					<ul class="adminformlist">
						<li><?php echo $this->form->getLabel('map'); ?>
						<?php echo $this->form->getInput('map'); ?></li>

						<li><?php echo $this->form->getLabel('latitude'); ?>
						<?php echo $this->form->getInput('latitude'); ?></li>

						<li><?php echo $this->form->getLabel('longitude'); ?>
						<?php echo $this->form->getInput('longitude'); ?></li>
					</ul>
					<div id="pinpointicon">
						<?php echo $this->pinpointicon; ?>
					</div>
				</fieldset>
			</div>
			<?php endif; ?>

			<?php echo JHtml::_('sliders.panel', JText::_('COM_REDEVENT_IMAGE'), 'image'); ?>
			<fieldset class="panelform">
				<?php echo $this->form->getLabel('locimage'); ?>
				<?php echo $this->form->getInput('locimage'); ?>
			</fieldset>

			<?php echo JHtml::_('sliders.panel', JText::_('COM_REDEVENT_ACCESS'), 'access'); ?>
			<fieldset class="panelform">
				<?php echo $this->form->getLabel('private'); ?>
				<?php echo $this->form->getInput('private'); ?>
			</fieldset>

			<?php echo JHtml::_('sliders.panel', JText::_('COM_REDEVENT_METADATA_INFORMATION'), 'metadata'); ?>
			<table>
				<tr>
					<td>
						<label for="metadesc">
							<?php echo JText::_('COM_REDEVENT_META_DESCRIPTION' ); ?>:
						</label>
						<br />
						<textarea class="inputbox" cols="40" rows="5" name="meta_description" id="metadesc" style="width:300px;"><?php echo str_replace('&','&amp;',$this->row->meta_description); ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<label for="metakey">
							<?php echo JText::_('COM_REDEVENT_META_KEYWORDS' ); ?>:
						</label>
						<br />
						<textarea class="inputbox" cols="40" rows="5" name="meta_keywords" id="metakey" style="width:300px;"><?php echo str_replace('&','&amp;',$this->row->meta_keywords); ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<input type="button" class="button" value="<?php echo JText::_('COM_REDEVENT_ADD_VENUE_CITY' ); ?>" onclick="f=document.adminForm;f.metakey.value=f.venue.value+', '+f.city.value+f.metakey.value;" />
					</td>
				</tr>
			</table>

		<?php echo JHtml::_('sliders.end'); ?>
	</div>

   <!-- begin ACL definition-->
   <div class="clr"></div>
   <?php if (1 or $this->canDo->get('core.admin')): ?>
	   <div class="width-100 fltlft">
		   <?php echo JHtml::_('sliders.start', 'permissions-sliders-'.$this->row->id, array('useCookie'=>1)); ?>
		   <?php echo JHtml::_('sliders.panel', JText::_('COM_REDEVENT_VENUE_FIELDSET_RULES'), 'access-rules'); ?>
		   <fieldset class="panelform">
			   <?php echo $this->form->getLabel('rules'); ?>
			   <?php echo $this->form->getInput('rules'); ?>
		   </fieldset>
		   <?php echo JHtml::_('sliders.end'); ?>
	   </div>
   <?php endif; ?>
   <!-- end ACL definition-->

<?php
if ( $this->params->get('showmapserv', 1) == 0 ) { ?>
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

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
