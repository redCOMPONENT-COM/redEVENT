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
$baseHeight = 50;
$sessionInforHeight = 400;
?>

<style type="text/css">
	.rf_img {min-height:<?php echo $this->config->get('imageheight', 100);?>px;}
	.redevent-timeline .timeline-sessions-wrapper {overflow: auto;}
	.redevent-timeline .timeline-sessions-wrapper .timeline-sessions {overflow: hidden; position: relative;}
	.redevent-timeline .timeline-sessions-wrapper .timeline-venues {position: absolute; display: block; box-sizing: border-box; height: 100%; border-right: 1px solid #0099ff;}
	.redevent-timeline .timeline-session-header, .redevent-timeline .timeline-venues-header {height: <?php echo $baseHeight; ?>px; background: #c0c0c0;}
	.redevent-timeline .timeline-sessions-wrapper .timeline-session-header-time {position: absolute; top: 0px; height: <?php echo $baseHeight; ?>px;}
	.redevent-timeline .timeline-sessions-wrapper .time-venues-base {position: relative; border-left: 1px solid #c0c0c0;}
	.redevent-timeline .timeline-sessions-wrapper .timeline-venues-wrapper {border-bottom: 1px solid #c0c0c0;}
	.redevent-timeline .timeline-sessions-wrapper .session-infor-hidden {display: none;}
	.redevent-timeline .timeline-sessions-wrapper .time-venues-base-information {background: #c0c0c0;display:block;box-sizing: border-box;padding:15px;position: static;}
	.venues-list {float: left; width: 29.9%; padding-left: 15px;}
	.sessions-list {float: left; width: 70%;}
	#timeline-session-information {background: #c0c0c0; position: absolute; overflow: hidden; display: block; width: 100%;}
	#timeline-session-information .col-left {float: left; width: 29.9%; padding: 15px 15px 15px 30px;}
	#timeline-session-information .col-right {float: left; width: 70%; padding: 15px 30px 15px 15px;}
	.timeline-wrapper {position: relative;}
	.timeline-venues-fake {background: #c0c0c0;}
</style>

<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			// Sort time checkbox
			$('#timeline-sort-venue-checkbox').change(function(event){
				event.preventDefault();

				if ($(this).is(':checked')) {
					$('#timeline-filter-order').val('l.venue');
					$('#timeline-filter-direction').val('asc');
				}
				else {
					$('#timeline-filter-order').val('x.dates');
					$('#timeline-filter-direction').val('ASC');
				}

				$('#adminForm').submit();
			});

			$('.timeline-venues').click(function(event){
				event.preventDefault();

				var hiddenInfor = $(this).parent().find('.session-infor-hidden');
				var rowIndex = hiddenInfor.attr('data-row');
				var parentBase = $(this).parent().parent();
				var topPos = parentBase.height() + <?php echo $baseHeight ?>;
				var targetInfor = $('#' + $(hiddenInfor).attr('data-target'));
				var targetVenueFake = $('#timeline-venues-fake-' + rowIndex);

				console.log(targetVenueFake);

				$('#timeline-session-information').hide();

				$('.time-venues-base-information').each(function(index){
					if ($(this).attr('id') != targetInfor.attr('id')) {
						$(this).hide();
					}
				});

				$('.timeline-venues-fake').each(function(index){
					if ($(this).attr('id') != targetVenueFake.attr('id')) {
						$(this).hide();
					}
				});

				parentBase.prevAll('.time-venues-base').each(function(index){
					topPos += $(this).height();
				});

				$('#timeline-session-information').find('.col-left').html($(hiddenInfor).find('.session-left-infor').html());
				$('#timeline-session-information').find('.col-right').html($(hiddenInfor).find('.session-right-infor').html());
				$('#timeline-session-information').css('top', topPos + 'px');

				targetVenueFake.slideToggle('slow');

				targetInfor.slideToggle('slow', function(){
					if (targetInfor.css('display') != 'none') {
						$('#timeline-session-information').fadeIn();
					}
				});
			});

			$('#timeline-session-information').height(<?php echo $sessionInforHeight ?>).hide();

			$('.time-venues-base-information, .timeline-venues-fake').each(function(index){
				$(this).height(<?php echo $sessionInforHeight ?>).css('max-height', '<?php echo $sessionInforHeight ?>px').hide();
			});
		});
	})(jQuery);
</script>

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
					<div class="el_fleft" id="el-events-filters">
						<?php if ($this->params->get('filter_text', 1) && $this->lists['filter_type']): ?>
							<div id="main-filter">
								<label for="filter_type"><?php echo JText::_('COM_REDEVENT_FILTER') ?></label>&nbsp
								<?php echo $this->lists['filter_type'] ?>&nbsp;
								<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" title="<?php echo JText::_('COM_REDEVENT_EVENTS_FILTER_HINT'); ?>"/>
								<button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_('COM_REDEVENT_GO' ); ?></button>
								<button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();"><?php echo JText::_('COM_REDEVENT_RESET' ); ?></button>
							</div>
						<?php endif; ?>

						<?php if ($this->params->get('lists_filter_date', 0)): ?>
							<div id="date-filter"><?php echo $this->lists['dateFilter']; ?></div>
						<?php endif; ?>

						<?php if ($this->params->get('lists_filter_event', 0)): ?>
							<div id="event-filter"><?php echo $this->lists['eventfilter']; ?></div>
						<?php endif; ?>

						<?php if ($this->params->get('lists_filter_category', 1)): ?>
							<div id="category-filter"><?php echo $this->lists['categoryfilter']; ?></div>
						<?php endif; ?>

						<?php if ($this->params->get('lists_filter_venue', 1)): ?>
							<div id="venue-filter"><?php echo $this->lists['venuefilter']; ?></div>
						<?php endif; ?>

						<?php if ($this->customsfilters && count($this->customsfilters)): ?>
							<?php foreach ($this->customsfilters as $custom): ?>
								<div class="custom-filter" id="filter<?php echo $custom->id; ?>">
									<?php echo '<label for="filtercustom'.$custom->id.'">'.JText::_($custom->name).'</label>&nbsp;'; ?>
									<?php echo $custom->renderFilter(array('class' => "inputbox dynfilter"), isset($this->filter_customs[$custom->id]) ? $this->filter_customs[$custom->id] : null); ?>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
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
		<input type="hidden" id="timeline-filter-order" name="filter_order" value="<?php echo $this->order; ?>" />
		<input type="hidden" id="timeline-filter-direction" name="filter_order_Dir" value="<?php echo $this->orderDir; ?>" />
		<input type="hidden" name="layout" value="<?php echo $this->getLayout(); ?>" />
	</form>
	<!-- filter end -->

	<?php if (!empty($this->rows)): ?>
	<div class="redevent-timeline">
		<div class="container">
			<div class="row">
				<div class="timeline-wrapper">
					<div class="venues-list">
						<div class="timeline-venues-header">
							<?php echo JText::_('COM_REDEVENT_TIMELINE_LOCATIONS') ?>
							<?php $sortChecked = ($this->order == 'l.venue') ? ' checked' : ''; ?>
							<label href="javascript:void(0);" class="timeline-sort-venue-label" for="timeline-sort-venue-checkbox">
								<input type="checkbox" value="" id="timeline-sort-venue-checkbox" <?php echo $sortChecked ?>/> <?php echo JText::_('COM_REDEVENT_TIMELINE_LOCATIONS_SORT_ALPHABETICAL') ?>
							</label>
						</div>
						<?php
						$timelineHeight = $baseHeight;
						$venueIndex = 0;
						?>
						<?php foreach ($this->rows as $venues): ?>
							<?php foreach ($venues['events'] as $venueEvent): ?>
								<?php $currentHeight = 0; ?>
								<?php foreach ($venueEvent->sessions as $sessionsRow): ?>
									<?php $currentHeight += count($sessionsRow) * $baseHeight; ?>
								<?php endforeach; ?>
								<div class="timeline-venue" style="height: <?php echo $currentHeight ?>px;"><?php echo $venues['venue'] ?></div>
								<div class="timeline-venues-fake" id="timeline-venues-fake-<?php echo $venueIndex ?>"></div>
								<?php $timelineHeight += $currentHeight; ?>
							<?php endforeach; ?>
							<?php $venueIndex++; ?>
						<?php endforeach; ?>
					</div>
					<div class="sessions-list">
						<div class="timeline-sessions-wrapper">
							<div class="timeline-sessions" style="width: <?php echo $timelineWidth ?>px;">
								<div class="timeline-session-header">
									<?php for ($timelineHour = $timelineStart; $timelineHour <= $timelineEnd; $timelineHour++): ?>
										<?php $timelineHourLeft = ($timelineHour - $timelineStart) * 60 * $this->minutePixel; ?>
										<div class="timeline-session-header-time" style="left: <?php echo $timelineHourLeft; ?>px;">
											<?php echo $timelineHour; ?>:00
										</div>
									<?php endfor; ?>
								</div>
								<?php $rowIndex = 0; ?>
								<?php foreach ($this->rows as $venue): ?>
									<?php foreach ($venue['events'] as $eventIndex => $event): ?>
										<?php
										$baseRowHeight = 0;
										foreach ($event->sessions as $sessionRowIndex => $sessionRow):
											$baseRowHeight += $baseHeight * count($sessionRow);
										endforeach;
										?>
										<div class="time-venues-base" style="height: <?php echo $baseRowHeight ?>px;">
										<?php foreach ($event->sessions as $sessionRowIndex => $sessionRow): ?>
											<?php $sessionRowPos = $sessionRowIndex * $baseHeight; ?>
												<?php foreach ($sessionRow as $session): ?>
													<div class="timeline-venues-wrapper" style="height: <?php echo $baseHeight ?>px; top: <?php echo $sessionRowPos ?>px;">
														<div class="timeline-venues" style="left: <?php echo $session->startPixel ?>px;  width: <?php echo $session->widthPixel ?>px;">
															<div class="timeline-session-time"><?php echo $session->times ?> - <?php echo $session->endtimes ?></div>
															<div class="timeline-session-title"><?php echo $session->session_title ?></div>
														</div>
														<div class="session-infor-hidden" id="session-infor-<?php echo $session->xref ?>" data-target="time-venues-session-infor-<?php echo $rowIndex ?>" data-row="<?php echo $rowIndex ?>">
															<?php
															$displayData = array('session' => $session);
															echo RLayoutHelper::render('timeline.session', $displayData, null, null);
															?>
														</div>
													</div>
												<?php endforeach; ?>
										<?php endforeach; ?>
										</div>
										<div class="time-venues-base-information" id="time-venues-session-infor-<?php echo $rowIndex ?>"></div>
									<?php endforeach; ?>
									<?php $rowIndex++; ?>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
					<div class="clear"></div>
					<div id="timeline-session-information">
						<div class="col-left">
						</div>
						<div class="col-right">
						</div>
						<div class="clear"></div>
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
