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
		if (!$this->_userid) {
			return false;
		}
  	if ($this->superuser()) {
  		return true;
  	}
  	
		$groups = $this->getUserGroups();
		foreach ((array) $groups as $group)
		{
			if ($group->manage_events > 0 || $group->gedit_events > 0) {
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
		if (!$this->_userid) {
			return false;
		}
  	if ($this->superuser()) {
  		return true;
  	}
  	
		$groups = $this->getUserGroups();
		foreach ((array) $groups as $group)
		{
			if ($group->edit_venues > 0 || $group->gedit_venues > 0) {
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
		if (!$this->_userid) {
			return false;
		}
  	if ($this->superuser()) {
  		return true;
  	}
  	
		$db = &JFactory::getDBO();
		
		$query = ' SELECT e.id '
		       . ' FROM #__redevent_events AS e '
		       . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
		       . ' INNER JOIN #__redevent_groups_categories AS gc ON gc.category_id = xcat.category_id '
		       . ' LEFT JOIN #__redevent_groups AS g ON g.id = gc.group_id '
		       . ' LEFT JOIN #__redevent_groupmembers AS gm ON gm.group_id = gc.group_id '
		       . ' WHERE e.id = '. $db->Quote($eventid)
		       . '   AND gc.accesslevel > 0 '
		       . '   AND ( ( g.isdefault = 1 ' // default group
		       . '         AND (g.edit_events = 2 '
		       . '             OR (g.edit_events = 1 AND e.created_by = '.$db->Quote($this->_userid).') ) ) '
		       . '      OR ( gm.member = '.$db->Quote($this->_userid) // user is member of this group
		       . '         AND ( gm.manage_events = 2 '
		       . '             OR (gm.manage_events = 1 AND e.created_by = '.$db->Quote($this->_userid).') ) ) )'
		       ;
		$db->setQuery($query);
//		echo($db->getQuery());
		return ($db->loadResult() ? true : false);
	}
	
	/**
	 * returns true if user can publish specified event
	 * @param int event id, or 0 for a new event
	 * @return boolean
	 */
	function canPublishEvent($eventid = 0)
	{
		if (!$this->_userid) {
			return false;
		}
  	if ($this->superuser()) {
  		return true;
  	}
  	
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
	 * returns true if user can publish specified event
	 * @param int event id, or 0 for a new event
	 * @return boolean
	 */
	function canPublishXref($xref = 0)
	{
		if (!$this->_userid) {
			return false;
		}
  	if ($this->superuser()) {
  		return true;
  	}
  	
		if (!$xref) // this is a new event
		{		
			return false;
		}
		else
		{
			$query = ' SELECT x.id '
			       . ' FROM #__redevent_event_venue_xref AS x '
			       . ' INNER JOIN #__redevent_events AS e ON e.id = x.eventid '
			       . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = x.eventid '
			       . ' INNER JOIN #__redevent_groups_categories AS gc ON gc.category_id = xcat.category_id '
			       . ' LEFT JOIN #__redevent_groups AS g ON g.id = gc.group_id '
			       . ' LEFT JOIN #__redevent_groupmembers AS gm ON gm.group_id = gc.group_id '
			       . ' WHERE x.id = '. $this->_db->Quote($xref)
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
		if (!$this->_userid) {
			return false;
		}
  	if ($this->superuser()) {
  		return true;
  	}
  	
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
		       . '   AND gc.accesslevel > 0 AND gv.accesslevel > 0 '
		       . '   AND ( ( g.isdefault = 1 AND ( g.edit_events = 2 OR (g.edit_events = 1 AND e.created_by = '.$db->Quote($this->_userid).')) ) '
		       . '      OR ( gm.member = '.$db->Quote($this->_userid)
		       . '        AND (gm.manage_xrefs > 0 OR gm.manage_events > 1 '
		       . '           OR (gm.manage_events = 1 AND e.created_by = '.$db->Quote($this->_userid).') ) ) )'
		       ;
		$db->setQuery($query);
		return ($db->loadResult() ? true : false);
	}
		
  /**
   * check if user is allowed to addxrefs
   * @return boolean
   */
	function canAddXref()
  {
		if (!$this->_userid) {
			return false;
		}
  	if ($this->superuser()) {
  		return true;
  	}
  	
  	$query = ' SELECT gm.id '
  	       . ' FROM #__redevent_groups AS g '
  	       . ' INNER JOIN #__redevent_groupmembers AS gm ON gm.group_id = g.id '
  	       . ' WHERE gm.member = '. $this->_db->Quote($this->_userid)
  	       . '   AND (gm.manage_xrefs > 0 OR gm.manage_events > 0) '
  	       ;
  	$this->_db->setQuery($query);
  	$res = $this->_db->loadObjectList();
  	return count($res) > 0;
  } 
	
	/**
	 * return true if current user can manage attendees
	 * @param int xref_id
	 */
  function canManageAttendees($xref_id)
  {
		if (!$this->_userid) {
			return false;
		}
  	if ($this->superuser()) {
  		return true;
  	}
  	
		$db = &JFactory::getDBO();
  	
  	$query = ' SELECT gm.id '
  	       . ' FROM #__redevent_event_venue_xref AS x '
  	       . ' INNER JOIN #__redevent_groups AS g ON x.groupid = g.id '
  	       . ' INNER JOIN #__redevent_groupmembers AS gm ON gm.group_id = g.id '
  	       . ' WHERE gm.member = '. $db->Quote($this->_userid)
  	       . '   AND (gm.manage_xrefs > 0 OR gm.manage_events > 0) '
  	       . '   AND x.id = '. $db->Quote($xref_id)
  	       ;
  	$db->setQuery($query);
  	$res = $db->loadObjectList();
  	
  	return count($res);
  }
	
	/**
	 * return true if current user can view attendees
	 * @param int xref_id
	 */
  function canViewAttendees($xref_id)
  {
		if (!$this->_userid) {
			return false;
		}
  	if ($this->superuser()) {
  		return true;
  	}
  	
		$db = &JFactory::getDBO();
  	
  	$query = ' SELECT gm.id '
  	       . ' FROM #__redevent_event_venue_xref AS x '
           . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = x.eventid'
  	       . ' INNER JOIN #__redevent_groups_categories AS gc ON gc.category_id = xcat.category_id '
  	       . ' INNER JOIN #__redevent_groups AS g ON gc.group_id = g.id '
  	       . ' INNER JOIN #__redevent_groupmembers AS gm ON gm.group_id = g.id '
  	       . ' WHERE gm.member = '. $db->Quote($this->_userid)
  	       . '   AND (gm.manage_xrefs > 0 OR gm.manage_events > 0 OR gm.receive_registrations > 0) '
  	       . '   AND x.id = '. $db->Quote($xref_id)
  	       ;
  	$db->setQuery($query);
  	$res = $db->loadObjectList();
  	return count($res);
  }
	
	/**
	 * return true if the user can edit specified event
	 * @param int $eventid
	 * @return boolean
	 */
	function canEditVenue($id)
	{
  	if ($this->superuser()) {
  		return true;
  	}
  	
		$db = &JFactory::getDBO();
		
		$query = ' SELECT v.id '
		       . ' FROM #__redevent_venues AS v '
		       . ' INNER JOIN #__redevent_groups_venues AS gv ON gv.venue_id = v.id '
		       . ' LEFT JOIN #__redevent_groups AS g ON g.id = gv.group_id '
		       . ' LEFT JOIN #__redevent_groupmembers AS gm ON gm.group_id = gv.group_id '
		       . ' WHERE v.id = '. $db->Quote($id)
		       . '   AND ( v.created_by = '.$db->Quote($this->_userid)
           . '       OR ( gv.accesslevel > 0 '
           . '          AND (  ( g.isdefault = 1 AND g.edit_venues = 2 ) '
           . '              OR ( gm.member = '.$this->_db->Quote($this->_userid).' AND (g.edit_venues = 2 OR gm.edit_venues = 2) ) ) ) ) '
		       ;
		$db->setQuery($query);
//		echo($db->getQuery());
		return ($db->loadResult() ? true : false);
	}

	
	/**
	 * returns true if user can publish specified venue
	 * @param int venue id, or 0 for a new venue
	 * @return boolean
	 */
	function canPublishVenue($id = 0)
	{
		if (!$this->_userid) {
			return false;
		}
  	if ($this->superuser()) {
  		return true;
  	}
  	
		if (!$id) // this is a new event
		{		
			$query = ' SELECT g.id '
			       . ' FROM #__redevent_groups AS g '
			       . ' LEFT JOIN #__redevent_groupmembers AS gm ON gm.group_id = g.id '
			       . ' WHERE ( gm.member = '.$this->_db->Quote($this->_userid).' AND gm.publish_venues > 0 ) '
			       . '   OR ( g.isdefault = 1 AND g.publish_venues > 0 ) '
			       ;		
		}
		else
		{
			$query = ' SELECT v.id '
			       . ' FROM #__redevent_venues AS v '
			       . ' INNER JOIN #__redevent_groups_venues AS gv ON gv.venue_id = v.id '
			       . ' LEFT JOIN #__redevent_groups AS g ON g.id = gv.group_id '
			       . ' LEFT JOIN #__redevent_groupmembers AS gm ON gm.group_id = gv.group_id '
			       . ' WHERE v.id = '. $this->_db->Quote($id)
			       . '   AND ( ( g.isdefault = 1 AND (g.publish_venues = 2 OR (g.publish_venues = 1 AND v.created_by = '.$this->_db->Quote($this->_userid).') ) ) '
			       . '      OR ( gm.publish_venues = 2 OR (gm.publish_venues = 1 AND v.created_by = '.$this->_db->Quote($this->_userid).') ) ) '
			       ;	
		}
		$this->_db->setQuery($query);
//		echo($db->getQuery());
		return ($this->_db->loadResult() ? true : false);	
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
			
			$query = ' SELECT g.id AS group_id, g.name AS group_name, g.parameters, g.isdefault, g.edit_events AS gedit_events, g.edit_venues AS gedit_venues, '
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
	 * return user group ids
	 * 
	 * @return array
	 */
	function getUserGroupsIds()
	{
		$res = array();
		$groups = $this->getUserGroups();
		foreach ((array)$groups as $g) {
			$res[] = $g->group_id;
		}
		return $res;
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
		if (!$groups) {
			return false;
		}
		$group_ids = array_keys($groups);
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
	 * get venues managed by the user
	 * 
	 * @return array
	 */
	function getManagedVenues()
	{
		$db = &JFactory::getDBO();

		$groups = $this->getUserGroups();
		if (!$groups) {
			return false;
		}
		
		$group_ids = array_keys($groups);
		$quoted = array();
		foreach ($group_ids as $g) {
			$quoted[] = $db->Quote($g);
		}		
		
		$query = ' SELECT DISTINCT v.id AS venue_id  '
		       . ' FROM #__redevent_venues AS v '
		       . ' LEFT JOIN #__redevent_groups_venues as gv ON gv.venue_id = v.id '
		       . ' LEFT JOIN #__redevent_venue_category_xref as xvcat ON xvcat.venue_id = v.id '
		       . ' LEFT JOIN #__redevent_venues_categories as vcat ON vcat.id = xvcat.category_id '
		       . ' LEFT JOIN #__redevent_groups_venues_categories as gvc ON gvc.category_id = vcat.id '
		       . ' WHERE (gv.group_id IN ('. implode(', ', $quoted) .') AND gv.accesslevel > 0) '
		       . '    OR (gvc.group_id IN ('. implode(', ', $quoted) .') AND gvc.accesslevel > 0) '
		       . '    OR v.created_by = '.$db->Quote($this->_userid);
		       ;
		$db->setQuery($query);
		return $db->loadResultArray();
	}
	
	/**
	 * get venues categories managed by user
	 * 
	 * @return array
	 */
	function getManagedVenuesCategories()
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
		       . ' FROM #__redevent_groups_venues_categories as gc '
		       . ' WHERE gc.group_id IN ('. implode(', ', $quoted) .')'
		       . '   AND gc.accesslevel > 0'
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
  	$auth =& JFactory::getACL();
        
    $auth->addACL('com_redevent', 'manageattendees', 'users', 'super administrator');
    $auth->addACL('com_redevent', 'manageattendees', 'users', 'administrator');
    $auth->addACL('com_redevent', 'manageattendees', 'users', 'manager');  	
    
  	$user = & JFactory::getUser();
  	
  	if ($user->authorize('com_redevent', 'manageattendees')) {
  		return true;
  	}
  	return false;
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