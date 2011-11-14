<?php
/**
 * @version   1.6 October 6, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

class RokMiniEventsSourceGoogle extends RokMiniEvents_SourceBase
{
    function getEvents(&$params)
    {
        jimport('simplepie.simplepie');

        $id = $params->get('google_gid', '');
        $starttime = $params->get('google_orderby', 'starttime');

        $query = '?singleevents=true&orderby=' . $starttime.'&sortorder=a&max-results='.$params->get('google_maxresults',25);

        if ($params->get('time_range') != 'time_span' && $params->get('rangespan') == 'all_events')
        {
            $query .= '&futureevents=true';
        }
        else {
            $startMin = $params->get('startmin');

            $query .= "&start-min=" . $startMin;
            $startMax = $params->get('startmax',false);
            if ($startMax !== false)
            {
                $query .= "&start-max=" . $startMax;
            }
        }

        $rss = new SimplePie('http://www.google.com/calendar/feeds/' . $id . '/public/full' . $query, JPATH_CACHE, $params->get('google_gcache', 3600));
        $rss->enable_order_by_date(false);

        $events = array();

        if ($rss->error) return $events['error'] = $rss->error;

        $items =$rss->get_items();

        foreach ($items as $item)
        {
            $when = $item->get_item_tags('http://schemas.google.com/g/2005', 'when');
			$link = ($params->get('google_links') != 'link_no') ? 
				array(
					'internal' => ($params->get('google_links') == 'link_internal') ? true : false,
					'link' => $item->get_link() 
				)
				: 
				false;
			
            $event = new RokMiniEvents_Event($when[0]['attribs']['']['startTime'],
                                             $when[0]['attribs']['']['endTime'],
                                             $item->get_title(), $item->get_content(), $link);

            $startTime = $when[0]['attribs']['']['startTime'];
            $endTime = $when[0]['attribs']['']['endTime'];

            if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$startTime)){
                $event->setAllDay(true);
            }
            $events[]=$event;
        }
        return $events;
    }

    function available()
    {
        return true;
    }
}
