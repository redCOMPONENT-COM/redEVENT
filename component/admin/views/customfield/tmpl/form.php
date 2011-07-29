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
$text = !$edit ? JText::_( 'New' ) : JText::_( 'Edit' );
JToolBarHelper::title(   JText::_( 'Custom field' ).': <small><small>[ ' . $text.' ]</small></small>' );
JToolBarHelper::save();
JToolBarHelper::apply();
if (!$edit)  {
	JToolBarHelper::cancel();
} else {
	// for existing items the button is renamed `close`
	JToolBarHelper::cancel( 'cancel', 'Close' );
}
?>

<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}

    // do field validation
    var validator = document.formvalidator;
    if ( validator.validate(form.name) === false ){
      alert( "<?php echo JText::_( 'NAME IS REQUIRED', true ); ?>" );
    } else {
      submitform( pressbutton );
    }
	}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col50">
<fieldset class="adminform"><legend><?php echo JText::_( 'Custom field' ); ?></legend>

<table class="admintable">
	<tr>
		<td width="100" align="right" class="key"><label for="name"><?php echo JText::_( 'Name' ); ?>:
		</label></td>
		<td><input class="text_area required" type="text" name="name" id="name"
			size="32" maxlength="250" value="<?php echo $this->object->name; ?>" />
		</td>
	</tr>
  <tr>
    <td width="100" align="right" class="key hasTip" title="<?php echo JText::_( 'Custom field Tag' ).'::'.JText::_('Custom field Tag tip'); ?>"><label for="tag"> <?php echo JText::_( 'Custom field Tag' ); ?>:
    </label></td>
    <td><input class="text_area required" type="text" name="tag" id="tag"
      size="32" maxlength="250" value="<?php echo $this->object->tag; ?>" />
    </td>
  </tr>
  <tr>
    <td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'Field for' ).'::'.JText::_('field for tip'); ?>"><label for="object_key"><?php echo JText::_( 'Field for' ); ?>:
    </label></td>
    <td><?php echo $this->lists['objects']; ?></td>
  </tr>
  <tr>
    <td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'custom field type' ).'::'.JText::_('custom field type tip'); ?>"><label for="type"><?php echo JText::_( 'custom field Type' ); ?>:
    </label></td>
    <td><?php echo ($this->object->id ? $this->object->type : $this->lists['types']); ?></td>
  </tr>
	<tr>
		<td valign="top" align="right" class="key"><label for="published"><?php echo JText::_( 'Published' ); ?>:</label>
		</td>
		<td><?php echo $this->lists['published']; ?></td>
	</tr>
	<tr>
		<td valign="top" align="right" class="key"><label for="ordering"> <?php echo JText::_( 'Ordering' ); ?>:
		</label></td>
		<td><?php echo $this->lists['ordering']; ?></td>
	</tr>
  <tr id="row-tooltip">
    <td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'Custom field Tooltip' ).'::'.JText::_('Custom field Tooltip tip'); ?>"><label for="tips"><?php echo JText::_( 'Custom field Tooltip' ); ?>:
    </label></td>
    <td><textarea name="tips" id="tips" rows="6" cols="20"><?php echo $this->object->tips; ?></textarea></td>
  </tr>
  <tr>
    <td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'Custom field Searchable' ).'::'.JText::_('Custom field Searchable tip'); ?>"><label for="searchable"><?php echo JText::_( 'Custom field Searchable' ); ?>:</label>
    </td>
    <td><?php echo $this->lists['searchable']; ?></td>
  </tr>
  <tr>
    <td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'Show in lists' ).'::'.JText::_('Show in lists tip'); ?>"><label for="in_lists"><?php echo JText::_( 'Show in lists' ); ?>:</label>
    </td>
    <td><?php echo $this->lists['in_lists']; ?></td>
  </tr>
  <tr>
    <td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'Frontend edit' ).'::'.JText::_('Frontend edit tip'); ?>"><label for="frontend_edit"><?php echo JText::_( 'Frontend edit' ); ?>:</label>
    </td>
    <td><?php echo $this->lists['frontend_edit']; ?></td>
  </tr>
  <tr>
    <td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'Custom field Required' ).'::'.JText::_('Custom field Required tip'); ?>"><label for="frontend_edit"><?php echo JText::_( 'Custom field required' ); ?>:</label>
    </td>
    <td><?php echo $this->lists['required']; ?></td>
  </tr>
  <tr id="row-min">
    <td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'Min characters' ).'::'.JText::_('Min characters tip'); ?>"><label for="min"><?php echo JText::_( 'Min characters' ); ?>:
    </label></td>
    <td><input type="text" name="min" id="min" size="3" value="<?php echo $this->object->min; ?>"/></td>
  </tr>
  <tr id="row-max">
    <td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'Max characters' ).'::'.JText::_('Max characters tip'); ?>"><label for="max"><?php echo JText::_( 'Max characters' ); ?>:
    </label></td>
    <td><input type="text" name="max" id="max" size="3" value="<?php echo $this->object->max; ?>"/></td>
  </tr>
  <tr id="row-options">
    <td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'COM_REDEVENT_CUSTOM_FIELD_OPTIONS_LABEL' ).'::'.JText::_('COM_REDEVENT_CUSTOM_FIELD_OPTIONS_DESC'); ?>"><label for="options"><?php echo JText::_( 'COM_REDEVENT_CUSTOM_FIELD_OPTIONS_LABEL' ); ?>:
    </label></td>
    <td><textarea name="options" id="options" rows="6" cols="20"><?php echo $this->object->options; ?></textarea></td>
  </tr>
  <tr id="row-default">
    <td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'COM_REDEVENT_CUSTOM_FIELD_DEFAULT_LABEL' ).'::'.JText::_('COM_REDEVENT_CUSTOM_FIELD_DEFAULT_TIP'); ?>"><label for="options"><?php echo JText::_( 'COM_REDEVENT_CUSTOM_FIELD_DEFAULT_LABEL' ); ?>:
    </label></td>
    <td><textarea name="default_value" id="default_value" rows="6" cols="20"><?php echo $this->object->default_value; ?></textarea></td>
  </tr>
</table>
</fieldset>
</div>

<div class="clr"></div>

<input type="hidden" name="option" value="com_redevent" /> <input
	type="hidden" name="controller" value="customfield" /> <input
	type="hidden" name="cid[]" value="<?php echo $this->object->id; ?>" />
<input type="hidden" name="task" value="" /></form>