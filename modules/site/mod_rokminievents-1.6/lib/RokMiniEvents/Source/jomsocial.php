<?php
/**
 * @version   1.6 October 6, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

class RokMiniEventsSourceJomSocial extends RokMiniEvents_SourceBase
{
    function getEvents(&$params)
    {
        require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );
        CFactory::load( 'helpers' , 'event' );
        CFactory::load( 'helpers' , 'string' );
        CFactory::load( 'helpers' , 'time' );


        // Reuse existing language file from JomSocial
        $language	= JFactory::getLanguage();
        $language->load( 'com_community' , JPATH_ROOT );

        $model		= CFactory::getModel( 'Events' );
        $user_id = null;
//        if ((bool) $params->get( 'jomsocial_user' , false )){
//            $user = JFactory::getUser();
//            $user_id = $user->id;
//        }


        $advanced = null;
        if ($params->get('time_range') == 'time_span' || $params->get('rangespan') != 'all_events')
        {
            $advanced = array();
            $advanced['startdate'] = $params->get('startmin');
            $startMax = $params->get('startmax',false);
            if ($startMax !== false)
            {
                $advanced['enddate'] = $startMax;
            }
        }


        $rows		= $model->getEvents( $params->get( 'jomsocial_category' , 0) , 0 , null , null , (bool) $params->get( 'jomsocial_past' , false ) , false , null , $advanced , $params->get( 'jomsocial_type' , CEventHelper::ALL_TYPES ) , 0 , $params->get( 'jomsocial_total' , 10 ) );
        $events		= array();
        foreach( $rows as $row )
        {
            $table		= JTable::getInstance( 'Event' , 'CTable' );
	        $table->bind( $row );
            $handler	= CEventHelper::getHandler( $table );

			if ($params->get('jomsocial_links') != 'link_no'){
            	$link = array(
					'internal' => ($params->get('jomsocial_links') == 'link_internal') ? true : false,
					'link' => $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $table->id )
				);
			} else {
				$link = false;
			}
            if ($row->offset != 0){
                $start = new RokMiniEvents_Date($row->startdate);
                $start->setOffset($row->offset*-1);
                $row->startdate = $start->toMySQL(true);
                $end = new RokMiniEvents_Date($row->enddate);
                $end->setOffset($row->offset*-1);
                $row->enddate = $end->toMySQL(true);
            }
			$event = new RokMiniEvents_Event($row->startdate,$row->enddate,$row->title,$row->description, $link);
            $events[] = $event;
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
        $db =JFactory::getDBO();
        $query = 'select count(*) from #__components as a where a.option = ' . $db->Quote('com_community');
		$db->setQuery($query);
		$count = (int)$db->loadResult();
        if ($count > 0)
            return true;

        return false;
    }
}
