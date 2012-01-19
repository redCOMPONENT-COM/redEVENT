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
// Set toolbar items for the page
$edit		= JRequest::getVar('edit',true);
$text = !$edit ? JText::_('COM_REDEVENT_New' ) : JText::_('COM_REDEVENT_Edit' );
JToolBarHelper::title(   JText::_( 'COM_REDEVENT_PRICEGROUPS_PRICEGROUP' ).': <small><small>[ ' . $text.' ]</small></small>' );
JToolBarHelper::save();
JToolBarHelper::apply();
if (!$edit)  {
	JToolBarHelper::cancel();
} else {
	// for existing items the button is renamed `close`
	JToolBarHelper::cancel( 'cancel', 'Close' );
}

$imagepath = '/administrator/components/com_redevent/assets/images/';
?>

<script language="javascript" type="text/javascript">
	window.addEvent('domready', function(){

		$('image').addEvent('change', function(){
			if (this.get('value')) {
				$('imagelib').empty().adopt(
						new Element('img', {
							src: '../'+this.get('value'),
							class: 're-image-preview',
							alt: 'preview'
						}));
			}
			else {
				$('imagelib').empty();
			}
		}).fireEvent('change');
		
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

<table class="editevent">
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
		<td width="100" align="right" class="key">
			<?php echo $this->form->getLabel('image'); ?>
		</td>
		<td>
			<?php echo $this->form->getInput('image'); ?>
			<div class="clear"></div>
			<div id="imagelib"></div>
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