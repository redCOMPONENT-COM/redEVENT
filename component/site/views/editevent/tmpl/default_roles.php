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

defined ( '_JEXEC' ) or die ( 'Restricted access' );
?>
<fieldset class="adminform">
<legend><?php echo JText::_('COM_REDEVENT_Roles'); ?></legend>

<table class="admintable">
<tbody>
	<?php foreach ((array)$this->roles as $k => $r): ?>
  <tr>
  	<td><?php echo JHTML::_('select.genericlist', $this->rolesoptions, 'rrole[]', '', 'value', 'text', $r->role_id); ?></td>
  	<td><?php echo JHTML::_('list.users', 'urole[]', $r->user_id, 0, NULL, 'name', 0); ?> <button type="button" class="role-button remove-role"><?php echo Jtext::_('COM_REDEVENT_REMOVE'); ?></button></td>
  </tr>
  <?php endforeach; ?>
  <tr id="trnewrole">
  	<td><?php echo JHTML::_('select.genericlist', $this->rolesoptions, 'rrole[]', array('id' => 'newrolerole', 'class' => 'rrole')); ?></td>
  	<td><?php echo JHTML::_('list.users', 'urole[]', 0, 1, 'id="newroleuser"', 'name', 0); ?> <button type="button" class="role-button" id="add-role"><?php echo JText::_('COM_REDEVENT_add'); ?></button></td>  	
  </tr>
</tbody>
</table>

</fieldset>