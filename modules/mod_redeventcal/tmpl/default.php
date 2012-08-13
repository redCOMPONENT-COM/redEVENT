<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<div class='redeventcal' align='center'>

<div class="cal_content">
<?php
//Month Names
$uxtime_first_of_month = gmmktime(0, 0, 0, $prev_month, 1, $req_year);
list($tmp, $year, $prev_month, $weekday) = explode(',', gmstrftime('%m,%Y,%b,%w', $uxtime_first_of_month));

$uxtime_first_of_month = gmmktime(0, 0, 0, $next_month, 1, $req_year);
list($tmp, $year, $next_month, $weekday) = explode(',', gmstrftime('%m,%Y,%b,%w', $uxtime_first_of_month));

//Creating switching links
$pn = array( $prev_month=>$prev_link, $next_month=>$next_link);

$document = &JFactory::getDocument();
$document->addStyleSheet( JURI::base() . '/modules/mod_redeventcal/mod_redeventcal.css' );

$calendar = '';
$month_href = NULL;
$year = $req_year;
$month = $req_month;
	
$uxtime_first_of_month = gmmktime(0, 0, 0, $month, 1, $year);
#remember that mktime will automatically correct if invalid dates are entered
# for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
# this provides a built in "rounding" feature to generate_calendar()
$first_week = strftime($week_nb_format, $uxtime_first_of_month);

$day_names = array(); #generate all the day names according to the current locale
$day_names_short = array();
$day_names_long = array();
for( $n = 0, $t = ( 3 + $first_day ) *24 *60 *60; $n < 7; ++$n, $t += 24 *60 *60) #January 4, 1970 was a Sunday
{
	if (!function_exists('mb_convert_case'))
	{
		$day_names_long[$n] = ucfirst(gmstrftime('%A',$t)); #%A means full textual day name
		$day_names_short[$n] = ucfirst(gmstrftime('%A',$t)); #%a means short day name
	}
	else
	{
		$day_names_long[$n] = mb_convert_case(gmstrftime('%A',$t),MB_CASE_TITLE, 'UTF-8'); #%A means full textual day name
		$day_names_short[$n] = mb_convert_case(gmstrftime('%A',$t),MB_CASE_TITLE, 'UTF-8'); #%a means short day name
	}
}
 

list($month, $year, $month_name_long, $month_name_short, $weekday) = explode(',', gmstrftime('%m,%Y,%B,%b,%w', $uxtime_first_of_month));
$weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
$year_length = $Year_length ? $year : substr($year, 2, 3);

$the_month = ucfirst($Month_length ?  $month_name_short : $month_name_long);

$title   = $the_month.' &nbsp;'.$year_length;    #note that some locales don't capitalize month and day names

#Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03

// Modified by Toni to display << and >> for previous and next months
@list($p, $pl) = each($pn); @list($n, $nl) = each($pn); #previous and next links, if applicable
// Modified by Toni to display << and >> for previous and next months

if($p) $p = ($pl ? '<a href="'.htmlspecialchars($pl).'">&lt;&lt; </a>' : $p).'&nbsp;'; //Modified by Toni
if($n) $n = '&nbsp;'.($nl ? '<a href="'.htmlspecialchars($nl).'"> &gt;&gt;</a>' : $n); //Modified by Toni

$month_href = NULL;

$calendar .= '<table class="mod_redeventcal_calendar" cellspacing="0" cellpadding="0">'."\n".
       '<caption class="mod_redeventcal_calendar-month">'.$p.($month_href ? '<a href="'.htmlspecialchars($month_href).'">'.$title.'</a>' : $title).$n."</caption>\n";

// Start days of the week header
$calendar .= '<tr>';

if ($show_weeknb) {
	$calendar .= '<th class="mod_redeventcal_wk">'.JText::_('MOD_REDEVENTCAL_WK').'</th>';
}
 
if($day_name_length)
{
	#if the day names should be shown ($day_name_length > 0)
	#if day_name_length is >3, the full name of the day will be printed
	if ($day_name_length >3)
	{
		foreach($day_names_long as $d) {
			$calendar .= '<th class="mod_redeventcal_daynames" abbr="'.$d.'">&nbsp;'.$d.'&nbsp;</th>';
		}
		$calendar .= "</tr>\n";
	}
	else
	{
		foreach($day_names_short as $d)
		{
			if (function_exists('mb_substr'))
			{
				$calendar .= '<th class="mod_redeventcal_daynames" abbr="'.$d.'">&nbsp;'.mb_substr($d,0,$day_name_length, 'UTF-8').'&nbsp;</th>';
			}
			else
			{
				$calendar .= '<th class="mod_redeventcal_daynames" abbr="'.$d.'">&nbsp;'.substr($d,0,$day_name_length, 'UTF-8').'&nbsp;</th>';
			}
		}
		$calendar .= "</tr>\n";
	}
}
// end days of the week header


// Today
$config =& JFactory::getConfig();
$tzoffset = $config->getValue('config.offset');
$time 		= time()  + ($tzoffset*60*60); //25/2/08 Change for v 0.6 to incorporate server offset into time;
$today 		= date( 'j', $time);
$currmonth 	= date( 'm', $time);
$curryear 	= date( 'Y', $time);

// start displaying the month days
$calendar .= '<tr>';

if ($show_weeknb) {
	$calendar .= '<td class="mod_redeventcal_wk">'.$first_week++.'</td>';
}

for ($counti = 0; $counti < $weekday; $counti++) {
	$calendar .= '<td class="mod_redeventcal">&nbsp;</td>'; #initial 'empty' days
}

for($day = 1, $days_in_month = gmdate('t', $uxtime_first_of_month); $day <= $days_in_month; $day++, $weekday++) 
{	 
	if($weekday == 7){
		$weekday   = 0; #start a new week
		$calendar .= "</tr>\n<tr>";
		if ($show_weeknb) {
			$calendar .= '<td class="mod_redeventcal_wk">'.$first_week++.'</td>';
		}
	}

	if (($day == $today) & ($currmonth == $month) & ($curryear == $year)) {
		$istoday = 1;
	} else {
		$istoday = 0;
	}
	$tdbaseclass = ( $istoday ) ? 'mod_redeventcal_caltoday' : 'mod_redeventcal_calday';

	//space in front of daynumber when day < 10
	($day < 10) ? $space = '&nbsp;&nbsp;': $space = '';

	if (isset($days[$day][1]))
	{
		$link = $days[$day][0];
		$title = $days[$day][1];
			
		if ($Show_Tooltips==1)
		{
			$calendar .= '<td class="'.$tdbaseclass.'link">';
			if ($link)
			{
				$tip = '';
				$title = explode('+', $title);
				if (count( $title ) > 1) {
					$tipTitle = count( $title ) . ' ' . JText::_($CalTooltipsTitle);
				}
				else {
					$tipTitle = '1 ' . JText::_($CalTooltipsTitle);
				}

				foreach ( $title as $t ) {
					$tip .= trim($t) . '<br />';
				}
				$calendar .= JHTML::tooltip($tip, $tipTitle, 'tooltip.png', $space.$day, htmlspecialchars($link));
			}

			$calendar .= '</td>';
		}
		else
		{
			$calendar .= '<td class="'.$tdbaseclass.'link">'.($link ? '<a href="'.htmlspecialchars($link).'">'.$space.$day.'</a>' : $space.$day).'</td>';
		}
	} else {
		$calendar .= '<td class="'.$tdbaseclass.'">'.$space.$day.'</td>';
	}
}
for ($counti = $weekday; $counti < 7; $counti++) {
	$calendar .= '<td class="mod_redeventcal">&nbsp;</td>'; #remaining 'empty' days
}

echo $calendar."</tr>\n</table>\n";

?>
</div>

<?php if ($params->get('toggle', 0)):?>
<div class="cal_toggle"><?php echo JText::_('MOD_REDEVENTCAL_MINIMIZE'); ?></div>
<div class="toggleoff hasTip" title="<?php echo JText::_('MOD_REDEVENTCAL_TOGGLE_TIP');?>"></div>
<?php endif; ?>

</div>