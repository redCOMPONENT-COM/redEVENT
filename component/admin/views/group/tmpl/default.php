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

		if (task == 'cancel') {
			submitform( task );
			return;
		}
		else if (form.name.value == ""){
			alert( "<?php echo JText::_( 'ADD GROUP NAME'); ?>" );
		} else {
			submitform( task );
		}
	}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<fieldset class="adminform"><legend><?php echo JText::_( 'Group' ); ?></legend>

<table class="admintable">
	<tr>
		<td width="100" align="right" class="key"><label for="name"> <?php echo JText::_( 'GROUP NAME' ); ?>:
		</label></td>
		<td><input class="text_area required" type="text" name="name" id="name"
			size="32" maxlength="250" value="<?php echo $this->row->name; ?>" />
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key"><label for="description"> <?php echo JText::_( 'DESCRIPTION' ); ?>:
		</label></td>
		<td>
			<textarea wrap="virtual" rows="10" cols="40" name="description" class="inputbox"><?php echo $this->row->description; ?></textarea>
		</td>
	</tr>
</table>
	
</fieldset>

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="controller" value="groups" />
<input type="hidden" name="view" value="group" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="task" value="" />
</form>

<p class="copyright">
	<?php echo ELAdmin::footer( ); ?>
</p>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>