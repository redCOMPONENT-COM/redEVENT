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
//JHtml::_('rjquery.chosen', 'select');

$action = JRoute::_('index.php?option=com_redeventsync&view=queuedmessages');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$search = $this->state->get('filter.search');
?>
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
				'limitFieldSelector' => '#list_queuedmessage_limit',
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
		<table class="table table-striped table-hover" id="queuedmessageList">
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
				<th class="nowrap">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDEVENTSYNC_QUEUEDMESSAGE_QUEUED', 'obj.queued', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDEVENTSYNC_QUEUEDMESSAGE_PLUGIN', 'obj.plugin', $listDirn, $listOrder); ?>
				</th>
				<th>
					<?php echo JText::_('COM_REDEVENTSYNC_QUEUEDMESSAGE_MESSAGE'); ?>
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
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="nowrap">
							<a href="<?php echo JRoute::_('index.php?option=com_redeventsync&view=queuedmessage&id=' . $item->id); ?>">
								<?php echo JHtml::_('link', 'index.php?option=com_redeventsync&task=queuedmessage.edit&id=' . $item->id, $item->queued); ?>
							</a>
						</td>
						<td>
							<?php echo $this->escape($item->plugin); ?>
						</td>
						<td>
							<?php echo $this->escape($item->message); ?>
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
