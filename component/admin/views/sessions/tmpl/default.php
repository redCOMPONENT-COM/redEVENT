<?php
/**
 * @package     Redevent.backend
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */defined('_JEXEC') or die;

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

		if (pressbutton == 'sessions.delete')
		{
			var r = confirm('<?php echo JText::_("COM_REDEVENT_ROLE_DELETE_COMFIRM")?>');
			if (r == true)    form.submit();
			else return false;
		}
		form.submit();
	}
</script>
<form action="index.php?option=com_redevent&view=sessions" class="admin" id="adminForm" method="post" name="adminForm">
	<?php
	echo RedeventLayoutHelper::render(
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
					<?php if (version_compare(JVERSION, '3.0', 'lt')): ?>
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					<?php else : ?>
						<?php echo JHTML::_('grid.checkall'); ?>
					<?php endif; ?>
				</th>
				<th width="30" nowrap="nowrap">
					<?php echo JHTML::_('rsearchtools.sort', 'JSTATUS', 'obj.published', $listDirn, $listOrder); ?>
				</th>
				<?php if ($this->canEdit): ?>
					<th width="1" nowrap="nowrap">
					</th>
				<?php endif; ?>
				<th class="title" width="auto">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_DATE', 'obj.dates', $listDirn, $listOrder); ?>
				</th>
				<th width="10"><?php echo JText::_('COM_REDEVENT_TIME'); ?></th>
				<th width="40">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_SESSIONS_SESSION_CODE', 'obj.session_code', $listDirn, $listOrder); ?>
				</th>
				<th width="auto">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_EVENT', 'e.title', $listDirn, $listOrder); ?>
				</th>
				<th width="auto">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_VENUE', 'v.venue', $listDirn, $listOrder); ?>
				</th>
				<th width="auto">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_TITLE', 'obj.title', $listDirn, $listOrder); ?>
				</th>
				<th width="50">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_NOTE', 'obj.note', $listDirn, $listOrder); ?>
				</th>
				<th width="10">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_SESSION_FEATURED', 'obj.featured', $listDirn, $listOrder); ?>
				</th>
				<?php if (!$this->event || $row->registra): ?>
				<th width="40">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_REGISTRATION_END', 'obj.registrationend', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>
				<th width="10"><?php echo JText::_('COM_REDEVENT_SESSION_TABLE_HEADER_ATTENDEES'); ?></th>
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
			<?php foreach ($this->items as $i => $row):
				/* Get the date */
				$date = RedeventHelperDate::formatdate($row->dates, $row->times, $this->params->get('backend_formatdate', 'd.m.Y'));
				$enddate  = (!RedeventHelperDate::isValidDate($row->enddates) || $row->enddates == $row->dates)
					? ''
					: RedeventHelperDate::formatdate($row->enddates, $row->endtimes, $this->params->get('backend_formatdate', 'd.m.Y'));
				$displaydate = $date. ($enddate ? ' - '.$enddate: '');
				$endreg = (!RedeventHelperDate::isValidDate($row->registrationend) ? '-' : RedeventHelperDate::formatdate($row->registrationend, null, $this->params->get('backend_formatdate', 'd.m.Y') . ' H:i'));

				$displaytime = '';

				/* Get the time */
				if (RedeventHelperDate::isValidTime($row->times) && $row->times != '00:00:00')
				{
					$displaytime = RedeventHelperDate::formattime($row->dates, $row->times);

					if (RedeventHelperDate::isValidTime($row->endtimes) && $row->endtimes != '00:00:00')
					{
						$displaytime .= ' - ' . RedeventHelperDate::formattime($row->enddates, $row->endtimes, $this->params->get('formattime', 'H:i'));
					}
				}

				$featured = $this->featured($row, $i);

				$venuelink = JRoute::_('index.php?option=com_redevent&task=venue.edit&id=' . $row->venueid);
				$eventlink = JRoute::_('index.php?option=com_redevent&task=event.edit&id=' . $row->eventid);
				?>
				<tr>
					<td>
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td>
						<?php echo JHtml::_('grid.id', $i, $row->id); ?>
					</td>
					<td>
						<?php if ($this->canEditState) : ?>
							<?php echo $this->published($row, $i); ?>
						<?php else: ?>
							<?php if ($row->published): ?>
								<a class="btn btn-small disabled"><i class="icon-ok-sign icon-green"></i></a>
							<?php else: ?>
								<a class="btn btn-small disabled"><i class="icon-remove-sign icon-red"></i></a>
							<?php endif; ?>
						<?php endif; ?>
					</td>
					<?php if ($this->canEdit): ?>
						<td>
							<?php if ($row->checked_out): ?>
								<?php
								$editor = JFactory::getUser($row->checked_out);
								$canCheckin = $row->checked_out == $userId || $row->checked_out == 0;
								echo JHtml::_('rgrid.checkedout', $i, $editor->name, $row->checked_out_time, 'sessions.', $canCheckin);
								?>
							<?php endif; ?>
						</td>
					<?php endif; ?>
					<td>
						<?php if (($row->checked_out) || (!$this->canEdit)) : ?>
							<?php echo $displaydate; ?>
						<?php else : ?>
							<?php echo JHtml::_('link', 'index.php?option=com_redevent&task=session.edit&id=' . $row->id, $displaydate); ?>
						<?php endif; ?>
						<span class="linkfront hasTooltip" title="<?php echo JText::_('COM_REDEVENT_EVENT_FRONTEND_LINK'); ?>">
						<?php echo JHTML::link(JURI::root().RedeventHelperRoute::getDetailsRoute($row->eventid, $row->id),
							JHTML::image('media/com_redevent/images/linkfront.png',
								JText::_('COM_REDEVENT_EVENT_FRONTEND_LINK'))); ?>
					</span>
					</td>
					<td><?php echo $displaytime; ?></td>
					<td><?php echo $row->session_code; ?></td>

					<?php if (!$this->event): ?>
						<td>
							<?php
							$table = RTable::getAdminInstance('Event');
							if ($table->isCheckedOut($this->user->get ('id'), $row->event_checked_out))
							{
								echo $row->event_title;
							}
							else
							{
								?>
								<a href="<?php echo $eventlink; ?>" title="<?php echo JText::_('COM_REDEVENT_EDIT_EVENT' ); ?>">
									<?php echo $row->event_title; ?></a>
							<?php
							}
							?>
						</td>
					<?php endif; ?>

					<td>
						<?php
						$table = RTable::getAdminInstance('Venue');
						if ($table->isCheckedOut($this->user->get ('id'), $row->event_checked_out))
						{
							echo $row->venue;
						}
						else
						{
							?>
							<a href="<?php echo $venuelink; ?>" title="<?php echo JText::_('COM_REDEVENT_EDIT_VENUE' ); ?>">
								<?php echo $row->venue; ?></a>
						<?php
						}
						?>
					</td>

					<td><?php echo $row->title; ?></td>
					<td><?php echo $row->note; ?></td>
					<td class="text-center"><?php echo $featured ?></td>

					<?php if (!$this->event || $row->registra): ?>
						<td><?php echo $endreg; ?></td>
						<td><?php echo ($row->registra ?
								JHTML::link('index.php?option=com_redevent&view=attendees&session=' . $row->id, intval($row->attendees->attending). ' / '. intval($row->attendees->waiting)) : '-'); ?></td>
					<?php endif; ?>

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
	<?php echo JHtml::_('form.token'); ?>
</form>
