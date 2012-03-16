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
      alert( "<?php echo JText::_('COM_REDEVENT_NAME_IS_REQUIRED', true ); ?>" );
    } else {
      submitform( pressbutton );
    }
	}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col50">
<fieldset class="adminform"><legend><?php echo JText::_( 'COM_REDEVENT_ROLE' ); ?></legend>

<table class="admintable">
	<tr>
		<td width="100" align="right" class="key"><label for="name"><?php echo JText::_('COM_REDEVENT_Name' ); ?>:
		</label></td>
		<td><input class="text_area required" type="text" name="name" id="name"
			size="32" maxlength="250" value="<?php echo $this->object->name; ?>" />
		</td>
	</tr>
	<tr>
		<td valign="top" align="right" class="key"><label for="ordering"> <?php echo JText::_('COM_REDEVENT_Ordering' ); ?>:
		</label></td>
		<td><?php echo $this->lists['ordering']; ?></td>
	</tr>
	<?php if (isset($this->lists['usertype'])): ?>
	<tr>
		<td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'COM_REDEVENT_REDMEMBER_USERTYPE_LABEL' ).'::'.JText::_( 'COM_REDEVENT_REDMEMBER_USERTYPE_DESC' ); ?>"><label for="usertype"> <?php echo JText::_( 'COM_REDEVENT_REDMEMBER_USERTYPE_LABEL' ); ?>:
		</label></td>
		<td><?php echo $this->lists['usertype']; ?></td>
	</tr>
	<tr <?php echo $this->object->usertype ? '' : 'style="display:none;"' ; ?>>
		<td valign="top" align="right" class="key hasTip" title="<?php echo JText::_( 'COM_REDEVENT_REDMEMBER_USERTYPE_FIELDS_LABEL' ).'::'.JText::_( 'COM_REDEVENT_REDMEMBER_USERTYPE_FIELDS_DESC' ); ?>"><label for="fields"> <?php echo JText::_( 'COM_REDEVENT_REDMEMBER_USERTYPE_FIELDS_LABEL' ); ?>:
		</label></td>
		<td><?php echo $this->lists['fields']; ?></td>
	</tr>
	<?php endif; ?>
	<tr>
		<td valign="top" align="right" class="key"><label for="description"> <?php echo JText::_('COM_REDEVENT_Description' ); ?>:
		</label></td>
		<td><textarea cols="80" rows="5" name="description"><?php echo $this->object->description; ?></textarea></td>
	</tr>
</table>
</fieldset>
</div>

<div class="clr"></div>

<input type="hidden" name="option" value="com_redevent" /> <input
	type="hidden" name="controller" value="roles" /> <input
	type="hidden" name="cid[]" value="<?php echo $this->object->id; ?>" />
<input type="hidden" name="task" value="" /></form>