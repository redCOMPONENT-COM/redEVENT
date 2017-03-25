<?php
/**
 * @version   1.6 October 6, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

class RokMiniEventsSourceJEvents extends RokMiniEvents_SourceBase
{
    function getEvents(&$params)
    {
        // Reuse existing language file from JomSocial
        $language = JFactory::getLanguage();
        $language->load('com_jevents', JPATH_ROOT);


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


        // setup for all required function and classes
		$file = JPATH_SITE . '/components/com_jevents/mod.defines.php';
		if (file_exists($file) ) {
			include_once($file);
			include_once(JEV_LIBS."/modfunctions.php");

		} else {
			die ("JEvents Calendar\n<br />This module needs the JEvents component");
		}

		// load language constants
		JEVHelper::loadLanguage('modlatest');



        $datamodel	= new JEventsDataModel();

        $showrepeats = ($params->get('jevents_norepeats',0) == 0)?true:false;
        //$myItemid = $this->datamodel->setupModuleCatids($this->modparams);
		//$catout	= $this->datamodel->getCatidsOutLink(true);


        $params->set('catid0', $params->get('jevents_category', 0));
        $myItemid = $datamodel->setupModuleCatids($params);
		$catout	= $datamodel->getCatidsOutLink(true);

        $reg = & JevRegistry::getInstance("jevents");
		$reg->setReference("jevents.datamodel",$datamodel);


        if (!empty($query_start_date)){
            $rstartdate = new RokMiniEvents_Date($query_start_date);
            if ($params->get('jevents_past', 0) == 0 && $rstartdate->toUnix() < time()) {
                $rstartdate = new RokMiniEvents_Date(time());
            }
            $dates_start = $rstartdate->toISO8601();
        }
        else if ($params->get('jevents_past', 0) == 0){
            $rstartdate = new RokMiniEvents_Date(time());
            $dates_start = $rstartdate->toISO8601();
        }
        else {
            $dates_start = date('Y-m-d\T23:59:59', strtotime("-1 month"));
        }

        if (empty($query_end_date)){
            $dates_end = date('Y-m-d\T23:59:59', strtotime("+1 year"));
        }
        else{
            $dates_end = $query_end_date;
        }

        $rows = $datamodel->queryModel->listIcalEventsByRange($dates_start, $dates_end, 0,0,$showrepeats);

        $events = array();
        $total_count = 1;
        $total_max = $params->get('jevents_total',10);
        foreach ($rows as $row)
        {
            if ($params->get('jevents_links') != 'link_no')
            {

                $link = array(
                    'internal' => ($params->get('jevents_links') == 'link_internal') ? true : false,
                    'link' => self::getCalendarLink($myItemid,$catout)
                );
            } else
            {
                $link = false;
            }

            $event = new RokMiniEvents_Event($row->_unixstarttime, $row->_unixendtime, $row->_title, $row->_content, $link);
            $events[] = $event;
            $total_count++;
            if ($total_count > $total_max) break;
        }
        return $events;
    }

    /**
     * Checks to see if the source is available to be used
     * @return boolean
     */
    function available()
    {
        $db =JFactory::getDBO();
        $query = 'select count(*) from #__components as a where a.option = ' . $db->Quote('com_jevents');
        $db->setQuery($query);
        $count = (int)$db->loadResult();
        if ($count > 0)
            return true;

        return false;
    }

    function getCalendarLink($myItemid, $catout){
		$menu =& JApplication::getMenu('site');
		$menuItem = $menu->getItem($myItemid);
		if ($menuItem && $menuItem->component == JEV_COM_COMPONENT){
			$task=$menuItem->query["task"] ;
		}
		else {
			$task="month.calendar";
		}
		return JRoute::_("index.php?option=".JEV_COM_COMPONENT .  "&Itemid=". $myItemid ."&task=".$task. $catout, true);
	}
}
