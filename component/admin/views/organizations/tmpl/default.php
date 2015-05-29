<?php
/**
* @package     redevent
* @subpackage  Template
*
* @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE
*/defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('rdropdown.init');
JHtml::_('rbootstrap.tooltip');
JHtml::_('rjquery.chosen', 'select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$user = JFactory::getUser();
$userId = $user->id;
$search = $this->state->get('filter.search');
?>
<script type="text/javascript">
	Joomla.submitbutton = function (pressbutton)
	{
		submitbutton(pressbutton);
	}
	submitbutton = function (pressbutton)
	{
		var form = document.adminForm;

		if (pressbutton)
		{
			form.task.value = pressbutton;
		}

		form.submit();
	}
</script>
<form action="index.php?option=com_redevent&view=organizations" class="admin" id="adminForm" method="post" name="adminForm">
	<?php
	echo RLayoutHelper::render(
		'searchtools.default',
		array(
			'view' => $this,
			'options' => array(
				'searchField' => 'search',
				'searchFieldSelector' => '#filter_search',
				'limitFieldSelector' => '#list_fields_limit',
				'activeOrder' => $listOrder,
				'activeDirection' => $listDirn
			)
		)
	);
	?>
	<hr />
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-info">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<div class="pagination-centered">
				<h3><?php echo JText::_('COM_REDEVENT_NOTHING_TO_DISPLAY'); ?></h3>
			</div>
		</div>
	<?php else : ?>
		<table class="table table-striped" id="table-items">
			<thead>
			<tr>
				<th width="10" align="center">
					<?php echo '#'; ?>
				</th>
				<th width="10">
					<?php if (version_compare(JVERSION, '3.0', 'lt')) : ?>
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					<?php else : ?>
						<?php echo JHTML::_('grid.checkall'); ?>
					<?php endif; ?>
				</th>
				<?php if ($this->canEdit) : ?>
					<th width="1" nowrap="nowrap">
					</th>
				<?php endif; ?>
				<th class="title" width="auto">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_NAME', 'o.name', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_B2B_ATTENDEE_NOTIFICATION_MAILFLOW', 'obj.b2b_attendee_notification_mailflow', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JText::_('COM_REDEVENT_ORGANIZATION_B2B_CANCELLATION_PERIOD'); ?>
				</th>
				<th width="10">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_ID', 'obj.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php $n = count($this->items); ?>
			<?php foreach ($this->items as $i => $row) : ?>
				<?php $orderkey = array_search($row->id, $this->ordering[0]); ?>
				<tr>
					<td>
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td>
						<?php echo JHtml::_('grid.id', $i, $row->id); ?>
					</td>
					<?php if ($this->canEdit) : ?>
						<td>
							<?php if ($row->checked_out) : ?>
								<?php
								$editor = JFactory::getUser($row->checked_out);
								$canCheckin = $row->checked_out == $userId || $row->checked_out == 0;
								echo JHtml::_('rgrid.checkedout', $i, $editor->name, $row->checked_out_time, 'organizations.', $canCheckin);
								?>
							<?php endif; ?>
						</td>
					<?php endif; ?>
					<td>
						<?php $itemTitle = JHTML::_('string.truncate', $row->name, 50, true, false); ?>
						<?php if (($row->checked_out) || (!$this->canEdit)) : ?>
							<?php echo $itemTitle; ?>
						<?php else : ?>
							<?php echo JHtml::_('link', 'index.php?option=com_redevent&task=organization.edit&id=' . $row->id, $itemTitle); ?>
						<?php endif; ?>
					</td>
					<td>
						<?php
						switch ($row->b2b_attendee_notification_mailflow)
						{
							case 0:
								echo JText::_('COM_REDEVENT_B2B_ATTENDEE_NOTIFICATION_MAILFLOW_ATTENDEE');
								break;
							case 1:
								echo JText::_('COM_REDEVENT_B2B_ATTENDEE_NOTIFICATION_MAILFLOW_ORG_ADMINS');
								break;
							case 2:
								echo JText::_('COM_REDEVENT_B2B_ATTENDEE_NOTIFICATION_MAILFLOW_BOTH');
								break;
						}
						?>
					</td>
					<td>
						<?php echo $row->b2b_cancellation_period; ?>
					</td>
					<td>
						<?php echo $row->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
	<?php endif; ?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
