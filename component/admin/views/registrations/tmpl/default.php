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

		if (pressbutton == 'registrations.delete')
		{
			var r = confirm('<?php echo JText::_("COM_REDEVENT_REGISTRATION_DELETE_COMFIRM")?>');
			if (r == true)    form.submit();
			else return false;
		}
		form.submit();
	}
</script>
<form action="index.php?option=com_redevent&view=registrations" class="admin" id="adminForm" method="post" name="adminForm">
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
				<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_SESSION', 'e.title', $listDirn, $listOrder); ?>
			</th>
			<th width="auto">
				<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_UNIQUE_ID', 'r.id', $listDirn, $listOrder); ?>
			</th>
			<th width="auto">
				<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_USERNAME', 'u.username', $listDirn, $listOrder); ?>
			</th>
			<th width="auto">
				<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_ACTIVATED', 'r.confirmed', $listDirn, $listOrder); ?>
			</th>
			<th width="50">
				<?php echo JHTML::_('rsearchtools.sort', 'COM_REDEVENT_WAITINGLIST', 'r.waitinglist', $listDirn, $listOrder); ?>
			</th>
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

			$eventdate = (!RedeventHelper::isValidDate($row->dates) ? JText::_('COM_REDEVENT_Open_date') : strftime($this->params->get('backend_formatdate', '%d.%m.%Y'), strtotime($row->dates)));
			$sessionlink = JHTML::link('index.php?option=com_redevent&view=attendees&session=' . $row->xref,
					$row->title . '<br/>' . $eventdate,
					'class="hasTip" title="' . JText::_('COM_REDEVENT_VIEW_REGISTRATIONS_CLICK_TO_MANAGE') . '::"') . '<br/>@' . $row->venue . '</br>' . JText::_('COM_REDEVENT_AUTHOR') . ': ' . $row->creator;

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
							echo JHtml::_('rgrid.checkedout', $i, $editor->name, $row->checked_out_time, 'registrations.', $canCheckin);
							?>
						<?php endif; ?>
					</td>
				<?php endif; ?>
				<td>
					<?php if (($row->checked_out) || (!$this->canEdit)) : ?>
						<?php echo $displaydate; ?>
					<?php else : ?>
						<?php echo JHtml::_('link', 'index.php?option=com_redevent&task=attendee.edit&id=' . $row->id . '&return=' . $this->return, $displaydate); ?>
					<?php endif; ?>
				</td>
				<td><?php echo $sessionlink; ?></td>
				<td><?php echo $row->course_code . '-' . $row->xref . '-' . $row->attendee_id; ?></td>
				<td><?php echo $row->name; ?></td>
				<td>
					<?php echo $this->confirmed($row, $i); ?>
				</td>
				<td>
					<?php echo $this->waitingStatus($row, $i); ?>
				</td>

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
