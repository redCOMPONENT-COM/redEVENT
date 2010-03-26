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

jimport( 'joomla.error.error' );

// check for component
if (!JComponentHelper::getComponent('com_autotweet', true)->enabled) {
	JError::raiseWarning('5', 'AutoTweet NG redEVENT-Plugin - AutoTweet NG Component is not installed or not enabled.');
	return;
}

// check for redEVENT extension
if (!JComponentHelper::getComponent('com_redevent', true)->enabled) {
	JError::raiseWarning('5', 'AutoTweet NG redEVENT-Plugin - redEVENT forum extensions is not installed or not enabled.');
	return;
}

require_once (JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_autotweet' . DS . 'helpers' . DS . 'autotweetbase.php');


/**
 * redEVENT extension plugin for AutoTweet.
 *
  */
class plgSystemAutotweetRedevent extends plgAutotweetBase
{
	function plgSystemAutotweetRedevent( &$subject, $params )
	{
		parent::__construct( $subject, $params );
		
		// Get Plugin info
		$plugin		=& JPluginHelper::getPlugin('system', 'autotweetredevent');
		$pluginParams	= new JParameter($plugin->params);
	}

	// handles normal forum post
	function onAfterDispatch()
	{
		$db = &JFactory::getDBO();
		
		$query = " SELECT ".$db->NameQuote('id').", ".$db->NameQuote('title').
			 " FROM ".$db->NameQuote('#__redevent_events').
			 " WHERE ".$db->NameQuote('id')." = '".JRequest::getVar('twit_id')."'";
		$db->setQuery($query);
		$row = $db->loadObjectList();	
		
		$url = 'index.php?option=com_redevent&view=simplelist';
			
		// Only post if data doesent already exist in the database
		if (JRequest::getVar('option') == "com_redevent"
		    && JRequest::getVar('view') == "events"
		    && JRequest::getVar('twit_id') != "")
		{
			$this->postTwitterStatusMessage ($row[0]->id, JFactory::getDate()->toFormat(), $row[0]->title, $url);
		}
	
		return true;
	}
}	
?>
