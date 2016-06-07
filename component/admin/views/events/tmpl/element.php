<?php
/**
 * @package     Redevent
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2005 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('rdropdown.init');
JHtml::_('rbootstrap.tooltip');
JHtml::_('rjquery.chosen', 'select');

RHelperAsset::load('redevent-backend.css');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$user = JFactory::getUser();
$userId = $user->id;
$search = $this->state->get('filter.search');

$function = JFactory::getApplication()->input->get('function');

$action = "index.php?option=com_redevent&view=events&layout=element&tmpl=component&function=" . $function;
?>
<form action="<?php echo $action; ?>" class="admin" id="adminForm" method="post" name="adminForm">
	<?php
	echo RedeventLayoutHelper::render(
		'searchtools.eventelement',
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
				<th width="30" nowrap="nowrap">
					<?php echo JHTML::_('rsearchtools.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
				</th>
				<th class="title" width="auto">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_EVENT_TITLE', 'obj.title', $listDirn, $listOrder); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_CATEGORY', 'cat.name', $listDirn, $listOrder); ?>
				</th>
				<th>
					<?php echo JHTML::_('rsearchtools.sort', 'JGRID_HEADING_LANGUAGE', 'c.language', $listDirn, $listOrder); ?>
				</th>
				<th width="10">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_ID', 'obj.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php $n = count($this->items); ?>
			<?php foreach ($this->items as $i => $row) : ?>
				<tr>
					<td>
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td>
						<?php if ($row->published) : ?>
							<a class="btn btn-small disabled"><i class="icon-ok-sign icon-green"></i></a>
						<?php else : ?>
							<a class="btn btn-small disabled"><i class="icon-remove-sign icon-red"></i></a>
						<?php endif; ?>
					</td>
					<td>
						<a href="javascript:void();" class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $row->id; ?>', '<?php echo $this->escape(addslashes($row->title)); ?>');">
							<?php $itemTitle = JHTML::_('string.truncate', $row->title, 50, true, false); ?>
							<?php echo $itemTitle; ?>
						</a>
					</td>
					<td>
						<?php
						foreach ((array) $row->categories as $k => $cat)
						{
							echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8');
						}
						?>
					</td>
					<td align="center"><?php echo $row->language == '*' ? Jtext::_('All') : $row->language_title; ?></td>
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
	<?php echo JHtml::_('form.token'); ?>
</form>
