<?php
/** 
 * @copyright Copyright (C) 2010 redCOMPONENT.com. All rights reserved. 
 * @license GNU/GPL, see LICENSE.php
 * autotweetredevent can be downloaded from www.redcomponent.com
 * autotweetredevent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * autotweetredevent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with autotweetredevent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.error.error');

// Check for component
if (!JComponentHelper::getComponent('com_autotweet', true)->enabled)
{
	JError::raiseWarning('5', 'AutoTweet NG Content-Plugin - AutoTweet NG Component is not installed or not enabled.');
	return;
}
require_once JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_autotweet' . DS . 'helpers' . DS . 'autotweetbase.php';
JTable::addIncludePath(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_autotweet' . DS . 'tables');

// check for redEVENT extension
if (!JComponentHelper::getComponent('com_redevent', true)->enabled) {
	JError::raiseWarning('5', 'AutoTweet NG redEVENT-Plugin - redEVENT extension is not installed or not enabled.');
	return;
}
// redevent
include_once(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'route.php');

/**
 * redEVENT extension plugin for AutoTweet.
 *
  */
class plgSystemAutotweetRedevent extends plgAutotweetBase
{
	protected $text_template = '';
	
	protected $date_format = '';
	
	public function __construct( &$subject, $params )
	{
		parent::__construct( $subject, $params );
		
		// Get Plugin info
		$pluginParams = $this->pluginParams;
		
		$this->text_template = $pluginParams->get('text_template', JText::sprintf('PLG_SYSTEM_AUTOTWEET_REDEVENT_TEXT_TEMPLATE'));
		$this->date_format   = $pluginParams->get('date_format', JText::sprintf('PLG_SYSTEM_AUTOTWEET_REDEVENT_DATE_FORMAT'));
	}

	/**
	 * triggered after a session gets saved
	 * @return boolean
	 */
	public function onAfterRedeventSessionSave($xref)
	{
		$db = &JFactory::getDBO();
		
		$db = &JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('x.id, x.dates, x.times');
		$query->select('e.title');
		$query->select('v.venue');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->where('x.id = '.(int) $xref);
		$db->setQuery($query);
		$res = $db->loadObject();
		
		$date = strtotime($res->dates) ? JFactory::getDate($res->dates) : false;
		
		$this->postStatusMessage($res->id, JFactory::getDate()->toFormat(), $res->title.'@'.$res->venue.($date ? $date->format(' \o\n Y-m-d') : ''), 'eventsession');
	
		return true;
	}
	
	/**
	 * returns data to be inserted on tweeter
	 * 
	 * @param int $id
	 * @param string $typeinfo
	 * @return array title, text, hash, url
	 */
	public function getData($id, $typeinfo)
	{		
		// $typeinfo not used
		$db = & JFactory::getDBO();
    $user = & JFactory::getUser();
		
    $query = ' SELECT a.id, a.title, a.summary, a.dates, a.times, '
           . ' x.id as xref, v.venue, '
           . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug '
           . ' FROM #__redevent_events AS a '
           . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id '
           . ' INNER JOIN #__redevent_venues AS v ON v.id = x.venueid '
           . ' WHERE x.id = ' . $id;
    $db->setQuery($query);
    
    if (!$event = $db->loadObject()) {
      if ($db->getErrorNum()) {
        RedeventError::raiseWarning('0', $db->getErrorMsg());
      }
    	return false;
    }
    
		// return values
		$data = array (
			'title'		=> $event->title,
			'text'		=> $event->title,
			'hashtags'	=> '',
			'url'		=> RedeventHelperRoute::getDetailsRoute($event->slug, $event->xref),
			'fulltext' => $event->summary,
		);	
		
		return $data;
	}
	
	protected function getFulltext($event)
	{
		$app = JFactory::getApplication();
		$text = str_replace("{site_name}", $app->getCfg('sitename'), $this->text_template);
		$text = str_replace("{title}", $event->title, $text);
		$text = str_replace("{venue}", $event->venue, $text);
		if (!strtotime($event->dated)) {
			$text = str_replace("{date}",  '', $text);
		}
		else {
			$date = JFactory::getDate($event->dates);
			$text = str_replace("{date}",  $date->format($this->date_format), $text);
		}
	}
}	
