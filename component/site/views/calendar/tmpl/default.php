<?php
/**
 * @version 1.1 $Id: default.php 1078 2009-06-29 18:15:43Z schlu $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright (C) 2005 - 2009 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die ('Restricted access');

// build calendar
$countcatevents = array ();

foreach ($this->rows as $row)
{
	//get event date
	$year = strftime('%Y', strtotime($row->dates));
	$month = strftime('%m', strtotime($row->dates));
	$day = strftime('%d', strtotime($row->dates));

	//for time printing
	$timehtml = '';

	if ($this->params->get('show_tip_time', 0))
	{
		$start = RedeventHelperDate::formattime($row->dates, $row->times);
		$end = RedeventHelperDate::formattime($row->dates, $row->endtimes);

		if ($start != '')
		{
			$timehtml = '<div class="time"><span class="label">'.JText::_('COM_REDEVENT_TIME').': </span>';
			$timehtml .= $start;
			if ($end != '') {
				$timehtml .= ' - '.$end;
			}
			$timehtml .= '</div>';
		}
	}

	$eventname = '<div class="eventName">'.$this->escape(RedeventHelper::getSessionFullTitle($row)).'</div>';

	//initialize variables
	$colorpic = '';
	$content = '';
	$contentend = '';
	if ($this->params->get('showdetails', 1)) {
		$detaillink = RedeventHelperRoute::getDetailsRoute($row->slug, $row->xslug);
	}
	else {
		$detaillink = null;
	}
	$cat_classes = array();
	$cat_names = array();
	//walk through categories assigned to an event
	foreach($row->categories AS $category)
	{
		$cat_classes[] = 'cat'.$category->id;

		//attach category color if any in front of the catname
		if ($category->color) {
			$cat_names[] = '<span class="colorpic" style="background-color: '.$category->color.';"></span>'.$category->name;
		}
		else {
			$cat_names[] = $category->name;
		}

		//attach category color if any in front of the event title in the calendar overview
		if ( isset ($category->color) && $category->color) {
			$colorpic .= '<span class="colorpic" style="background-color: '.$category->color.';"></span>';
		}

		//count number of events per category
		if (!array_key_exists($category->id, $countcatevents)) {
			$countcatevents[$category->id] = 1;
		}
		else {
			$countcatevents[$category->id]++;
		}
	}
	//wrap a div for each category around the event for show hide toggler
	$content    .= '<div class="'.implode(' ', $cat_classes).($row->featured ? ' featured' : '').'">';
	$contentend   .= '</div>';

	$catname = '<div class="catname">'.implode(', ', $cat_names).'</div>';

	$eventdate = RedeventHelperDate::formatdate($row->dates, $row->times);

	//venue
	if ($this->params->get('showlocate', 1) == 1)
	{
		$venue = '<div class="location"><span class="label">'.JText::_('COM_REDEVENT_VENUE').': </span>';

		if ($this->params->get('showlinkvenue',1) == 1 && 0) {
			$venue .= $row->locid ? "<a href='".'index.php?option=com_redevent&view=venueevents&id='.$row->venueslug."'>".$this->escape($row->venue)."</a>" : '-';
		}
		else {
			$venue .= $row->locid ? $this->escape($row->venue) : '-';
		}
		$venue .= '</div>';
	}
	else {
		$venue = '';
	}

	//generate the output
	$content .= $colorpic;
	if ($this->params->get('show_start_time', 0))
	{
		$content .= RedeventHelperDate::formattime($row->dates, $row->times) . ' ';
	}

	// Text to display in calendar
	if ($this->params->get('session_display', 0) == 0 || !$row->datimage)
	{
		$text = RedeventHelper::getSessionFullTitle($row);
	}
	elseif ($this->params->get('session_display', 0) == 1)
	{
		$img = RedeventImage::getThumbUrl($row->datimage, $this->params->get('pic_size', 20));
		$text = '<span class="session-image">' . JHTML::image($img, RedeventHelper::getSessionFullTitle($row)) . '</span>';
	}
	else
	{
		$img = RedeventImage::getThumbUrl($row->datimage, $this->params->get('pic_size', 20));
		$text = '<span class="session-image">' . JHTML::image($img, RedeventHelper::getSessionFullTitle($row)) . '</span>';
		$text .= RedeventHelper::getSessionFullTitle($row);
	}

	$content .= $this->caltooltip($catname.$eventname.$timehtml.$venue, $eventdate, $text, $detaillink, 'eventTip');
	$content .= $contentend;
	// add the event to the calendar
	$this->cal->setEventContent($year, $month, $day, $content);

}
?>
<div id="redevent" class="jlcalendar">
    <?php if ($this->params->def('show_page_title', 1)): ?>
    	<h1 class="componentheading">
        	<?php echo $this->escape($this->params->get('page_title')); ?>
    	</h1>
    <?php endif; ?>

<?php
  // print the calendar
  if ($this->params->get('show_week_num', 1)) {
  	$this->cal->enableWeekNum($this->params->get('week_column_name'));
  }
  print ($this->cal->showMonth());
  //return;
?>
</div>

<div id="jlcalendarlegend">

    <div id="buttonshowall">
        <?php echo JText::_('COM_REDEVENT_SHOWALL'); ?>
    </div>

    <div id="buttonhideall">
        <?php echo JText::_('COM_REDEVENT_HIDEALL'); ?>
    </div>

    <?php if($this->params->get('displayLegend')) :

		//walk through events
		foreach ($this->categories as $category):
			$eventsCount = isset($countcatevents[$category->id]) ? $countcatevents[$category->id] : 0;

			//build legend
			if ($this->params->get('showAllCategories', 0) || $eventsCount): ?>
				<div class="eventCat" catid="<?php echo $category->id; ?>">
					<?php if (isset($category->color) && $category->color): ?>
						<span class="colorpic" style="background-color: <?php echo $category->color; ?>"></span>
					<?php endif;
					echo $category->name . ' (' . $eventsCount . ')';
					?>
				</div>
			<?php endif;

		endforeach;
	endif; ?>
</div>

<div class="clr"/></div>
