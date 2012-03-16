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

defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>
<?php JHTML::_('behavior.formvalidation'); ?>

<?php
$imagepath = '/administrator/components/com_redevent/assets/images/';
?>

<script language="javascript" type="text/javascript">
	window.addEvent('domready', function(){
		$('image').addEvent('change', function(){
			if ($('image').value != "") {
				$('img-preview').setStyle('visibility', 'visible');
				$('img-preview').src = imgpath+$('image').value;
			}
			else {
				$('img-preview').setStyle('visibility', 'hidden');
				$('img-preview').src = '';				
			}
		});
	});

	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}

    // do field validation
    var validator = document.formvalidator;
    if ( validator.validate(form.name) === false ){
      alert( "<?php echo JText::_('COM_REDEVENT_NAME_IS_REQUIRED', true ); ?>" );
    } else {
      submitform( pressbutton );
    }
	}

	var imgpath = "<?php echo JURI::root().$imagepath; ?>";
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col50">
<fieldset class="adminform"><legend><?php echo JText::_( 'COM_REDEVENT_PRICEGROUPS_PRICEGROUP' ); ?></legend>

<table class="admintable">
	<tr>
		<td width="100" align="right" class="key"><label for="name"><?php echo JText::_('COM_REDEVENT_Name' ); ?>:
		</label></td>
		<td><input class="text_area required" type="text" name="name" id="name"
			size="32" maxlength="250" value="<?php echo $this->object->name; ?>" />
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key"><label for="alias"><?php echo JText::_('COM_REDEVENT_Alias' ); ?>:
		</label></td>
		<td><input class="text_area" type="text" name="alias" id="alias"
			size="32" maxlength="250" value="<?php echo $this->object->alias; ?>" />
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="hasTip key" title="<?php echo JText::_( 'COM_REDEVENT_PRICEGROUPS_TOOLTIP' ).'::'.JText::_( 'COM_REDEVENT_PRICEGROUPS_TOOLTIP_TIP' ); ?>">
			<label for="tooltip"><?php echo JText::_( 'COM_REDEVENT_PRICEGROUPS_TOOLTIP' ); ?>:</label>
		</td>
		<td><textarea name="tooltip" id="tooltip"	cols="32" rows="4"><?php echo $this->object->tooltip; ?></textarea>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="hasTip key" title="<?php echo JText::_( 'COM_REDEVENT_PRICEGROUPS_ADMINONLY' ).'::'.JText::_( 'COM_REDEVENT_PRICEGROUPS_ADMINONLY_TIP' ); ?>">
			<label for="tooltip"><?php echo JText::_( 'COM_REDEVENT_PRICEGROUPS_ADMINONLY' ); ?>:</label>
		</td>
		<td><?php echo JHTML::_('select.booleanlist', 'adminonly', '', $this->object->adminonly); ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key"><label for="alias"><?php echo JText::_('COM_REDEVENT_Image' ); ?>:</label></td>
		<td>
			<?php	echo JHTML::_('list.images', 'image', $this->object->image, 'id="image"', $imagepath); ?>
			<img src="<?php echo JURI::root().$imagepath.$this->object->image; ?>" id="img-preview" border="0" alt="<?php echo JText::_('COM_REDEVENT_Preview' ); ?>" />
		</td>
	</tr>
	<tr>
		<td valign="top" align="right" class="key"><label for="ordering"> <?php echo JText::_('COM_REDEVENT_Ordering' ); ?>:
		</label></td>
		<td><?php echo $this->lists['ordering']; ?></td>
	</tr>
</table>
</fieldset>
</div>

<div class="clr"></div>

<input type="hidden" name="option" value="com_redevent" /> <input
	type="hidden" name="controller" value="pricegroups" /> <input
	type="hidden" name="cid[]" value="<?php echo $this->object->id; ?>" />
<input type="hidden" name="task" value="" /></form>