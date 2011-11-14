<?php // no direct access
/**
* @package RokMicroEvents
* @copyright Copyright (C) 2009 RocketTheme. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
defined('_JEXEC') or die('Restricted access'); 

if (!count($events)) echo JText::_("ROKMINIEVENTS_NOEVENTSFROUND");
if (isset($events['error'])) echo $events;

if (!count($events) || isset($events['error'])) return;


$offset_x = $params->get('offset_x', 0);

$pages = ceil(count($events) / $params->get('events_pane'));
$per_pane = $params->get('events_pane'); 
$timeline = $params->get('timeline', 'both');
?>

<script type="text/javascript">
/* <![CDATA[ */
	if (typeof RokMiniEvents != 'undefined' && RokMiniEvents.settings){
		RokMiniEvents.settings['rme-<?php echo $module->id; ?>'] = {
			'per_pane': <?php echo $params->get('events_pane'); ?>,
			'offset': {'x': <?php echo $offset_x; ?>},
			'transition': Fx.Transitions.<?php echo $params->get('transition', 'Expo.easeInOut'); ?>,
			'duration': <?php echo $params->get('duration', 500); ?>
		} 
	}
/* ]]> */
</script>
<div id="rme-<?php echo $module->id; ?>" class="rokminievents-wrapper">
	<div class="rokminievents-container">
		<ul class="rokminievents grid<?php echo $params->get('events_pane', 3); ?>">
			<?php $i = 0; ?>
			<?php foreach($events as $event): ?>
				<?php
					$start = $event->getStart()->getDay() . ' ' . $event->getStart()->getMonth() . ' ' . $event->getStart()->getYear();
                    $end = $event->getEnd()->getDay() . ' ' . $event->getEnd()->getMonth() . ' ' . $event->getEnd()->getYear();
					$time = $event->getStart()->getTime();
				
					if ($end === $start) $end = '';
					else $end = ' to ' . $end; 
				
					if (!$event->isAllDay()) $time .= ' to ' . $event->getEnd()->getTime();
				?>
				<li class="rokminievents-item<?php echo (!$i) ? ' rokminievents-item-first' : '';?>">
					<div class="rokminievents-item-padding">
						<?php if ($params->get('datedisplay') == 'badge' || $params->get('datedisplay') == 'both'): ?>
						<div class="rokminievents-badge<?php echo ($params->get('showyear')) ? ' showyear' : ''; ?>">
							<span class="day"><?php echo $event->getStart()->getDay(); ?></span>
							<span class="month"><?php echo $event->getStart()->getMonth(); ?></span>
							<?php if ($params->get('showyear')): ?><span class="year"><?php echo $event->getStart()->getYear(); ?></span><?php endif; ?>
						</div>
						<?php endif; ?>
						<div class="rokminievents-desc">
							
							<?php if (!$event->getLink()): ?>
								<h3 class="rokminievents-title-nolink"><?php echo $event->getTitle(); ?></h3>
							<?php else: ?>
								<?php
									$values = $event->getLink();
									$internal = $values['internal'];
									$link = $values['link'];
								?>
								<a class="rokminievents-title<?php echo $internal ? '' : ' rokminievents-external-link';?>" href="<?php echo $link ?>"><?php echo $event->getTitle(); ?></a>
							<?php endif; ?>
							
							<?php if ($params->get('datedisplay') == 'inline' || $params->get('datedisplay') == 'both'): ?>
							<span class="rokminievents-date"><?php echo $start . $end; ?></span>
							<?php endif; ?>
							<?php if(!$event->isAllDay()): ?><span class="rokminievents-time"><?php echo $time; ?><?php endif;?></span>
							<?php if ($params->get('show_description')): ?>
							<p><?php echo $event->getDescription(); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</li>
				<?php $i++; ?>
			<?php endforeach;  ?>
		</ul>
	</div>
	<div class="rokminievents-controls<?php echo ($timeline == 'arrows' || $timeline == 'both') ? ' arrows-on' : '';?>">
		<?php if ($timeline == 'arrows' || $timeline == 'both'): ?>
		<div class="arrows">
			<span class="left-arrow"></span>
			<span class="right-arrow"></span>
		</div>
		<?php endif;?>
		<?php if ($timeline == 'timeline' || $timeline == 'both'): ?>
		<div class="timeline">
			<div class="progress-wrapper">
				<div class="progress">
					<div class="pins">
						<?php
							for($i = 0; $i < $pages; $i++) echo '<div class="rokminievent-page"></div>'."\n";
						?>
					</div>
					<div class="knob"></div>
				</div>
			</div>
			<?php if ($params->get('timeline_dates') == 'inline'): ?>
			<div class="timeline-dates date-inline">
			<?php else: ?>
			<div class="timeline-dates date-column">
			<?php endif; ?>
				<?php 
					$timelineDates = RokMiniEvents::getTimelineDates($events, $params);
					foreach($timelineDates as $date):
				?>
				<?php if ($params->get('timeline_dates') == 'inline'): ?>
					<div class="date">
						<span class="start"><?php echo $date['start']; ?></span> - <span class="end"><?php echo $date['end']; ?></span>
					</div>
				<?php else: ?>
					<div class="date">
						<span class="start"><?php echo $date['start']; ?></span>
						<span class="end"><?php echo $date['end']; ?></span>
					</div>
				<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
