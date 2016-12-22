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

$data = $displayData;

$eventId = $data['eventId'];
$state = $data['state'];
$items = $data['items'];
$pagination = $data['pagination'];
$filterForm = $displayData['filter_form'];
$formName = $data['formName'];
$showToolbar = isset($data['showToolbar']) ? $data['showToolbar'] : false;
$return = isset($data['return']) ? $data['return'] : null;
$action = isset($data['action']) ? $data['action'] : 'index.php?option=com_redevent&view=sessions';
$params = RedeventHelper::config();

$listOrder = $state->get('list.ordering');
$listDirn = $state->get('list.direction');

$user = JFactory::getUser();
$userId = $user->id;
$search = $state->get('filter.search');

$filterForm->removeField('event', 'filter');
$filterForm->removeField('category', 'filter');

$searchToolsOptions = array(
	'searchField' => 'search',
	'searchFieldSelector' => '#filter_search',
	'limitFieldSelector' => '#list_fields_limit',
	"orderFieldSelector" => "#list_fullordering",
	"limitFieldSelector" => "#list_session_limit",
	"activeOrder" => $listOrder,
	"activeDirection" => $listDirn,
	"formSelector" => ("#" . $formName),
	"filtersHidden" => (bool) empty($data['activeFilters'])
);

// Event filter should not enable search tools
if (isset($data['activeFilters']['event']))
{
	unset($data['activeFilters']['event']);
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

		if (pressbutton == 'sessions.delete')
		{
			var r = confirm('<?php echo JText::_("COM_REDEVENT_ROLE_DELETE_COMFIRM")?>');
			if (r == true)    form.submit();
			else return false;
		}
		form.submit();
	}
</script>
<form action="<?php echo $action; ?>" class="admin" id="<?php echo $formName; ?>" method="post" name="<?php echo $formName; ?>">
	<div class="panel panel-default">
		<div class="panel-heading">
			<?php
			// Render the toolbar?
			if ($showToolbar)
			{
				echo RedeventLayoutHelper::render('sessions.toolbar', $data);
			}
			?>
		</div>

		<div class="panel-body">
			<?php
			echo RedeventLayoutHelper::render(
				'searchtools.default',
				array(
					'view' => (object) array(
						'filterForm' => $filterForm,
						'activeFilters' => $data['activeFilters']
					),
					'options' => $searchToolsOptions
				)
			);
			?>

			<?php if (empty($items)) : ?>
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
							<?php echo JHTML::_('rsearchtools.sort', 'JSTATUS', 'obj.published', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
						</th>
						<th width="1" nowrap="nowrap">
						</th>
						<th class="title" width="auto">
							<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_DATE', 'obj.dates', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
						</th>
						<th width="40">
							<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_SESSIONS_SESSION_CODE', 'obj.session_code', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
						</th>
						<th width="auto">
							<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_VENUE', 'v.venue', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
						</th>
						<th width="auto">
							<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_TITLE', 'obj.title', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
						</th>
						<th width="50">
							<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_NOTE', 'obj.note', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
						</th>
						<th width="10">
							<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_SESSION_FEATURED', 'obj.featured', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
						</th>
						<th width="40">
							<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_REGISTRATION_END', 'obj.registrationend', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
						</th>
						<th width="10"><?php echo JText::_('COM_REDEVENT_SESSION_TABLE_HEADER_ATTENDEES'); ?></th>
						<th width="40">
							<?php echo JHTML::_('rsearchtools.sort', 'JGRID_HEADING_LANGUAGE', 'obj.language', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
						</th>
						<th width="10">
							<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_ID', 'obj.id', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php $n = count($items); ?>
					<?php foreach ($items as $i => $item):
						$canChange = 1;
						$canEdit = 1;
						$canEditState = 1;
						$canCheckin = 1;

						$session = RedeventEntitySession::getInstance($item->id);
						$session->bind($item);
						$dates = implode('<br>', $session->getFormattedDates(
								$params->get('backend_formatdate', 'd.m.Y'), $params->get('formattime', 'H:i')
							)
						);

						$endreg = '';

						if ($endregDate = $session->getRegistrationEnd())
						{
							$endreg = $endregDate->format($params->get('backend_formatdate', 'd.m.Y') . ' H:i', true);
						}

						$featured = RedeventHtmlSessions::featured($item, $i, $canEditState);

						$venuelink = JRoute::_('index.php?option=com_redevent&task=venue.edit&id=' . $item->venueid);
						$eventlink = JRoute::_('index.php?option=com_redevent&task=event.edit&id=' . $item->eventid);
						?>
						<tr>
							<td>
								<?php echo $pagination->getRowOffset($i); ?>
							</td>
							<td>
								<?php echo JHtml::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
							</td>
							<td>
								<?php echo JHtml::_('rgrid.published', $item->published, $i, 'sessions.', $canChange, 'cb', null, null, $formName); ?>
							</td>
							<?php if ($canEdit): ?>
								<td>
									<?php if ($item->checked_out): ?>
										<?php
										$editor = JFactory::getUser($item->checked_out);
										$canCheckin = $item->checked_out == $userId || $item->checked_out == 0;
										echo JHtml::_('rgrid.checkedout', $i, $editor->name, $item->checked_out_time, 'sessions.', $canCheckin);
										?>
									<?php endif; ?>
								</td>
							<?php endif; ?>
							<td>
								<?php if (($item->checked_out) || (!$canEdit)) : ?>
									<?php echo $dates; ?>
								<?php else : ?>
									<?php
									$itemUrl = 'index.php?option=com_redevent&task=session.edit&id=' . $item->id
										. '&jform[eventid]=' . $eventId . '&from_form=1';

									if ($return)
									{
										$itemUrl .= '&return=' . $return;
									}
									?>
									<a href="<?php echo $itemUrl; ?>">
										<?php echo $dates; ?>
									</a>
								<?php endif; ?>
								<span class="linkfront hasTooltip" title="<?php echo JText::_('COM_REDEVENT_EVENT_FRONTEND_LINK'); ?>">
								<?php echo JHTML::link(JURI::root().RedeventHelperRoute::getDetailsRoute($item->eventid, $item->id),
									JHTML::image('media/com_redevent/images/linkfront.png',
										JText::_('COM_REDEVENT_EVENT_FRONTEND_LINK'))); ?>
							</span>
							</td>
							<td><?php echo $item->session_code; ?></td>

							<td>
								<?php echo $item->venue; ?>
							</td>

							<td><?php echo $item->title; ?></td>
							<td><?php echo $item->note; ?></td>
							<td class="text-center"><?php echo $featured ?></td>

							<td><?php echo $endreg; ?></td>
							<td><?php echo ($item->registra ?
										JHTML::link('index.php?option=com_redevent&view=attendees&xref=' . $item->id, intval($item->attendees->attending). ' / '. intval($item->attendees->waiting)) : '-'); ?></td>

							<td>
								<?php echo $item->language; ?>
							</td>
							<td>
								<?php echo $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php echo $pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
			<?php endif; ?>

			<div>
				<input type="hidden" name="task" value="session.saveModelState">
				<?php if ($return) : ?>
					<input type="hidden" name="return" value="<?php echo $return; ?>">
				<?php endif; ?>
				<input type="hidden" name="jform[eventid]" value="<?php echo $eventId; ?>">
				<input type="hidden" name="boxchecked" value="0">
				<input type="hidden" name="from_form" value="1">
				<?php echo JHtml::_('form.token'); ?>
			</div>

		</div>
	</div>
</form>
<script type="text/javascript">
	(function ($) {
		$('#<?php echo $formName; ?>').searchtools(
			<?php echo json_encode($searchToolsOptions); ?>
		);
	})(jQuery);
</script>
