<?php
/**
 * @version    $Id: form.php 94 2008-05-02 10:28:05Z julienv $
 * @package    JoomlaTracks
 * @copyright	Copyright (C) 2008 Julien Vonthron. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla Tracks is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
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
JToolBarHelper::help( 'screen.tracks.edit' );
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
    <td><?php echo $this->lists['types']; ?></td>
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
    <td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'custom field options' ).'::'.JText::_('custom field options tip'); ?>"><label for="options"><?php echo JText::_( 'custom field options' ); ?>:
    </label></td>
    <td><textarea name="options" id="options" rows="6" cols="20"><?php echo $this->object->options; ?></textarea></td>
  </tr>
</table>
</fieldset>
</div>

<div class="clr"></div>

<input type="hidden" name="option" value="com_redevent" /> <input
	type="hidden" name="controller" value="customfield" /> <input
	type="hidden" name="cid[]" value="<?php echo $this->object->id; ?>" />
<input type="hidden" name="task" value="" /></form>