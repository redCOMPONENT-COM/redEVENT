<?php
/**
 * @version 1.0 $Id: output.class.php 1719 2009-11-23 17:05:54Z julien $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Holds the logic for all output related things
 *
 * @package Joomla
 * @subpackage redEVENT
 */
class UserAcl {
	
	var $_groups = null;
	
	var $_userid = 0;
	
	var $_db = null;
	
	function __construct($userid = 0)
	{
		$this->_db = &JFactory::getDBO();
		
		if (!$userid) {
			$user = &Jfactory::getUser();
			$userid = $user->get('id');
		}
		$this->_userid = $userid;
	}
	
	/**
	 * Returns a reference to the global User object, only creating it if it
	 * doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $user =& JUser::getInstance($id);</pre>
	 *
	 * @access 	public
	 * @param 	int 	$id 	The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 * @return 	JUser  			The User object.
	 * @since 	1.5
	 */
	function &getInstance($id = 0)
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		// Find the user id
		if(!$id)
		{
			$user = &Jfactory::getUser();
			$id = $user->get('id');
		}

		if (empty($instances[$id])) {
			$inst = new UserAcl($id);
			$instances[$id] = $inst;
		}

		return $instances[$id];
	}
	
	/**
	 * returns true if the user can add events
	 * 
	 * @return boolean
	 */
	function canAddEvent()
	{
		$groups = $this->getUserGroups();
		foreach ((array) $groups as $group)
		{
			if ($group->manage_events == 1 || $group->params->get('add_event', 0)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * returns true if the user can add venues
	 * 
	 * @return boolean
	 */
	function canAddVenue()
	{
		$groups = $this->getUserGroups();
		foreach ((array) $groups as $group)
		{
			if ($group->edit_venues == 1 || $group->params->get('add_venue', 0)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * return true if the user can edit specified event
	 * @param int $eventid
	 * @return boolean
	 */
	function canEditEvent($eventid)
	{
		$db = &JFactory::getDBO();
		
		$query = ' SELECT e.id '
		       . ' FROM #__redevent_events AS e '
		       . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
		       . ' INNER JOIN #__redevent_groups_categories AS gc ON gc.category_id = xcat.category_id '
		       . ' LEFT JOIN #__redevent_groups AS g ON g.id = gc.group_id '
		       . ' LEFT JOIN #__redevent_groupmembers AS gm ON gm.group_id = gc.group_id '
		       . ' WHERE e.id = '. $db->Quote($eventid)
		       . '   AND (gm.member = '.$db->Quote($this->_userid).' OR g.isdefault = 1) '
		       . '   AND gc.accesslevel > 0 '
		       . '   AND ( gm.manage_events > 0 OR g.edit_events = 2 '
		       . '         OR (g.edit_events = 1 AND e.created_by = '.$db->Quote($this->_userid).') )'
		       ;
		$db->setQuery($query);
//		echo($db->getQuery());
		return ($db->loadResult() ? true : false);
	}
	
	function canPublishEvent($eventid = 0)
	{
		if (!$eventid) // this is a new event
		{		
			$query = ' SELECT g.id '
			       . ' FROM #__redevent_groups AS g '
			       . ' LEFT JOIN #__redevent_groupmembers AS gm ON gm.group_id = g.id '
			       . ' WHERE ( gm.member = '.$this->_db->Quote($this->_userid).' AND gm.publish_events > 0 ) '
			       . '   OR ( g.isdefault = 1 AND g.publish_events > 0 ) '
			       ;		
		}
		else
		{
			$query = ' SELECT e.id '
			       . ' FROM #__redevent_events AS e '
			       . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
			       . ' INNER JOIN #__redevent_groups_categories AS gc ON gc.category_id = xcat.category_id '
			       . ' LEFT JOIN #__redevent_groups AS g ON g.id = gc.group_id '
			       . ' LEFT JOIN #__redevent_groupmembers AS gm ON gm.group_id = gc.group_id '
			       . ' WHERE e.id = '. $this->_db->Quote($eventid)
			       . '   AND ( ( g.isdefault = 1 AND (g.publish_events = 2 OR (g.publish_events = 1 AND e.created_by = '.$this->_db->Quote($this->_userid).') ) ) '
			       . '      OR ( gm.publish_events = 2 OR (gm.publish_events = 1 AND e.created_by = '.$this->_db->Quote($this->_userid).') ) ) '
			       ;			
		}
		$this->_db->setQuery($query);
//		echo($db->getQuery());
		return ($this->_db->loadResult() ? true : false);	
	}
	
	/**
	 * return true if the user can edit specified xref
	 * @param int xref
	 * @return boolean
	 */
	function canEditXref($xref)
	{
		$db = &JFactory::getDBO();

		$query = ' SELECT e.id '
		       . ' FROM #__redevent_events AS e '
		       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
		       . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
		       . ' INNER JOIN #__redevent_groups_categories AS gc ON gc.category_id = xcat.category_id '
		       . ' LEFT JOIN #__redevent_groups_venues AS gv ON gv.venue_id = x.venueid AND gv.group_id = gc.group_id '
		       . ' LEFT JOIN #__redevent_groups AS g ON g.id = gc.group_id '
		       . ' LEFT JOIN #__redevent_groupmembers AS gm ON gm.group_id = gc.group_id '
		       . ' WHERE x.id = '. $db->Quote($xref)
		       . '   AND (gm.member = '.$db->Quote($this->_userid).' OR g.isdefault = 1) '
		       . '   AND gc.accesslevel > 0 AND gv.accesslevel > 0 '
		       . '   AND ( gm.manage_xrefs > 0 OR gm.manage_events > 0 OR g.edit_events = 2 '
		       . '         OR (g.edit_events = 1 AND e.created_by = '.$db->Quote($this->_userid).') )'
		       ;
		$db->setQuery($query);
		return ($db->loadResult() ? true : false);
	}
	
	/**
	 * return true if the user can edit specified event
	 * @param int $eventid
	 * @return boolean
	 */
	function canEditVenue($id)
	{
		$db = &JFactory::getDBO();
		
		$query = ' SELECT v.id '
		       . ' FROM #__redevent_venues AS v '
		       . ' INNER JOIN #__redevent_groups_venues AS gv ON gv.venue_id = v.id '
		       . ' LEFT JOIN #__redevent_groups AS g ON g.id = gv.group_id '
		       . ' LEFT JOIN #__redevent_groupmembers AS gm ON gm.group_id = gv.group_id '
		       . ' WHERE v.id = '. $db->Quote($id)
		       . '   AND (gm.member = '.$db->Quote($this->_userid).' OR g.isdefault = 1) '
		       . '   AND gv.accesslevel > 0 '
		       . '   AND ( gm.edit_venues > 0 OR g.edit_venues = 2 '
		       . '         OR (g.edit_venues = 1 AND v.created_by = '.$db->Quote($this->_userid).') )'
		       ;
		$db->setQuery($query);
//		echo($db->getQuery());
		return ($db->loadResult() ? true : false);
	}
	
	/**
	 * get user groups
	 * 
	 * @return array
	 */	
	function getUserGroups()
	{
		if (empty($this->_groups))
		{
			$db = &JFactory::getDBO();
			
			$query = ' SELECT g.id AS group_id, g.name AS group_name, g.parameters, g.isdefault, '
			       . '   gm.member AS user_id, gm.manage_events, gm.manage_xrefs, gm.edit_venues '
			       . ' FROM #__redevent_groups AS g '
			       . ' LEFT JOIN #__redevent_groupmembers AS gm ON gm.group_id = g.id ' 
			       . ' WHERE isdefault = 1 '
			       . '    OR gm.member = '. $db->Quote($this->_userid);
			$db->setQuery($query);
			$groups = $db->loadObjectList();
			
			foreach ((array) $groups as $group)
			{
				$params = new JParameter( $group->parameters, JPATH_ADMINISTRATOR.DS.'components'.DS.'com_redevent'.DS.'models'.DS.'group.xml' );
				$group->params = $params;
				$this->_groups[$group->group_id] = $group;
			}			
		}
		return $this->_groups;
	}
	
	/**
	 * returns default group if set
	 * 
	 * return object or false
	 */
	function getDefaultGroup()
	{
		foreach ($this->getUserGroups AS $g)
		{
			if ($g->isdefault) {
				return $g;
			}
		}
		return false;
	}
	
	/**
	 * get categories managed by user
	 * 
	 * @return array
	 */
	function getManagedCategories()
	{
		$db = &JFactory::getDBO();

		$groups = $this->getUserGroups();
		if ($groups) {
			$group_ids = array_keys($groups);
		}
		$quoted = array();
		foreach ($group_ids as $g) {
			$quoted[] = $db->Quote($g);
		}		
		
		$query = ' SELECT DISTINCT gc.category_id  '
		       . ' FROM #__redevent_groups_categories as gc '
		       . ' WHERE gc.group_id IN ('. implode(', ', $quoted) .')'
		       . '   AND gc.accesslevel > 0'
		       ;
		$db->setQuery($query);
		return $db->loadResultArray();
	}
	
	/**
	 * get venues manages by the user
	 * 
	 * @return array
	 */
	function getManagedVenues()
	{
		$db = &JFactory::getDBO();

		$groups = $this->getUserGroups();
		if ($groups) {
			$group_ids = array_keys($groups);
		}
		$quoted = array();
		foreach ($group_ids as $g) {
			$quoted[] = $db->Quote($g);
		}		
		
		$query = ' SELECT DISTINCT gv.venue_id  '
		       . ' FROM #__redevent_groups_venues as gv '
		       . ' WHERE gv.group_id IN ('. implode(', ', $quoted) .')'
		       . '   AND gv.accesslevel > 0'
		       ;
		$db->setQuery($query);
		return $db->loadResultArray();
	}
	
	/**
	 * Checks if the user is a superuser
	 * A superuser will allways have access if the feature is activated
	 *
	 * @since 0.9
	 * 
	 * @return boolean True on success
	 */
	function superuser()
	{
		$user 		= & JFactory::getUser();
		
		$group_ids = array(
					24, //administrator
					25 //super administrator
					);
		return in_array($user->get('gid'), $group_ids);
	}
	
	/**
	 * Checks if the user has the privileges to use the wysiwyg editor
	 *
	 * We could use the validate_user method instead of this to allow to set a groupid
	 * Not sure if this is a good idea
	 *
	 * @since 0.9
	 * 
	 * @return boolean True on success
	 */
	function editoruser()
	{
		$user 		= & JFactory::getUser();
		
		$group_ids = array(
		//			18, //registered
		//			19, //author
					20, //editor
					21, //publisher
					23, //manager
					24, //administrator
					25 //super administrator
					);

		return in_array($user->get('gid'), $group_ids);
	}
}