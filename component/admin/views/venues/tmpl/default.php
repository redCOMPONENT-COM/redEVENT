<?php
/**
 * @package     Redevent
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('rdropdown.init');
JHtml::_('rbootstrap.tooltip');
JHtml::_('rjquery.chosen', 'select');

$saveOrderUrl = 'index.php?option=com_redevent&task=venues.saveOrderAjax&tmpl=component';
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'obj.ordering' && strtolower($listDirn) == 'asc');

$user = JFactory::getUser();
$userId = $user->id;
$search = $this->state->get('filter.search');

if (($saveOrder) && ($this->canEdit))
{
	JHTML::_('rsortablelist.sortable', 'table-items', 'adminForm', strtolower($listDirn), $saveOrderUrl, false, true);
}
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

		if (pressbutton == 'venues.delete')
		{
			var r = confirm('<?php echo JText::_("COM_REDEVENT_VENUE_DELETE_COMFIRM")?>');
			if (r == true)    form.submit();
			else return false;
		}
		form.submit();
	}
</script>
<form action="index.php?option=com_redevent&view=venues" class="admin" id="adminForm" method="post" name="adminForm">
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
				<th width="30" nowrap="nowrap">
					<?php echo JHTML::_('rsearchtools.sort', 'JSTATUS', 'obj.published', $listDirn, $listOrder); ?>
				</th>
				<?php if ($this->canEdit) : ?>
					<th width="1" nowrap="nowrap">
					</th>
				<?php endif; ?>
				<?php if (($search == '') && ($this->canEdit)) : ?>
					<th width="40">
						<?php echo JHTML::_('rsearchtools.sort', '<i class=\'icon-sort\'></i>', 'obj.ordering', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<th class="title" width="auto">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_NAME', 'obj.name', $listDirn, $listOrder); ?>
				</th>
				<th width="50">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_VENUES_VENUE_CODE', 'obj.venue_code', $listDirn, $listOrder); ?>
				</th>
				<th width="50">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_COMPANY', 'obj.company', $listDirn, $listOrder); ?>
				</th>
				<th width="50">
					<?php echo JText::_('COM_REDEVENT_WEBSITE'); ?>
				</th>
				<th width="10">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_CITY', 'obj.city', $listDirn, $listOrder); ?>
				</th>
				<th width="50">
					<?php echo JText::_('COM_REDEVENT_CATEGORY'); ?>
				</th>
				<th width="10">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_ACCESS', 'obj.access', $listDirn, $listOrder); ?>
				</th>
				<th><?php echo JText::_('COM_REDEVENT_CREATION' ); ?></th>
				<th><?php echo JText::_('COM_REDEVENT_SESSIONS' ); ?></th>
				<th width="40">
					<?php echo JHTML::_('rsearchtools.sort', 'JGRID_HEADING_LANGUAGE', 'obj.language', $listDirn, $listOrder); ?>
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
					<td>
						<?php if ($this->canEditState) : ?>
							<?php echo JHtml::_('rgrid.published', $row->published, $i, 'venues.', true, 'cb'); ?>
						<?php else : ?>
							<?php if ($row->published) : ?>
								<a class="btn btn-small disabled"><i class="icon-ok-sign icon-green"></i></a>
							<?php else : ?>
								<a class="btn btn-small disabled"><i class="icon-remove-sign icon-red"></i></a>
							<?php endif; ?>
						<?php endif; ?>
					</td>
					<?php if ($this->canEdit) : ?>
						<td>
							<?php if ($row->checked_out) : ?>
								<?php
								$editor = JFactory::getUser($row->checked_out);
								$canCheckin = $row->checked_out == $userId || $row->checked_out == 0;
								echo JHtml::_('rgrid.checkedout', $i, $editor->name, $row->checked_out_time, 'venues.', $canCheckin);
								?>
							<?php endif; ?>
						</td>
					<?php endif; ?>
					<?php if (($search == '') && ($this->canEdit)) : ?>
						<td class="order nowrap center">
						<span class="sortable-handler hasTooltip <?php echo ($saveOrder) ? '' : 'inactive'; ?>">
							<i class="icon-move"></i>
						</span>
							<input type="text" style="display:none" name="order[]" value="<?php echo $orderkey + 1;?>" class="text-area-order" />
						</td>
					<?php endif; ?>
					<td>
						<?php $itemTitle = JHTML::_('string.truncate', $row->venue, 50, true, false); ?>
						<?php if (($row->checked_out) || (!$this->canEdit)) : ?>
							<?php echo $itemTitle; ?>
						<?php else : ?>
							<?php echo JHtml::_('link', 'index.php?option=com_redevent&task=venue.edit&id=' . $row->id, $itemTitle); ?>
						<?php endif; ?>
						<br />
						<?php echo JHTML::_('string.truncate', $row->alias, 50, true, false); ?>
					</td>
					<td><?php echo $row->venue_code; ?></td>
					<td><?php echo $row->company; ?></td>
					<td>
						<?php
						if ($row->url) {
							?>
							<a href="<?php echo htmlspecialchars($row->url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
								<?php
								if (JString::strlen($row->url) > 25) {
									echo JString::substr( htmlspecialchars($row->url, ENT_QUOTES, 'UTF-8'), 0 , 25).'...';
								} else {
									echo htmlspecialchars($row->url, ENT_QUOTES, 'UTF-8');
								}
								?>
							</a>
						<?php
						} else {
							echo  '-';
						}
						?>
					</td>
					<td align="left"><?php echo $row->city ? htmlspecialchars($row->city, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
					<td>
						<?php
						foreach ((array) $row->categories as $ck => $cat)
						{
							if ($cat->checked_out && ( $cat->checked_out != $this->user->get('id') ) )
							{
								echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8');
							}
							else
							{
								$catlink    = 'index.php?option=com_redevent&amp;controller=venuescategories&amp;task=edit&amp;cid[]='.$cat->id;
								?>
								<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_EDIT_CATEGORY' );?>::<?php echo $cat->name; ?>">
								<a href="<?php echo $catlink; ?>">
									<?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?>
								</a></span>
								<?php

								if ($ck < count($row->categories)-1)
								{
									echo "<br/>";
								}
							}
						}
						?>
					</td>
					<td align="center">
						<?php echo $row->access_level; ?>
					</td>
					<td>
						<?php echo JText::_('COM_REDEVENT_AUTHOR' ).': '; ?><a href="<?php echo 'index.php?option=com_users&amp;task=edit&amp;hidemainmenu=1&amp;cid[]='.$row->created_by; ?>"><?php echo $row->author; ?></a><br />
						<?php echo JText::_('COM_REDEVENT_EMAIL' ).': '; ?><a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email; ?></a><br />
						<?php
						$delivertime 	= JHTML::Date($row->created, JText::_('DATE_FORMAT_LC2'));
						$edittime 		= JHTML::Date($row->modified, JText::_('DATE_FORMAT_LC2'));
						$ip				= $row->author_ip == 'DISABLED' ? JText::_('COM_REDEVENT_DISABLED' ) : $row->author_ip;
						$image 			= JHTML::_('image', 'administrator/templates/'. $this->template .'/images/menu/icon-16-info.png', JText::_('COM_REDEVENT_NOTES') );
						$overlib 		= JText::_('COM_REDEVENT_CREATED_AT' ).': '.$delivertime.'<br />';
						$overlib		.= JText::_('COM_REDEVENT_WITH_IP' ).': '.$ip.'<br />';
						if ($row->modified != '0000-00-00 00:00:00') {
							$overlib 	.= JText::_('COM_REDEVENT_EDITED_AT' ).': '.$edittime.'<br />';
							$overlib 	.= JText::_('COM_REDEVENT_EDITED_FROM' ).': '.$row->editor.'<br />';
						}
						?>
						<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_VENUE_STATS'); ?>::<?php echo $overlib; ?>">
					<?php echo $image; ?>
				</span>
					</td>
					<td align="center"><?php echo JHTML::link($sessionslink, $row->assignedevents); ?></td>
					<td>
						<?php echo $row->language; ?>
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
