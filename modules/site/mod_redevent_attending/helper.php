<?php
/**
 * @version 0.9 $Id$
 * @package Joomla
 * @subpackage RedEvent
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENCE.php
 * RedEvent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * RedEvent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with RedEvent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'route.php');

/**
 * RedEvent Module helper
 *
 * @package Joomla
 * @subpackage RedEvent Module
 * @since		0.9
*/
class modRedEventAttendingHelper
{

	/**
	 * Method to get the events
	 *
	 * @access public
	 * @return array
	 */
	public function getList(&$params)
	{
		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		if (!$user->get('id')) {
			return false;
		}
		$user_gid	= (int) max($user->getAuthorisedViewLevels());
		$reparams = JComponentHelper::getParams('com_redevent');
		$weekstart = ($reparams->get('week_start', "MO") == "SU" ? 0 : 1);

		$where = array();
		$where[] = 'reg.uid = '.$user->get('id');
		$where[] = 'x.published = 1';
		$where[] = 'reg.cancelled = 0';
		$where[] = 'reg.confirmed = 1';

		$offset = JRequest::getInt('reattoffset', (int) $params->get( 'offset', '0' ));
		$signedoffet = sprintf('%+d', $offset);

		$date_cond = array();
		if ($params->get('includeopen', 0)) {
			$date_cond[] = ' x.dates = 0 ';
		}

		$type = JRequest::getInt('reattspan', $params->get( 'type', '0' ));
		if ($type == 0) // all upcoming dates
		{
			$startdate = strftime('%Y-%m-%d', strtotime('today'.($offset ? ' '.$signedoffet.' days' : '')));
			$date_cond[] = ' (x.dates >= '.$db->Quote($startdate).')';
		}
		else if ($type == 1) // day
		{
			$startdate = strftime('%Y-%m-%d', strtotime('today'.($offset ? ' '.$signedoffet.' days' : '')));
			$date_cond[] = ' (x.dates = '.$db->Quote($startdate).')';
		}
		else if ($type == 2) // week
		{
			$startdate = strftime('%Y-%m-%d', strtotime('last '.($weekstart ? ' monday ' : ' sunday ').($offset ? ' '.$signedoffet.' weeks' : '')));
			$enddate = strftime('%Y-%m-%d', strtotime('next '.($weekstart ? ' monday ' : ' sunday ').($offset ? ' '.$signedoffet.' weeks' : '')));
			$date_cond[] = ' (x.dates >= '.$db->Quote($startdate) .' AND x.dates < '.$db->Quote($enddate).')';
		}
		else if ($type == 3) // month
		{
			$startdate = strftime('%Y-%m-%d', strtotime('first day of this month'.($offset ? ' '.$signedoffet.' months' : '')));
			$enddate = strftime('%Y-%m-%d', strtotime('last day of this month'.($offset ? ' '.$signedoffet.' months' : '')));
			$date_cond[] = ' (x.dates >= '.$db->Quote($startdate) .' AND x.dates <= '.$db->Quote($enddate).')';
		}
		$where[] = '('.implode(" OR ", $date_cond).')';


		$order = ' ORDER BY x.dates, x.times';
		$groupby = ' GROUP BY x.id ';

		//get $params->get( 'count', '2' ) nr of datasets
		$query = 'SELECT a.*, x.id AS xref, x.dates, x.enddates, x.times, x.endtimes, l.venue, l.city, l.url ,'
		. ' CONCAT_WS(",", c.image) AS categories_images,'
		. ' reg.id AS rid, reg.sid, '
		. ' CASE WHEN CHAR_LENGTH(x.title) THEN x.title ELSE a.title END as session_title, '
		. ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title, '
		. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
		. ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug '
		. ' FROM #__redevent_event_venue_xref AS x'
		. ' INNER JOIN #__redevent_register AS reg ON reg.xref = x.id '
		. ' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid'
		. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
		. ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
		. ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
		. ' WHERE '.implode(' AND ', $where)
		. $groupby
		. $order
		;

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		self::_addPrices($rows);

		$i		= 0;
		$lists	= array();
		$title_length = $params->get('cuttitle', '18');

		switch ($params->get('title_type', 0))
		{
			case 1:
				$title_type = 'session_title';
				break;
			case 2:
				$title_type = 'full_title';
				break;
			case 0:
			default:
				$title_type = 'title';
				break;
		}

		foreach ( $rows as $k => $row )
		{
			$rowtitle = $row->$title_type;
			//cut title
			$length = mb_strlen( $rowtitle, 'UTF-8' );
			if ($title_length && $length > $title_length) {
				$rows[$k]->title_short = mb_substr($rowtitle, 0, $title_length, 'UTF-8').'...';
			}
			else {
				$rows[$k]->title_short = $rowtitle;
			}
			// cut venue name
			$length = mb_strlen($row->venue, 'UTF-8');
			if ($title_length && $length > $title_length) {
				$rows[$k]->venue_short = mb_substr($row->venue, 0, $title_length, 'UTF-8').'...';
			}
			else {
				$rows[$k]->venue_short = $row->venue;
			}

			$rows[$k]->link		= JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref));
			//			$rows[$k]->link		= JRoute::_('index.php?option=com_redevent&view=details&id='. $row->slug .'&xref='.$row->xref);
			$rows[$k]->dateinfo 	= self::_builddateinfo($row, $params);
			$rows[$k]->city		= htmlspecialchars( $row->city, ENT_COMPAT, 'UTF-8' );
			$rows[$k]->venueurl 	= !empty( $row->url ) ? self::_format_url($row->url) : JRoute::_(RedeventHelperRoute::getVenueEventsRoute($row->venueslug) , false);
		}

		return $rows;
	}

	/**
	 * Method to a formated and structured string of date infos
	 *
	 * @access public
	 * @return string
	 */
	protected function _builddateinfo($row, &$params)
	{
		if (!RedeventHelper::isValidDate($row->dates)) {
			return JText::_('MOD_REDEVENT_ATTENDING_OPEN_DATE');
		}
		if ($params->get('show_enddate', 1) && strtotime($row->enddates) && $row->dates != $row->enddates) {
			$txt = self::datesSpan($row->dates, $row->enddates, $params->get('formatdate', '%d.%m.%Y'));
		}
		else {
			$txt = self::datesSpan($row->dates, null, $params->get('formatdate', '%d.%m.%Y'));
		}
		$time		= ($row->times && $row->times != '00:00:00') ? self::_format_date($row->dates, $row->times, $params->get('formattime', '%H:%M')) : null;

		if ( isset($time) && $params->get('show_time', 1) ) {
			$txt .= ' <span class="event-time">'.$time.'</span>';
		}

		return $txt;
	}

	/**
	 * Method to get a valid url
	 *
	 * @access public
	 * @return string
	 */
	protected function _format_url($url)
	{
		if(!empty($url) && strtolower(substr($url, 0, 7)) != "http://") {
			$url = 'http://'.$url;
		}
		return $url;
	}

	/**
	 * Method to format date information
	 *
	 * @access public
	 * @return string
	 */
	protected function _format_date($date, $time, $format)
	{
		//format date
		$date = strftime($format, strtotime( $date.' '.$time ));

		return $date;
	}

	public function getSelect(&$params)
	{
		$type = JRequest::getInt('reattspan', $params->get( 'type', '0' ));
		if ($type == 0) {
			return false;
		}
		if ($type == '1') {
			return self::_getDaySelect($params);
		}
		if ($type == '2') {
			return self::_getWeekSelect($params);
		}
		if ($type == '3') {
			return self::_getMonthSelect($params);
		}
	}

	protected function _getDaySelect(&$params)
	{
		$currentoffset = JRequest::getInt('reattoffset', (int) $params->get( 'offset', '0' ));

		$options = array();
		// let's have 5 days on each side
		for ($i = 0; $i < 11; $i++)
		{
			$signedoffset = sprintf('%+d', $currentoffset+$i-5);
			$time = strtotime('today '.$signedoffset.' days');

			$text = strftime(JText::_('COM_REDEVENT_ATTENDING_SELECT_DAY_FORMAT'), $time);
			$text = ($currentoffset+$i-5 == 0) ? '-'.$text.'-' : $text;
			$options[] = JHTML::_('select.option', $currentoffset+$i-5, $text);
		}
		return JHTML::_('select.genericlist', $options, 'reattoffset', 'class="reattoffset"', 'value', 'text', $currentoffset);
	}

	protected function _getWeekSelect(&$params)
	{
		$reparams = JComponentHelper::getParams('com_redevent');
		$weekstart = ($reparams->get('week_start', "MO") == "SU" ? 0 : 1);

		$offset = JRequest::getInt('reattoffset', (int) $params->get( 'offset', '0' ));
		$current = strftime($weekstart ? '%W' : '%U');

		$options = array();
		// let's have 5 weeks on each side
		for ($i = 0; $i < 11; $i++)
		{
			$signedoffet = sprintf('%+d', $offset+$i-5);
			$weekoption_sec = strtotime('today '.$signedoffet.' weeks');
			$start = strftime('%F',strtotime('last '.($weekstart ? 'monday' : 'sunday'), $weekoption_sec));
			$end   = strftime('%F',strtotime('next '.($weekstart ? 'sunday' : 'saturday'), $weekoption_sec));
			$text = self::datesSpan($start, $end);
			$text = ($offset+$i-5 == 0) ? '> '.$text : $text;
			$options[] = JHTML::_('select.option', $offset+$i-5, $text);
		}
		return JHTML::_('select.genericlist', $options, 'reattoffset', 'class="reattoffset"', 'value', 'text', $offset);
	}

	protected function _getMonthSelect(&$params)
	{
		$offset = JRequest::getInt('reattoffset', (int) $params->get( 'offset', '0' ));
		// let's have 5 months on each side
		for ($i = 0; $i < 11; $i++)
		{
			$signedoffet = sprintf('%+d', $offset+$i-5);
			$text = strftime(JText::_('COM_REDEVENT_ATTENDING_SELECT_MONTH_FORMAT'), strtotime('today '.$signedoffet.' months'));
			$text = ($offset+$i-5 == 0) ? '> '.$text : $text;
			$options[] = JHTML::_('select.option', $offset+$i-5, $text);
		}
		return JHTML::_('select.genericlist', $options, 'reattoffset', 'class="reattoffset"', 'value', 'text', $offset);
	}

	/**
	 * special display handling of dates when in same month/year
	 *
	 * @param string $start date
	 * @param string $end date
	 * @return string dates span
	 */
	public function datesSpan($start, $end = null, $full_date_format = null)
	{
		if (!strtotime($start)) {
			return false;
		}
		if (!strtotime($end)) {
			return strftime($full_date_format ? $full_date_format : JText::_('COM_REDEVENT_ATTENDING_FULL_DATE_FORMAT'), strtotime($start));
		}

		$start_month = strftime('%m-%Y', strtotime($start));
		$end_month   = strftime('%m-%Y', strtotime($end));

		if ($start_month == $end_month)
		{
			return JText::sprintf( 'COM_REDEVENT_ATTENDING_DATES_SPAN',
			strftime(JText::_('COM_REDEVENT_ATTENDING_DATES_SPAN_START_DATE_SAME_MONTH_FORMAT'), strtotime($start)),
			strftime(JText::_('COM_REDEVENT_ATTENDING_DATES_SPAN_END_DATE_SAME_MONTH_FORMAT'), strtotime($end))
			);
		}
		else if (substr($start_month, 3) == substr($end_month, 3)) // same year
		{
			return JText::sprintf( 'COM_REDEVENT_ATTENDING_DATES_SPAN',
			strftime(JText::_('COM_REDEVENT_ATTENDING_DATES_SPAN_START_DATE_SAME_YEAR_FORMAT'), strtotime($start)),
			strftime(JText::_('COM_REDEVENT_ATTENDING_DATES_SPAN_END_DATE_SAME_YEAR_FORMAT'), strtotime($end))
			);
		}
		else {
			return JText::sprintf( 'COM_REDEVENT_ATTENDING_DATES_SPAN',
			strftime(JText::_('COM_REDEVENT_ATTENDING_FULL_DATE_FORMAT'), strtotime($start)),
			strftime(JText::_('COM_REDEVENT_ATTENDING_FULL_DATE_FORMAT'), strtotime($end))
			);
		}
	}

	/**
	 * adds submission price to rows	 *
	 *
	 * @param array $rows
	 * @return boolean true on success
	 */
	protected function _addPrices(&$rows)
	{
		if (!count($rows)) {
			return true;
		}
		$rids = array();
		foreach ($rows as $k => $r)
		{
			if ($r->rid) {
				$rids[] = $r->rid;
			}
		}

		$db = & JFactory::getDBO();
		$query =  ' SELECT SUM(s.price) as price, r.id, s.currency AS currency '
		. ' FROM #__redevent_register AS r '
		. ' INNER JOIN #__rwf_submitters AS s ON s.id = r.sid '
		. ' INNER JOIN #__rwf_forms AS f ON f.id = s.form_id '
		. ' WHERE r.id IN ('.implode(", ", $rids).')'
		. ' GROUP BY r.id '
		;
		$db->setQuery($query);
		$res = $db->loadObjectList('id');

		foreach ($rows as $k => $r)
		{
			if (isset($res[$r->rid])) {
				$rows[$k]->price = $res[$r->rid]->price;
				$rows[$k]->currency = $res[$r->rid]->currency;
			}
			else {
				$rows[$k]->price = 0;
				$rows[$k]->currency = null;
			}
		}
		return true;
	}

	public function getTotal($rows)
	{
		if (!count($rows)) {
			return 0;
		}
		$currency = null;
		$total = 0;
		foreach ($rows as $r)
		{
			if ($currency && $currency != $r->currency) { // can't sum if not the same currency
				return false;
			}
			$total += $r->price;
			$currency = $r->currency;
		}
		return $total;
	}

	/**
	 * doesn't do anything special, but maybe could call a function more advanced in the future
	 *
	 * @param int $price
	 * @param string $currency
	 * @return string
	 */
	public function printPrice($price, $currency)
	{
		if (!$price) {
			return '-';
		}
		return $currency. ' ' . sprintf('%.2f', $price);
	}
}
