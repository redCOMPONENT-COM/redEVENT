<?php // no direct access
defined('_JEXEC') or die('Restricted access');

$year = $req_year;
$month = $req_month;

$prev_month_link = JHTML::link($prev_link, '');
$next_month_link = JHTML::link($next_link, '');


$uxtime_first_of_month = gmmktime(0, 0, 0, $month, 1, $year);
$firstofthemonth = JFactory::getDate("$month/1/$year");
$uxtime_first_of_month = $firstofthemonth->toUnix();

#remember that mktime will automatically correct if invalid dates are entered
# for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
# this provides a built in "rounding" feature to generate_calendar()
$first_week = strftime($week_nb_format, $uxtime_first_of_month);

// caption
$current_month = ucfirst($Month_length ?  $month_names_short[$month-1] : $month_names[$month-1]);
$current_year = $Year_length ? $year : substr($year, 2, 3);
$caption   = $prev_month_link.' '.$current_month.' '.$current_year.' '.$next_month_link;

// Today
$tz = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));
$currentdate = JFactory::getDate('now', $tz);
$today     = $currentdate->format('j', true);
$currmonth = $currentdate->format('m', true);
$curryear  = $currentdate->format('Y', true);

// for toggle
if ($params->get('toggle', 0) && $params->get('default_toggle', 1) == 0) {
	$toggleclass = ' hide_mod';
}
else {
	$toggleclass = '';
}
?>

<div class='redeventcal<?php echo $toggleclass; ?>' align='center'>

	<div class="cal_content">
		<?php

		// where do we start => first weekday of the month
		$weekday = $firstofthemonth->format('w', true);
		$weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
		?>
		<table class="mod_redeventcal_calendar">
			<caption class="mod_redeventcal_calendar-month"><?php echo $caption; ?></caption>
			<tr>
				<?php if ($show_weeknb): ?>
					<th class="mod_redeventcal_wk"><?php echo JText::_('MOD_REDEVENTCAL_WK'); ?></th>
				<?php endif; ?>

				<?php foreach ($day_names as $k => $full): ?>
					<?php
					// day name
					if ($day_name_length >3) {
						$day_name = $full;
					}
					else
					{
						if (function_exists('mb_substr'))
						{
							$day_name = mb_substr($full,0,$day_name_length, 'UTF-8');
						}
						else
						{
							$day_name = substr($full,0,$day_name_length, 'UTF-8');
						}
					}
					?>
					<th class="mod_redeventcal_daynames" abbr="<?php echo $full; ?>"><?php echo $day_name; ?></th>
				<?php endforeach; ?>
			</tr>

			<tr>
				<?php if ($show_weeknb): ?>
			<td class="mod_redeventcal_wk"><?php echo $first_week++; ?></th>
		<?php endif; ?>

				<?php for ($counti = 0; $counti < $weekday; $counti++): // initial 'empty' days ?>
					<td class="mod_redeventcal">&nbsp;</td>
				<?php endfor; ?>

				<?php for ($day = 1, $days_in_month = gmdate('t', $uxtime_first_of_month); $day <= $days_in_month; $day++, $weekday++): ?>

				<?php if ($weekday == 7):
				$weekday   = 0; #start a new week
				?>
			</tr>
			<tr>
				<?php if ($show_weeknb): ?>
					<td class="mod_redeventcal_wk"><?php echo $first_week++; ?></td>
				<?php endif; ?>
				<?php endif; ?>

				<?php
				if (($day == $today) & ($currmonth == $month) & ($curryear == $year)) {
					$tdbaseclass = 'mod_redeventcal_caltoday';
				} else {
					$tdbaseclass = 'mod_redeventcal_calday';
				}

				if (isset($days[$day])):
					$events = $days[$day];
					if (count($events) > 1)
					{
						$link = JRoute::_(RedeventHelperRoute::getDayRoute($year.sprintf('%02d', $month).sprintf('%02d', $day)));
						$tip = array();
						foreach ($events as $e) {
							$tip[] = $params->get('events_tip') ? $e->title.' @ '.$e->venue : $e->title;
						}
						$tip = implode("<br/>", $tip);
					}
					else
					{
						$link = JRoute::_(RedeventHelperRoute::getDetailsRoute($events[0]->slug, $events[0]->xslug));
						$tip = $params->get('events_tip') ? $events[0]->title.' @ '.$events[0]->venue : $events[0]->title;
					}
					$text = count( $events ) . ' ' . JText::_($CalTooltipsTitle);

					if ($Show_Tooltips==1): ?>
						<td class="<?php  echo $tdbaseclass; ?>link">
							<?php echo JHTML::tooltip($tip, $text, 'tooltip.png', $day, $link); ?>
						</td>
					<?php else: ?>
						<td class="<?php  echo $tdbaseclass; ?>link"><?php echo JHTML::link($link, $day); ?></td>
					<?php endif; ?>

				<?php else: ?>
					<td class="<?php  echo $tdbaseclass; ?>"><?php echo $day; ?></td>
				<?php endif; ?>
				<?php endfor; ?>

				<?php for ($counti = $weekday; $counti < 7; $counti++): // remaining 'empty' days ?>
					<td class="mod_redeventcal">&nbsp;</td>
				<?php endfor; ?>
			</tr>
		</table>
	</div>

	<?php if ($params->get('toggle', 0)):?>
		<div class="cal_toggle"><?php echo JText::_('MOD_REDEVENTCAL_MINIMIZE'); ?></div>
		<div class="toggleoff hasTooltip" title="<?php echo JText::_('MOD_REDEVENTCAL_TOGGLE_TIP');?>"></div>
	<?php endif; ?>

</div>
