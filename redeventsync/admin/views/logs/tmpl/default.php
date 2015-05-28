<?php
/**
 * @package     Redeventsync.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JHtml::_('rbootstrap.tooltip');
JHtml::_('behavior.framework');
JHtml::_('rjquery.chosen', 'select');

$action = JRoute::_('index.php?option=com_redeventsync&view=logs');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$search = $this->state->get('filter.search');

JText::script('COM_REDEVENTSYNC_LOGS_ALERT_ARCHIVE');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton) {

		if (pressbutton == 'logs.archiveold') {
			if (!confirm(Joomla.JText._('COM_REDEVENTSYNC_LOGS_ALERT_ARCHIVE'))) {
				return false;
			}
		}

		Joomla.submitform(pressbutton);
	};
</script>
<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

	<?php
	echo ResyncHelperLayout::render(
		'searchtools.default',
		array(
			'view' => $this,
			'options' => array(
				'filterButton' => true,
				'filtersHidden' => false,
				'searchField' => 'search',
				'searchFieldSelector' => '#filter_search',
				'limitFieldSelector' => '#list_log_limit',
				'activeOrder' => $listOrder,
				'activeDirection' => $listDirn
			)
		)
	);
	?>

	<hr/>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-info">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<div class="pagination-centered">
				<h3><?php echo JText::_('COM_REDEVENTSYNC_NOTHING_TO_DISPLAY') ?></h3>
			</div>
		</div>
	<?php else : ?>
		<table class="table table-striped table-hover" id="logList">
			<thead>
			<tr>
				<th width="10" align="center">
					<?php echo '#'; ?>
				</th>
				<th class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDEVENTSYNC_LOGS_TYPE', 'obj.type', $listDirn, $listOrder); ?>
				</th>
				<th width="18%" class="nowrap">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDEVENTSYNC_LOGS_DATE', 'obj.date', $listDirn, $listOrder); ?>
				</th>
				<th width="18%" class="nowrap">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDEVENTSYNC_LOGS_DIRECTION', 'obj.direction', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDEVENTSYNC_LOGS_TRANSACTIONID', 'obj.transactionid', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDEVENTSYNC_LOGS_FIELD_STATUS', 'obj.status', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'Id', 'obj.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<?php if ($this->items): ?>
				<tbody>
				<?php foreach ($this->items as $i => $item): ?>
					<?php
					$canChange = 1;
					$canEdit = 1;
					$canCheckin = 1;
					?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_redeventsync&view=log&id=' . $item->id); ?>">
								<?php echo $this->escape($item->type); ?>
							</a>
						</td>
						<td>
							<?php echo $this->escape($item->date); ?>
						</td>
						<td>
							<?php if ($item->direction): ?>
								<i class="icon-arrow-up"></i>
							<?php else: ?>
								<i class="icon-arrow-down"></i>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $this->escape($item->transactionid); ?>
						</td>
						<td>
							<?php echo $this->escape($item->status); ?>
						</td>
						<td>
							<?php echo $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			<?php endif; ?>
		</table>
		<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
	<?php endif; ?>

	<div>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
