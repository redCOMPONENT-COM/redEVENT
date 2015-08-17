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

$function = JFactory::getApplication()->input->get('function');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$user = JFactory::getUser();
$userId = $user->id;
$search = $this->state->get('filter.search');
?>
<form action="index.php?option=com_redevent&view=sessions&layout=element&tmpl=component&function=<?php echo $function; ?>" id="adminForm" method="post" name="adminForm">
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
				<th width="30" nowrap="nowrap">
					<?php echo JHTML::_('rsearchtools.sort', 'JSTATUS', 'obj.published', $listDirn, $listOrder); ?>
				</th>
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
			<?php foreach ($this->items as $i => $row) :
				/* Get the date */
				$date = (!RedeventHelper::isValidDate($row->dates) ? JText::_('COM_REDEVENT_Open_date') : strftime($this->params->get('backend_formatdate', '%d.%m.%Y'), strtotime($row->dates)));
				$enddate  = (!RedeventHelper::isValidDate($row->enddates) || $row->enddates == $row->dates) ? '' : strftime($this->params->get('backend_formatdate', '%d.%m.%Y'), strtotime($row->enddates));
				$displaydate = $date. ($enddate ? ' - '.$enddate: '');
				$endreg = (!RedeventHelper::isValidDate($row->registrationend) ? '-' : strftime( $this->params->get('backend_formatdate', '%d.%m.%Y'), strtotime( $row->registrationend )));

				$displaytime = '';
				/* Get the time */
				if (isset($row->times) && $row->times != '00:00:00') {
					$displaytime = strftime( $this->params->get('formattime', '%H:%M'), strtotime( $row->times ));

					if (isset($row->endtimes) && $row->endtimes != '00:00:00') {
						$displaytime .= ' - '.strftime( $this->params->get('formattime', '%H:%M'), strtotime( $row->endtimes ));
					}
				}

				$featured = $this->featured($row, $i);

				$venuelink = JRoute::_( 'index.php?option=com_redevent&task=venue.edit&id=' . $row->venueid);
				$eventlink = JRoute::_( 'index.php?option=com_redevent&task=event.edit&id=' . $row->eventid);
				?>
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
						<?php echo $displaydate; ?>
					</td>
					<td><?php echo $displaytime; ?></td>
					<td><?php echo $row->session_code; ?></td>

					<td>
						<a href="javascript:void()" class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $row->id; ?>', '<?php echo $this->escape(addslashes($row->event_title)); ?>');">
							<?php $itemTitle = JHTML::_('string.truncate', $row->event_title, 50, true, false); ?>
							<?php echo $row->event_title; ?>
						</a>
					</td>

					<td>
						<?php
							echo $row->venue;
						?>
					</td>

					<td><?php echo $row->title; ?></td>
					<td><?php echo $row->note; ?></td>
					<td class="text-center"><?php echo $featured ?></td>

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
