<?php
/**
 * @version   1.6 October 6, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

JLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');

if (!defined('REDEVENT_PATH_SITE')) DEFINE('REDEVENT_PATH_SITE', JPATH_SITE.DS.'components'.DS.'com_redevent');

class RokMiniEventsSourceRedEvent extends RokMiniEvents_SourceBase
{
    function getEvents(&$params)
    {
    	include_once(REDEVENT_PATH_SITE.DS.'classes'.DS.'output.class.php');
    	include_once(REDEVENT_PATH_SITE.DS.'helpers'.DS.'route.php');
    	include_once(REDEVENT_PATH_SITE.DS.'helpers'.DS.'helper.php');
    	include_once('redevent'.DS.'model.php');

        // load language file
        $language = JFactory::getLanguage();
        $language->load('com_redevent', JPATH_ROOT);

	    	$model = new RokMiniEventsSourceRedEventModel($params);
	    	$rows = $model->getData();

        $total_count = 1;
        $total_max = $params->get('redevent_total',10);
        $events = array();
        foreach ($rows as $row)
        {
            if ($params->get('redevent_links') != 'link_no')
            {

                $link = array(
                    'internal' => ($params->get('redevent_links') == 'link_internal') ? true : false,
                    'link' => JRoute::_(REdeventHelperRoute::getDetailsRoute($row->slug, $row->xslug))
                );
            } else
            {
                $link = false;
            }

            date_default_timezone_set('UTC');
            $offset = 0;
            if ($params->get('redevent_dates_format', 'utc') == 'joomla'){
                $conf =& JFactory::getConfig();
                $timezone = $conf->getValue('config.offset') ;
                $offset = $timezone * 3600 * -1;
            }

	        $startdate = strtotime($row->dates . ' ' . $row->times)+$offset;
            $enddate = $row->enddates ? strtotime($row->enddates . ' ' . $row->endtimes)+$offset : strtotime($row->dates . ' ' . $row->endtimes)+$offset;
            $event = new RokMiniEvents_Event($startdate, $enddate, $row->title, $row->summary, $link);
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
        $query = 'select count(*) from #__components as a where a.option = ' . $db->Quote('com_redevent');
        $db->setQuery($query);
        $count = (int)$db->loadResult();
        if ($count > 0)
            return true;

        return false;
    }
}
