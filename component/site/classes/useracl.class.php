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
	
	function __construct($userid = 0)
	{
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
	
	function checkAddEvent()
	{
		$groups = $this->getUserGroups();
		foreach ((array) $groups as $group)
		{
			if ($group->add_events == 1 || $group->params->get('add_event', 0)) {
				return true;
			}
		}
		
		return false;
	}
		
	function getUserGroups()
	{
		if (empty($this->_groups))
		{
			$db = &JFactory::getDBO();
			
			$query = ' SELECT g.id AS group_id, g.name AS group_name, g.parameters, g.isdefault, '
			       . '   gm.member AS user_id, gm.add_events, gm.add_xrefs, gm.edit_venues '
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
}