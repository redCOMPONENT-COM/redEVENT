<?php defined('_JEXEC') or die('Restricted access'); ?>
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

<?php
