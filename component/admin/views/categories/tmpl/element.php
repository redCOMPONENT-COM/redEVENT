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

defined('_JEXEC') or die('Restricted access');
$function = JFactory::getApplication()->input->get('function');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>

<form action="index.php?option=com_redevent&view=categories&layout=element&tmpl=component&function=<?php echo $function; ?>" method="post" name="adminForm" id="adminForm">
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
				<th width="30" nowrap="nowrap">
					<?php echo JHTML::_('rsearchtools.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
				</th>
				<th width="40">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_ORDER', 'c.ordering', $listDirn, $listOrder); ?>
				</th>
				<th class="title" width="auto">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_CATEGORY_NAME', 'c.name', $listDirn, $listOrder); ?>
				</th>
				<th width="100">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_PARENT_CATEGORY', 'c.left', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_ACCESS', 'c.access', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JHTML::_('rsearchtools.sort', 'JGRID_HEADING_LANGUAGE', 'c.language', $listDirn, $listOrder); ?>
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
						<?php if ($row->published) : ?>
							<a class="btn btn-small disabled"><i class="icon-ok-sign icon-green"></i></a>
						<?php else : ?>
							<a class="btn btn-small disabled"><i class="icon-remove-sign icon-red"></i></a>
						<?php endif; ?>
					</td>
					<td class="nowrap center">
						<?php echo $row->ordering; ?>
					</td>
					<td>
        <a href="javascript:void()" class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $row->id; ?>', '<?php echo $this->escape(addslashes($row->name)); ?>');">
						<?php $itemTitle = JHTML::_('string.truncate', $row->name, 50, true, false); ?>
							<?php echo $itemTitle; ?>
						</a>
					</td>
					<td>
						<?php echo ($row->parent_name) ? htmlspecialchars($row->parent_name, ENT_QUOTES, 'UTF-8') : '-'; ?>
					</td>
					<td>
						<?php echo $row->access_level; ?>
					</td>
					<td>
						<?php echo $row->language; ?>
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
