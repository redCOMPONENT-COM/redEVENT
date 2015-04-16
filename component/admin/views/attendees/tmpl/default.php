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

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$user = JFactory::getUser();
$userId = $user->id;
$search = $this->state->get('filter.search');

JHtml::_('behavior.modal', 'a.answersmodal');

RHelperAsset::load('redevent-backend.css', 'com_redevent');
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

		if (pressbutton == 'attendees.delete')
		{
			var r = confirm('<?php echo JText::_("COM_REDEVENT_REGISTRATION_DELETE_COMFIRM")?>');
			if (r == true)    form.submit();
			else return false;
		}
		form.submit();
	}
</script>

<div class="well">
	<strong><?php echo JText::_('COM_REDEVENT_DATE' ).':'; ?></strong>
	<?php echo JHtml::link(
		'index.php?option=com_redevent&task=session.edit&id=' . $this->session->xref,
		(RedeventHelper::isValidDate($this->session->dates) ? $this->session->dates : JText::_('COM_REDEVENT_OPEN_DATE'))
		); ?>
	<br />
	<strong><?php echo JText::_('COM_REDEVENT_EVENT_TITLE' ).':'; ?></strong>&nbsp;<?php echo htmlspecialchars($this->session->title, ENT_QUOTES, 'UTF-8'); ?>
</div>


<form action="index.php?option=com_redevent&view=attendees" class="admin" id="adminForm" method="post" name="adminForm">
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
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_REGDATE', 'r.uregdate', $listDirn, $listOrder); ?>
				</th>
				<th width="40">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_ACTIVATIONDATE', 'r.confirmdate', $listDirn, $listOrder); ?>
				</th>
				<?php if ($this->params->get('attendees_table_show_ip', 0)): ?>
					<th class="50">
						<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_IP_ADDRESS', 'r.uip', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>

				<?php if ($this->params->get('attendees_table_show_uniqueid', 1)): ?>
				<th width="auto">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_UNIQUE_ID', 'r.id', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>
				<th width="auto">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_REGISTERED_BY', 'u.username', $listDirn, $listOrder); ?>
				</th>
				<th width="auto">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_ACTIVATED', 'r.confirmed', $listDirn, $listOrder); ?>
				</th>
				<th width="50">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_WAITINGLIST', 'r.waitinglist', $listDirn, $listOrder); ?>
				</th>

				<?php foreach ((array) $this->redformFields as $f):?>
					<?php if (in_array($f->fieldId, $this->selectedRedformFields)): ?>
					<th class="title">
						<?php echo JHTML::_('rsearchtools.sort',  $f->field_header, 'f.field_' . $f->id, $listDirn, $listOrder); ?>
					</th>
					<?php endif; ?>
				<?php endforeach;?>

				<th width="10"><?php echo JText::_('COM_REDEVENT_ANSWERS'); ?></th>
				<th width="10"><?php echo JText::_('COM_REDEVENT_PRICE'); ?></th>
				<th width="10"><?php echo JText::_('COM_REDEVENT_PRICEGROUP'); ?></th>
				<th width="10">
					<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_PAYMENT', 'p.paid', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php $n = count($this->items); ?>
			<?php foreach ($this->items as $i => $row) :
				$displaydate = JHTML::Date($row->uregdate, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'));
				$trClass = $row->cancelled ? ' class="cancelled"' : '';
				?>
				<tr<?php echo $trClass; ?>>
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
								echo JHtml::_('rgrid.checkedout', $i, $editor->name, $row->checked_out_time, 'attendees.', $canCheckin);
								?>
							<?php endif; ?>
						</td>
					<?php endif; ?>
					<td>
						<?php if (($row->checked_out) || (!$this->canEdit)) : ?>
							<?php echo $displaydate; ?>
						<?php else : ?>
							<?php echo JHtml::_('link', 'index.php?option=com_redevent&task=attendee.edit&id=' . $row->id, $displaydate); ?>
						<?php endif; ?>
					</td>
					<td><?php echo ($row->confirmdate) ? JHTML::Date( $row->confirmdate, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME' ) ) : '-'; ?></td>

					<?php if ($this->params->get('attendees_table_show_ip', 0)): ?>
						<td><?php echo $row->uip == 'DISABLED' ? JText::_('COM_REDEVENT_DISABLED' ) : $row->uip; ?></td>
					<?php endif; ?>

					<?php if ($this->params->get('attendees_table_show_uniqueid', 1)): ?>
						<td><?php echo $row->course_code . '-' . $row->xref . '-' . $row->attendee_id; ?></td>
					<?php endif; ?>

					<td><?php echo $row->name; ?></td>
					<td>
						<?php echo $this->confirmed($row, $i); ?>
					</td>
					<td>
						<?php if (!$this->session->maxattendees): // no waiting list ?>
							<?php echo '-'; ?>
						<?php else: ?>
							<?php echo $this->waitingStatus($row, $i); ?>
						<?php endif; ?>
					</td>

					<?php foreach ((array) $this->redformFields as $f):?>
						<?php if (in_array($f->fieldId, $this->selectedRedformFields)): ?>
							<?php $fname = 'field_' . $f->fieldId; ?>
							<td><?php echo $row->$fname; ?></td>
						<?php endif; ?>
					<?php endforeach;?>

					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_redevent&view=attendeeanswers&tmpl=component&submitter_id=' . $row->submitter_id); ?>"
							class="answersmodal" rel="{handler: 'iframe'}"><?php echo JText::_('COM_REDEVENT_view') ?></a>
					</td>

					<td class="attendeePrice">
						<?php echo $row->price ? $row->currency . ' ' . $row->price : ''; ?>
					</td>
					<td>
						<?php echo $row->pricegroup; ?>
					</td>
					<td class="price <?php echo($row->paid ? 'paid' : 'unpaid'); ?>">
						<?php $link = JHTML::link(JRoute::_('index.php?option=com_redform&view=payments&submit_key=' . $row->submit_key), JText::_('COM_REDEVENT_history')); ?>
						<?php if (!$row->paid): ?>
							<span class="hasTip"
								title="<?php echo JText::_('COM_REDEVENT_REGISTRATION_NOT_PAID') . '::' . $row->status; ?>"><?php echo JHTML::_('image', 'admin/publish_x.png', 'Not Paid', null, true); ?><?php echo $link; ?></span>
							<?php echo ' ' . JHTML::link(JURI::root() . '/index.php?option=com_redform&task=payment.select&key=' . $row->submit_key, JText::_('COM_REDEVENT_link')); ?>
						<?php else: ?>
							<span class="hasTip"
								title="<?php echo JText::_('COM_REDEVENT_REGISTRATION_PAID') . '::' . $row->status; ?>"><?php echo JHTML::_('image', 'admin/tick.png', 'Paid', null, true); ?><?php echo $link; ?></span>
						<?php endif; ?>
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
