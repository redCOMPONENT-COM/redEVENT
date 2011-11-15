<?php
/**
 * @version 1.0 $Id: default.php 1588 2009-11-17 18:45:32Z julien $
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
JHTML::_('behavior.tooltip');
?>

<script language="javascript" type="text/javascript">
	function submitbutton(task)
	{

		var form = document.adminForm;

		if (task == 'cancel') {
			submitform( task );
			return;
		} else {
			submitform( task );
		}
	}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<fieldset class="adminform"><legend><?php echo JText::_('COM_REDEVENT_Group_ACL' ); ?></legend>

<table class="admintable">
	<tr>
		<td width="100" align="right" class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_ACL_MAINTAINED_CATEGORIES' ).'::'.JText::_('COM_REDEVENT_ACL_MAINTAINED_CATEGORIES_TIP' ); ?>"><label for="maintaincategories"> <?php echo JText::_('COM_REDEVENT_ACL_MAINTAINED_CATEGORIES' ); ?>:
		</label></td>
		<td><?php echo $this->lists['maintaincategories']; ?></td>
	</tr>
	<tr>
		<td width="100" align="right" class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_ACL_MAINTAINED_VENUES_CATEGORIES' ).'::'.JText::_('COM_REDEVENT_ACL_MAINTAINED_VENUES_CATEGORIES_TIP' ); ?>"><label for="maintainvenues"> <?php echo JText::_('COM_REDEVENT_ACL_MAINTAINED_VENUES_CATEGORIES' ); ?>:
		</label></td>
		<td><?php echo $this->lists['maintainvenuescategories']; ?></td>
	</tr>
	<tr>
		<td width="100" align="right" class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_ACL_MAINTAINED_VENUES' ).'::'.JText::_('COM_REDEVENT_ACL_MAINTAINED_VENUES_TIP' ); ?>"><label for="maintainvenues"> <?php echo JText::_('COM_REDEVENT_ACL_MAINTAINED_VENUES' ); ?>:
		</label></td>
		<td><?php echo $this->lists['maintainvenues']; ?></td>
	</tr>
</table>
	
</fieldset>

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="controller" value="groups" />
<input type="hidden" name="view" value="groupacl" />
<input type="hidden" name="group_id" value="<?php echo $this->group_id; ?>" />
<input type="hidden" name="task" value="" />
</form>

<p class="copyright">
	<?php echo ELAdmin::footer( ); ?>
</p>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>