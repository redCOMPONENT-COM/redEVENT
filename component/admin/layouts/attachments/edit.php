<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

RHelperAsset::load('attachments.js');
RHelperAsset::load('attachements.css');
JText::script('COM_REDEVENT_ATTACHMENT_CONFIRM_MSG');

$data = $displayData;

JHtml::_('rjquery.chosen', 'select');
?>
<table class="adminform" id="re-attachments">
	<thead>
		<tr>
			<th><?php echo JText::_('COM_REDEVENT_ATTACHMENT_NAME'); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_ATTACHMENT_DESCRIPTION'); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_ATTACHMENT_ACCESS'); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_ATTACHMENT_FILE'); ?></th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($data->item->attachments as $file): ?>
		<tr>
			<td><input type="text" name="attached-name[]" placeholder="<?php echo JText::_('COM_REDEVENT_ATTACHMENT_NAME'); ?>" style="width: 100%" value="<?php echo $file->name; ?>" /></td>
			<td><input type="text" name="attached-desc[]" placeholder="<?php echo JText::_('COM_REDEVENT_ATTACHMENT_DESCRIPTION'); ?>" style="width: 100%" value="<?php echo $file->description; ?>" /></td>
			<td><?php echo JHTML::_('access.level', 'attached-access[]', $file->access, 'class="inputbox"', false); ?></td>
			<td><?php echo $file->file; ?><input type="hidden" name="attached-id[]" value="<?php echo $file->id; ?>"/></td>
			<td style="white-space: nowrap">
				<button type="button" class="btn btn-default attach-add"><span class="icon-plus"></span></button>
				<button type="button" class="btn btn-danger attach-remove"><span class="icon-remove"></span></button>
			</td>
		</tr>
		<?php endforeach; ?>
		<tr>
			<td>
				<input type="text" name="attach-name[]" placeholder="<?php echo JText::_('COM_REDEVENT_ATTACHMENT_NAME'); ?>" value="" style="width: 100%" />
			</td>
			<td>
				<input type="text" name="attach-desc[]" placeholder="<?php echo JText::_('COM_REDEVENT_ATTACHMENT_DESCRIPTION'); ?>" value="" style="width: 100%" />
			</td>
			<td>
				<?php echo JHTML::_('access.level', 'attach-access[]', '', '', false); ?>
			</td>
			<td>
				<input type="file" name="attach[]" class="attach-field"/>
			</td>
			<td style="white-space: nowrap">
				<button type="button" class="btn btn-default attach-add"><span class="icon-plus"></span></button>
				<button type="button" class="btn btn-danger attach-remove"><span class="icon-remove"></span></button>
			</td>
		</tr>
	</tbody>
</table>
