<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

RHelperAsset::load('xref_roles.js');
JText::script('COM_REDEVENT_LAYOUT_ROLES_REMOVE_CONFIRM_MSG');

$data = $displayData;

JHtml::_('rjquery.chosen', 'select');
?>
&nbsp;<!-- this is a trick for IE7... otherwise the first table inside the tab is shifted right ! -->
<table class="adminform" id="re-roles">
	<thead>
	<tr>
		<th style="width:40%"><?php echo JText::_('COM_REDEVENT_LAYOUT_ROLES_EDIT_ROLE_NAME'); ?></th>
		<th style="width:40%"><?php echo JText::_('COM_REDEVENT_LAYOUT_ROLES_EDIT_ROLE_USER'); ?></th>
		<th style="width:10px">&nbsp;</th>
	</tr>
	</thead>

	<tbody>
		<?php foreach ((array) $data->roles as $k => $r): ?>
			<tr>
				<td>
					<?php echo JHTML::_('select.genericlist', $data->rolesoptions, 'jform[rrole][]', '', 'value', 'text', $r->role_id); ?>
				</td>
				<td>
					<?php echo JHTML::_('list.users', 'jform[urole][]', $r->user_id, 0, null, 'name', 0); ?>
				</td>
				<td>
					<button type="button" class="btn role-button remove-role"><?php echo Jtext::_('COM_REDEVENT_REMOVE'); ?></button>
				</td>
			</tr>
		<?php endforeach; ?>

		<tr id="trnewrole">
			<td>
				<?php echo JHTML::_('select.genericlist', $data->rolesoptions, 'jform[rrole][]', 'id="newrolerole" class="rrole"'); ?>
			</td>
			<td>
				<?php echo JHTML::_('list.users', 'jform[urole][]', 0, 1, 'id="newroleuser"', 'name', 0); ?>
			</td>
			<td>
				<button type="button" class="btn role-button" id="add-role"><?php echo JText::_('COM_REDEVENT_add'); ?></button>
			</td>
		</tr>
	</tbody>
</table>

