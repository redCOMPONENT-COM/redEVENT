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

		if (pressbutton == 'events.delete')
		{
			var r = confirm('<?php echo JText::_("COM_REDEVENT_CATEGORY_DELETE_COMFIRM")?>');
			if (r == true)    form.submit();
			else return false;
		}
		form.submit();
	}
</script>
<form action="index.php?option=com_redevent&view=events" class="admin" id="adminForm" method="post" name="adminForm">
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
					<?php echo JHTML::_('rsearchtools.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
				</th>
				<?php if ($this->canEdit) : ?>
					<th width="1" nowrap="nowrap">
					</th>
				<?php endif; ?>
				<th class="title" width="auto">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_EVENT_TITLE', 'obj.title', $listDirn, $listOrder); ?>
				</th>
				<th width="100">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_CATEGORY', 'c.name', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JText::_('COM_REDEVENT_SESSIONS'); ?>
				</th>
				<th width="150">
					<?php echo JText::_('COM_REDEVENT_CREATION'); ?>
				</th>
				<th width="150">
					<?php echo JHTML::_('rsearchtools.sort', 'JGRID_HEADING_LANGUAGE', 'c.language', $listDirn, $listOrder); ?>
				</th>
				<th width="10">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_ID', 'c.id', $listDirn, $listOrder); ?>
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
						<?php echo JHtml::_('grid.id', $i, $row->id); ?>
					</td>
					<td>
						<?php if ($this->canEditState) : ?>
							<?php echo JHtml::_('rgrid.published', $row->published, $i, 'events.', true, 'cb'); ?>
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
								echo JHtml::_('rgrid.checkedout', $i, $editor->name, $row->checked_out_time, 'events.', $canCheckin);
								?>
							<?php endif; ?>
						</td>
					<?php endif; ?>
					<td>
						<div>
						<?php $itemTitle = JHTML::_('string.truncate', $row->title, 50, true, false); ?>
						<?php if (($row->checked_out) || (!$this->canEdit)) : ?>
							<?php echo $itemTitle; ?>
						<?php else : ?>
							<?php echo JHtml::_('link', 'index.php?option=com_redevent&task=event.edit&id=' . $row->id, $itemTitle); ?>
						<?php endif; ?>
						<span class="linkfront"><?php echo JHTML::link(JURI::root().RedeventHelperRoute::getDetailsRoute($row->id),
								JHTML::image('administrator/components/com_redevent/assets/images/linkfront.png',
									JText::_('COM_REDEVENT_EVENT_FRONTEND_LINK'))); ?>
						</span>
						<br />
						<?php echo JHTML::_('string.truncate', $row->alias, 50, true, false); ?>
						<?php if ($row->course_code): ?>
							<br/>
							<?php echo $row->course_code; ?>
						<?php endif; ?>
						</div>
					</td>
					<td>
						<?php
						//$cats_html = array();
						foreach ((array) $row->categories as $k => $cat)
						{
							if ($cat->checked_out && ( $cat->checked_out != $this->user->get('id') ) ) {
								echo htmlspecialchars($cat->catname, ENT_QUOTES, 'UTF-8');
							} else {
								$catlink    = 'index.php?option=com_redevent&view=category&cid[]='.$cat->id;
								?>
								<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_EDIT_CATEGORY' );?>::<?php echo $cat->name; ?>">
		            <a href="<?php echo $catlink; ?>">
			            <?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?>
		            </a></span>
								<?php
								if ($k < count($row->categories)-1) {
									echo "<br/>";
								}
							}
						}
						?>
					</td>
					<td>
						<?php if (isset($this->eventvenues[$row->id])): ?>
							<?php echo JHTML::link('index.php?option=com_redevent&view=sessions&eventid='.$row->id,
								Jtext::sprintf('COM_REDEVENT_SESSIONS_LINK', $this->eventvenues[$row->id]->total
									, $this->eventvenues[$row->id]->unpublished
									, $this->eventvenues[$row->id]->published
									, $this->eventvenues[$row->id]->archived
									, $this->eventvenues[$row->id]->featured),
								array('class' => 'hasTip',
									'title' => Jtext::_('COM_REDEVENT_SESSIONS_LINK_TIP_TITLE').'::'.Jtext::sprintf('COM_REDEVENT_SESSIONS_LINK_TIP'
											, $this->eventvenues[$row->id]->unpublished
											, $this->eventvenues[$row->id]->published
											, $this->eventvenues[$row->id]->archived
											, $this->eventvenues[$row->id]->featured))); ?>
						<?php else: ?>
							<?php echo JHTML::link('index.php?option=com_redevent&view=sessions&eventid='.$row->id, Jtext::sprintf('COM_REDEVENT_0_SESSION_LINK')); ?>
						<?php endif; ?>
					</td>
					<td>
						<?php echo JText::_('COM_REDEVENT_AUTHOR' ).': '; ?><a href="<?php echo 'index.php?option=com_users&amp;task=edit&amp;hidemainmenu=1&amp;cid[]='.$row->created_by; ?>"><?php echo $row->author; ?></a><br />
						<?php echo JText::_('COM_REDEVENT_EMAIL' ).': '; ?><a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email; ?></a><br />
						<?php
						$created	 	= JHTML::Date( $row->created, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME' ) );
						$edited 		= JHTML::Date( $row->modified, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME' ) );
						$ip				= $row->author_ip == 'DISABLED' ? JText::_('COM_REDEVENT_DISABLED' ) : $row->author_ip;
						$image 			= '<i class="icon-info"/>';
						$overlib 		= JText::_('COM_REDEVENT_CREATED_AT' ).': '.$created.'<br />';
						$overlib		.= JText::_('COM_REDEVENT_WITH_IP' ).': '.$ip.'<br />';
						if ($row->modified != '0000-00-00 00:00:00')
						{
							$overlib 	.= JText::_('COM_REDEVENT_EDITED_AT' ).': '.$edited.'<br />';
							$overlib 	.= JText::_('COM_REDEVENT_EDITED_FROM' ).': '.$row->editor.'<br />';
						}
						?>
						<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_EVENT_STATS'); ?>::<?php echo $overlib; ?>">
						<?php echo $image; ?>
					</span>
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
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
