<?php defined('_JEXEC') or die('Restricted access'); ?>
<fieldset class="adminform">
<legend><?php echo JText::_('Roles'); ?></legend>

<table class="admintable">
<tbody>
  <tr id="trnewrole">
  	<td><?php echo JHTML::_('select.genericlist', $this->rolesoptions, 'rrole[]', array('id' => 'newrolerole', 'class' => 'rrole')); ?></td>
  	<td><?php echo JHTML::_('list.users', 'urole[]', 0, 1, 'id="newroleuser"', 'name', 0); ?> <button type="button" class="role-button" id="add-role"><?php echo Jtext::_('add'); ?></button></td>  	
  </tr>
</tbody>
</table>

</fieldset>

<?php
