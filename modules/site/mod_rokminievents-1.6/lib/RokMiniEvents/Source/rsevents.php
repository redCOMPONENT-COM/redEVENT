<?php
/**
 * @version   1.6 October 6, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');


class RokMiniEventsSourceRSEvents extends RokMiniEvents_SourceBase
{
    function getEvents(&$params)
    {
        require_once(JPATH_SITE.'/components/com_rsevents/helpers/rsevents.php');
        require_once(JPATH_SITE.'/components/com_rsevents/helpers/events.php');

        // Reuse existing language file from JomSocial
        $language = JFactory::getLanguage();
        $language->load('com_rsevents', JPATH_ROOT);


        $query_start_date = null;
        $query_end_date = null;

        if ($params->get('time_range') == 'time_span' || $params->get('rangespan') != 'all_events')
        {
            $query_start_date = $params->get('startmin');
            $startMax = $params->get('startmax', false);
            if ($startMax !== false)
            {
                $query_end_date = $startMax;
            }
        }

        $db =& JFactory::getDBO();
        $user =& JFactory::getUser();
        $user_gid = (int)$user->get('aid');


//        $venid = trim($params->get('rsevents_venue', 0));

        $categories = '';
        $catids = $params->get('rsevents_category', null);
        if (isset($catids) & $catids != 0)
        {
            $catids = explode(",",$catids);
            $outcats = $catids;
            foreach($catids as $catid){
                $childcats = self::getChildCategories($catid, $outcats);
            }
            $categories = ' AND e.IdEvent in (Select IdEvent from #__rsevents_events_cat where IdCategory in (' . implode(',',$outcats) . '))';
        }
        $venues = '';
        $locationids = $params->get('rsevents_venue', null);
        if (isset($locationids) && $locationids != 0)
        {
            $venues  = ' AND e.IdLocation in ('.$locationids. ')';
        }

        $dates_start='';
        if (!empty($query_start_date)){
            $rstartdate = strtotime($query_start_date);
            if ($params->get('rsevents_past', 0) == 0 && $rstartdate < time()) {
                $rstartdate = time();
            }
            $dates_start = ' AND e.EventStartDate >= ' . $rstartdate;
        }
        else if ($params->get('rsevents_past', 0) == 0){
            $rstartdate = time();
            $dates_start = ' AND e.EventStartDate >= ' . $rstartdate;
        }

        $dates_end ='';
        if (!empty($query_end_date)){
            $renddate = strtotime($query_end_date);
            $dates_end = ' AND e.EventStartDate <= ' . $renddate;
        }

        $query='SELECT e.IdEvent as id, e.EventName as title ,e.EventDescription as description, e.EventStartDate as startdate, e.EventEndDate as enddate'
						.' FROM #__rsevents_events e'
                        .' '
						.' WHERE e.published = 1'
                        . $categories
                        . $venues
                        . $dates_start
                        . $dates_end
						.' GROUP BY e.IdEvent'
						.' ORDER BY e.EventStartDate ASC';

        $db->setQuery($query);
        $rows = $db->loadObjectList();


        $events = array();
        $total_count = 1;
        $total_max = $params->get('rsevents_total',10);
        foreach ($rows as $row)
        {
            if ($params->get('rsevents_links') != 'link_no')
            {

                $link = array(
                    'internal' => ($params->get('rsevents_links') == 'link_internal') ? true : false,
                    'link' => rseventsHelper::route('index.php?option=com_rsevents&view=events&layout=show&cid='.eventsHelper::sef($row->id,$row->title),false)
                );
            } else
            {
                $link = false;
            }

            $event = new RokMiniEvents_Event($row->startdate, $row->enddate, $row->title, $row->description, $link);
            $events[] = $event;
            $total_count++;
            if ($total_count > $total_max) break;
        }

        //$events = array();
        return $events;
    }

    /**
     * Checks to see if the source is available to be used
     * @return bool
     */
    function available()
    {
        $db =& JFactory::getDBO();
        $query = 'select count(*) from #__components as a where a.option = ' . $db->Quote('com_rsevents');
        $db->setQuery($query);
        $count = (int)$db->loadResult();
        if ($count > 0)
            return true;

        return false;
    }

	function getChildCategories($id, &$list)
	{
		$db =& JFactory::getDBO();

		// Initialize variables
		$return = true;

		// Get all rows with parent of $id
		$query = 'SELECT IdCategory as id' .
				' FROM #__rsevents_categories' .
				' WHERE parent = '.(int) $id;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		// Make sure there aren't any errors
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Recursively iterate through all children... kinda messy
		foreach ($rows as $row)
		{
			$found = false;
			foreach ($list as $idx)
			{
				if ($idx == $row->id) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				$list[] = $row->id;
			}
			$return = self::getChildCategories($row->id, $list);
		}
		return $return;
	}
}
