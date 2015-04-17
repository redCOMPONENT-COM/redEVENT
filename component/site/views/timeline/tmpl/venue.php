<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die();

$venueData = $this->venue;

?>

<div id="redevent" class="timeline-venue">
	<h2><?php echo $venueData->venue; ?></h2>

	<?php if ($venueData->locdescription): ?>
		<div class="description">
			<?php echo JHTML::_('content.prepare', $venueData->locdescription); ?>
		</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_(RedeventHelperRoute::getVenueTimelineRoute($venueData->slug)); ?>" method="post" id="adminForm">
		<div class="filters">
			<div class="timeline-search">
				<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" placeholder="<?php echo JText::_('COM_REDEVENT_EVENTS_FILTER_HINT'); ?>"/>
			</div>

			<div id="date-filter" class="custom-filter"><?php echo $this->lists['dateFilter']; ?>
				<ul id="divselectdate" class=" dynfilter"></ul>
			</div>

			<?php if ($this->customsfilters && count($this->customsfilters)): ?>
				<?php foreach ($this->customsfilters as $custom): ?>
					<div class="custom-filter" id="filter<?php echo $custom->id; ?>">
						<?php echo '<label for="filtercustom'.$custom->id.'">'.JText::_($custom->name).'</label>&nbsp;'; ?>
						<?php echo $custom->renderFilter(array('class' => "inputbox dynfilter"), isset($this->filter_customs[$custom->id]) ? $this->filter_customs[$custom->id] : null); ?>
						<ul id="divselect<?php echo $custom->id ?>" class="inputbox dynfilter"></ul>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
			<button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();" class="btn-reset-filter-redvent"><?php echo '<span class="pink">X </span>'.JText::_('COM_REDEVENT_RESET' ); ?></button>
		</div>
	</form>

	<div id="sessions-toolbar">
		<div id="print">
			<?php //echo JHtml::link(RedeventHelperRoute::getVenueTimelinePrintRoute($venueData->id), JText::_('COM_REDEVENT_TIMELINE_VENUE_PRINT_PROGRAMM')); ?>
			<a href="#" onclick="window.print();return false;"><?php echo JText::_('COM_REDEVENT_TIMELINE_VENUE_PRINT_PROGRAMM'); ?></a>
		</div>
		<div id="back">
			<?php echo JHtml::link(RedeventHelperRoute::getTimelineRoute($venueData->id),
				JText::_('COM_REDEVENT_TIMELINE_VENUE_BACK')); ?>
		</div>
	</div>

	<div id="sessions">
		<?php foreach ($this->rows AS $session): ?>
		<div class="session-detail">
			<div class="row">
				<div class="col-md-4 session-image">
					<?php
					if ($session->datimage)
					{
						$img = JHtml::image($session->datimage, $session->full_title, array('class' => 'session-image'));
						echo $img;
					}
					?>
				</div>

				<div class="col-md-6 session-desc">
					<div class="session-header">
						<?php foreach (explode("\n", $session->custom6) as $type): ?>
							<span class="type-<?php
							// We hash the type has the value could be a phrase, unusable for css
							echo hash('crc32', $type); ?>" aria-hidden="true"></span>
						<?php endforeach; ?>
						<span class="time">
							<?php echo RedeventHelper::formatEventStart($session, 'H:i') . ' - ' . RedeventHelper::formatEventEnd($session, 'H:i'); ?>
						</span>
						<?php foreach (explode("\n", $session->custom7) as $born): ?>
						<span class="born"><?php echo $born; ?></span>
						<?php endforeach; ?>
					</div>
					<div class="session-title"><?php echo $session->full_title; ?></div>
					<div class="session-summary">
						<?php echo $session->summary; ?>
						<span class="link-full"><?php
							echo JHtml::link(RedeventHelperRoute::getDetailsRoute($session->slug, $session->xslug),
								JText::_('COM_REDEVENT_TIMELINE_READMORE')); ?></span>
					</div>
				</div>

				<div class="col-md-2">
					<?php if ($session->custom8 == 'no'): ?>
						<div class="session-free">
							<?php echo JText::_('COM_REDEVENT_TIMELINE_FREE'); ?>
						</div>
					<?php elseif ($session->external_registration_url): ?>
						<div class="session-paid">
							<?php echo JHtml::link($session->external_registration_url, JText::_('COM_REDEVENT_TIMELINE_GET_TICKET')); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

</div>
