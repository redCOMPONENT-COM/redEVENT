<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die();

$toggle = $this->params->get('filter_toggle', 3);
$showPagination = $this->params->def('show_pagination', 1);
$showPaginationResults = $this->params->def('show_pagination_results', 1);

// Hour
$timelineStart = 9;

// Hour
$timelineEnd   = 24;

// Minute
$timelineBlock = 60;

$timelineWidth = ($timelineEnd - $timelineStart) * 60 * $this->minutePixel;
$baseHeight    = 50;
$baseHeightTimelineHeader    = 30;
?>

<style type="text/css">
	.rf_img {min-height:<?php echo $this->config->get('imageheight', 100);?>px;}
	.redevent-timeline .timeline-sessions-wrapper {overflow: auto;}
	.redevent-timeline .timeline-sessions-wrapper .timeline-sessions {overflow: hidden; position: relative;}
	.redevent-timeline .timeline-sessions-wrapper .timeline-venues {position: absolute; display: block; border: 1px solid #c0c0c0; box-sizing: border-box;}
	.redevent-timeline .timeline-session-header, .redevent-timeline .timeline-venues-header {height: <?php echo $baseHeight; ?>px; background: #c0c0c0;}
	.redevent-timeline .timeline-session-header {height: <?php echo $baseHeightTimelineHeader; ?>px; background: #c0c0c0;}
	.redevent-timeline .timeline-sessions-wrapper .timeline-session-header-time {position: absolute; top: 0px; height: <?php echo $baseHeight; ?>px;}
</style>

<div id="redevent" class="rf_thumb">
	<p class="buttons">
		<?php
			if (!$this->params->get('popup')) : //don't show in printpopup
				echo RedeventHelperOutput::listbutton( $this->list_link, $this->params );
				echo RedeventHelperOutput::submitbutton( $this->dellink, $this->params );
			endif;

			echo RedeventHelperOutput::printbutton( $this->print_link, $this->params );
		?>
	</p>

	<?php if ($this->params->def('show_page_title', 1)) : ?>
		<h1 class="componentheading">
			<?php echo $this->escape($this->pagetitle); ?>
		</h1>
	<?php endif; ?>


	<?php if ($this->params->get('showintrotext')) : ?>
		<div class="description no_space floattext">
			<?php echo $this->params->get('introtext'); ?>
		</div>
	<?php endif; ?>

	<!-- filter -->
	<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="adminForm">
		<?php if ($toggle != 1 || $this->params->get('display_limit_select')) : ?>
			<div id="el_filter" class="floattext">
				<?php if ($toggle != 1 || 1): ?>
					<?php if ($toggle > 1): ?>
						<div id="filters-toggle"><?php echo JTExt::_('COM_REDEVENT_TOGGLE_FILTERS'); ?></div>
					<?php endif; ?>
					<div class="el_fleft" id="el-events-filters" style="display:none;">
						<?php if ($this->params->get('filter_text', 1) && $this->lists['filter_type']): ?>
							<div id="main-filter">
								<label for="filter_type"><?php echo JText::_('COM_REDEVENT_FILTER') ?></label>&nbsp
								<?php echo $this->lists['filter_type'] ?>&nbsp;
								<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" title="<?php echo JText::_('COM_REDEVENT_EVENTS_FILTER_HINT'); ?>"/>
								<button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_('COM_REDEVENT_GO' ); ?></button>


							</div>
						<?php endif; ?>

						<?php if ($this->params->get('lists_filter_date', 0)): ?>
							<div id="date-filter"><?php echo $this->lists['dateFilter']; ?></div>
						<?php endif; ?>


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
					<input type="hidden" id="f-showfilters" name="showfilters" value="<?php echo $toggle == 0 ? '1' : JRequest::getInt('showfilters', $toggle != 3 ? 1 : 0); ?>"/>
				<?php endif; ?>
				<?php if ($this->params->get('display_limit_select')) : ?>
					<div class="el_fright">
						<?php
						echo '<label for="limit">'.JText::_('COM_REDEVENT_DISPLAY_NUM').'</label>&nbsp;';
						echo $this->pageNav->getLimitBox();
						?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<input type="hidden" name="filter_order" value="<?php echo $this->order; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderDir; ?>" />
		<input type="hidden" name="layout" value="<?php echo $this->getLayout(); ?>" />
	</form>
	<!-- filter end -->
	<!--  Scrollbar timeline-->

		<?php
			$startTime = 9;
			$minutePixel = 25;
			$currentTimeH=date('H');
			$currentTimeS=date('i');
			$startTimeH=9;
			$startTimeS=0;
			if($currentTimeH > $startTimeH)
			{
				$width=((($currentTimeH - $startTime) * 60) + $currentTimeS) * 0.78;
				$widthTimeMarker=((($currentTimeH - $startTime) * 60) + $currentTimeS)* ($this->minutePixel);

			}
			else if($currentTimeH == $startTimeH)
			{
				$width=( $currentTimeS) * 0.9;
				$widthTimeMarker=((($currentTimeH - $startTime) * 60) + $currentTimeS)* (7.5);
			}
		?>



	<?php if (!empty($this->rows)): ?>
	<div class="redevent-timeline">

		<div class="container">
			<div class="row">
				<div class="col-md-3">

				</div>
				<div class="col-md-9 timeline">
					<div class="scrollbar-timeline">
						<div class="timeline-scrollbar-header">

							<?php for ($timelineHour = $timelineStart; $timelineHour < $timelineEnd; $timelineHour++): ?>

							<?php $timelineHourLeft = ($timelineHour - $timelineStart) * 60 * $this->minutePixel; ?>


								<div class="timeline-session-header-time" style="left: <?php echo $timelineHourLeft; ?>px;">
									<?php echo $timelineHour; ?>
								</div>
							<?php endfor; ?>
						</div>
						<div class="scollbar" >
							<div class="scrollbar-active" style="left: 0px;background:#ff0080;width:<?php echo $width ?>px;height:8px;">
							</div>
							<div class="pointer"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3">
					<div class="timeline-venues-header">
						<?php echo JText::_('COM_REDEVENT_LOCATIONS') ?>
					</div>
					<?php $timelineHeight = $baseHeight; ?>
					<?php foreach ($this->rows as $venue): ?>
						<?php $currentHeight = count($venue['events']) * $baseHeight; ?>
						<div class="timeline-venue" style="height: <?php echo $currentHeight ?>px;">
							<?php echo $venue['venue'] ?>
						</div>
						<?php $timelineHeight += $currentHeight; ?>
					<?php endforeach; ?>
				</div>
				<div class="col-md-9">

					<div class="timeline-sessions-wrapper">
						<div class="timeline-sessions" style="width: <?php echo $timelineWidth ?>px; height: <?php echo $timelineHeight ?>px;">
							<div class="time-marker" style="left: <?php echo $widthTimeMarker ?>px;"></div>
							<div class="timeline-session-header">
								<?php for ($timelineHour = $timelineStart; $timelineHour <= $timelineEnd; $timelineHour++): ?>
									<?php $timelineHourLeft = ($timelineHour - $timelineStart) * 60 * $this->minutePixel; ?>
									<div class="timeline-session-header-time" style="left: <?php echo $timelineHourLeft; ?>px;">
										<?php echo $timelineHour; ?>:00
									</div>
								<?php endfor; ?>
							</div>
							<?php $rowIndex = 1; ?>
							<?php foreach ($this->rows as $venue): ?>
								<?php foreach ($venue['events'] as $event): ?>
									<?php //$topPos = $rowIndex * $baseHeightTimelineHeader; ?>
									<?php $topPos = $rowIndex * $baseHeight; ?>
									<?php foreach ($event->sessions as $session): ?>
										<?php
										// @TODO: For Trang override
										$additionClass = '';

										if (!empty($session->custom10)):
											$sessionTypes = explode("\n", $session->custom10);

											foreach ($sessionTypes as $sessionType):
												$additionClass .= 'type-' . strtolower(JFilterOutput::stringURLSafe($sessionType)) . ' ';
											endforeach;
										endif;
										// @TODO: For Trang override -- End
										?>
										<div class="timeline-venues <?php echo $additionClass; ?>" style="left: <?php echo $session->startPixel ?>px; height: <?php echo $baseHeight ?>px; top: <?php echo $topPos ?>px; width: <?php echo $session->widthPixel ?>px;">
											<div class="<?php echo $additionClass; ?>"></div>
											<div class="timeline-session-time"><?php echo $session->times ?> - <?php echo $session->endtimes ?></div>
											<div class="timeline-session-title"><?php echo $session->session_title ?></div>
										</div>
									<?php endforeach; ?>
									<?php $rowIndex++; ?>
								<?php endforeach; ?>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<!--footer-->
	<!--pagination-->
	<?php if (($showPagination == 1  || ($showPagination == 2)) && ($this->pageNav->get('pages.total') > 1)): ?>
		<div class="pagination">
			<?php  if ($showPaginationResults) : ?>
				<p class="counter"><?php echo $this->pageNav->getPagesCounter(); ?></p>
			<?php endif; ?>
			<?php echo $this->pageNav->getPagesLinks(); ?>
		</div>
	<?php  endif; ?>
	<!-- pagination end -->
</div>
