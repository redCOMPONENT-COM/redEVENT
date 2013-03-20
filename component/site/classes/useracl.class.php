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
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       2.0
*/
class UserAcl
{

	protected $_groups = null;

	protected $_userid = 0;

	protected $user = null;

	protected $_db = null;

	/**
	 * constructor
	 *
	 * @param   int  $userid  user id
	 */
	protected function __construct($userid = 0)
	{
		$this->_db = JFactory::getDBO();

		if (!$userid)
		{
			$user = Jfactory::getUser();
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
	public function &getInstance($id = 0)
	{
		static $instances;

		if (!isset ($instances))
		{
			$instances = array ();
		}

		// Find the user id
		if(!$id)
		{
			$user = Jfactory::getUser();
			$id = $user->get('id');
		}

		if (empty($instances[$id]))
		{
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
	public function canAddEvent()
	{
		if (!$this->_userid)
		{
			return false;
		}

		if ($this->superuser())
		{
			return true;
		}

		$user = $this->getUser();

		$canAdd = $user->authorise('re.createevent', 'com_redevent');
		$cats = $this->getAuthorisedCategories('re.manageevents');

		return ($canAdd && count($cats));
	}

	/**
	 * returns true if the user can add venues
	 *
	 * @return boolean
	 */
	public function canAddVenue()
	{
		if (!$this->_userid)
		{
			return false;
		}

		if ($this->superuser())
		{
			return true;
		}

		return $this->getUser()->authorise('re.createvenue', 'com_redevent');
	}

	/**
	 * return true if the user can edit specified event
	 *
	 * @param   int  $eventid  event id
	 *
	 * @return boolean
	 */
	public function canEditEvent($eventid)
	{
		if (!$this->_userid)
		{
			return false;
		}

		if ($this->superuser())
		{
			return true;
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$canEdit = $this->getUser()->authorise('re.editevent', 'com_redevent');
		$canAdd  = $this->getUser()->authorise('re.createevent', 'com_redevent');

		if ((!$canEdit && !$canAdd) || !count($cats))
		{
			return false;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('e.id');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
		$query->where('e.id = ' . $eventid);
		$query->where('xcat.category_id IN (' . implode(', ', $cats) . ')');
		if (!$canEdit)
		{
			$query->where('e.created_by = ' . $db->Quote($this->_userid));
		}
		$db->setQuery($query);

		return ($db->loadResult() ? true : false);
	}

	/**
	 * returns true if user can publish specified event
	 *
	 * @param   int $eventid  event id, or 0 for a new event
	 *
	 * @return boolean
	 */
	public function canPublishEvent($eventid = 0)
	{
		if (!$this->_userid)
		{
			return false;
		}

		if ($this->superuser())
		{
			return true;
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$canPublishOwn = $this->getUser()->authorise('re.publishown', 'com_redevent');
		$canPublishAny = $this->getUser()->authorise('re.publishany', 'com_redevent');

		if ((!$canPublishOwn && !$canPublishAny) || !count($cats))
		{
			return false;
		}

		if ($eventid == 0)
		{
			// New event, so it's own and should be in allowed cats...
			return true;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('e.id');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
		$query->where('e.id = ' . $eventid);
		$query->where('xcat.category_id IN (' . implode(', ', $cats) . ')');
		if (!$canPublishAny)
		{
			$query->where('e.created_by = ' . $db->Quote($this->_userid));
		}
		$db->setQuery($query);

		return ($db->loadResult() ? true : false);
	}

	/**
	 * returns true if user can publish specified event
	 *
	 * @param   int  $xref  session id, or 0 for a new session
	 *
	 * @return boolean
	 */
	public function canPublishXref($xref = 0)
	{
		if (!$this->_userid)
		{
			return false;
		}

		if ($this->superuser())
		{
			return true;
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$canPublishOwn = $this->getUser()->authorise('re.publishown', 'com_redevent');
		$canPublishAny = $this->getUser()->authorise('re.publishany', 'com_redevent');

		if ((!$canPublishOwn && !$canPublishAny) || !count($cats))
		{
			return false;
		}

		if ($xref == 0)
		{
			// New session, so it's own and should be for allowed event...
			return true;
		}

		// Otherwise find corresponding event, and check for this event
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.eventid');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->where('x.id = ' . $xref);

		$db->setQuery($query);
		$res = $db->loadResult();

		return $this->canPublishEvent($res);
	}

	/**
	 * return true if the user can edit specified xref
	 *
	 * @param   int  $xref  xref
	 *
	 * @return boolean
	 */
	public function canEditXref($xref)
	{
		if (!$this->_userid)
		{
			return false;
		}

		if ($this->superuser())
		{
			return true;
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$venues  = $this->getAuthorisedVenues('re.manageevents');
		$venuescats  = $this->getAuthorisedVenuesCategories('re.manageevents');
		$canEdit = $this->getUser()->authorise('re.editsession', 'com_redevent');
		$canAdd  = $this->getUser()->authorise('re.createsession', 'com_redevent');

		if ((!$canEdit && !$canAdd) || !count($cats) || (!count($venuescats) && !count($venues)))
		{
			return false;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.id');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.event_id = e.id');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
		$query->join('LEFT', '#__redevent_venue_category_xref AS xvcat ON xvcat.venue_id = x.venueid');
		$query->where('x.id = ' . $xref);
		$query->where('xcat.category_id IN (' . implode(', ', $cats) . ')');

		if (count($venuescats) && count($venues))
		{
			$query->where('(xvcat.category_id IN (' . implode(', ', $venuescats) . ') OR x.venueid IN (' . implode(', ', $venues) . '))');
		}
		elseif (count($venuescats))
		{
			$query->where('xvcat.category_id IN (' . implode(', ', $venuescats) . ')');
		}
		else
		{
			$query->where('x.venueid IN (' . implode(', ', $venues) . ')');
		}

		if (!$canEdit)
		{
			$query->where('e.created_by = ' . $db->Quote($this->_userid));
		}
		$db->setQuery($query);

		return ($db->loadResult() ? true : false);
	}

	/**
	 * get array of all the xrefs the user can edit
	 *
	 * @return array int xrefs
	 */
	public function getCanEditXrefs()
	{
		if (!$this->_userid)
		{
			return false;
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$venues  = $this->getAuthorisedVenues('re.manageevents');
		$venuescats  = $this->getAuthorisedVenuesCategories('re.manageevents');
		$canEdit = $this->getUser()->authorise('re.editsession', 'com_redevent');
		$canAdd  = $this->getUser()->authorise('re.createsession', 'com_redevent');

		if ((!$canEdit && !$canAdd) || !count($cats) || (!count($venuescats) && !count($venues)))
		{
			return false;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.id');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.event_id = e.id');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
		$query->join('LEFT', '#__redevent_venue_category_xref AS xvcat ON xvcat.venue_id = x.venueid');

		if (!$this->superuser())
		{
			$query->where('xcat.category_id IN (' . implode(', ', $cats) . ')');

			if (count($venuescats) && count($venues))
			{
				$query->where('(xvcat.category_id IN (' . implode(', ', $venuescats) . ') OR x.venueid IN (' . implode(', ', $venues) . '))');
			}
			elseif (count($venuescats))
			{
				$query->where('xvcat.category_id IN (' . implode(', ', $venuescats) . ')');
			}
			else
			{
				$query->where('x.venueid IN (' . implode(', ', $venues) . ')');
			}

			if (!$canEdit)
			{
				$query->where('e.created_by = ' . $db->Quote($this->_userid));
			}
		}

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}


	/**
	 * get array of all the xrefs the user can view attendees from
	 *
	 * @return array int xrefs
	 */
	public function getCanViewAttendees()
	{
		if (!$this->_userid)
		{
			return false;
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$venues  = $this->getAuthorisedVenues('re.manageevents');
		$venuescats  = $this->getAuthorisedVenuesCategories('re.manageevents');
		$canViewAttendees = $this->getUser()->authorise('re.viewattendees', 'com_redevent') || $this->getUser()->authorise('re.manageattendees', 'com_redevent');

		if (!$canManageAttendees || !count($cats) || (!count($venuescats) && !count($venues)))
		{
			return false;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.id');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.event_id = e.id');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
		$query->join('LEFT', '#__redevent_venue_category_xref AS xvcat ON xvcat.venue_id = x.venueid');

		if (!$this->superuser())
		{
			$query->where('xcat.category_id IN (' . implode(', ', $cats) . ')');

			if (count($venuescats) && count($venues))
			{
				$query->where('(xvcat.category_id IN (' . implode(', ', $venuescats) . ') OR x.venueid IN (' . implode(', ', $venues) . '))');
			}
			elseif (count($venuescats))
			{
				$query->where('xvcat.category_id IN (' . implode(', ', $venuescats) . ')');
			}
			else
			{
				$query->where('x.venueid IN (' . implode(', ', $venues) . ')');
			}
		}

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}

	/**
	 * check if user is allowed to addxrefs
	 * @return boolean
	 */
	public function canAddXref()
	{
		if (!$this->_userid)
		{
			return false;
		}

		if ($this->superuser())
		{
			return true;
		}

		$user = $this->getUser();

		$canAdd = $user->authorise('re.createsession', 'com_redevent');
		$cats = $this->getAuthorisedCategories('re.manageevents', 'com_redevent');

		return ($canAdd && count($cats));
	}

	/**
	 * return true if current user can manage attendees
	 *
	 * @param   int  $xref_id  xref_id
	 *
	 * @return boolean
	 */
	public function canManageAttendees($xref_id)
	{
		if (!$this->_userid)
		{
			return false;
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$venues  = $this->getAuthorisedVenues('re.manageevents');
		$venuescats  = $this->getAuthorisedVenuesCategories('re.manageevents');
		$canManageAttendees = $this->getUser()->authorise('re.manageattendees', 'com_redevent');

		if (!$canManageAttendees || !count($cats) || (!count($venuescats) && !count($venues)))
		{
			return false;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.id');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.event_id = e.id');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
		$query->join('LEFT', '#__redevent_venue_category_xref AS xvcat ON xvcat.venue_id = x.venueid');
		$query->where('x.id = ' . $xref_id);

		if (!$this->superuser())
		{
			$query->where('xcat.category_id IN (' . implode(', ', $cats) . ')');

			if (count($venuescats) && count($venues))
			{
				$query->where('(xvcat.category_id IN (' . implode(', ', $venuescats) . ') OR x.venueid IN (' . implode(', ', $venues) . '))');
			}
			elseif (count($venuescats))
			{
				$query->where('xvcat.category_id IN (' . implode(', ', $venuescats) . ')');
			}
			else
			{
				$query->where('x.venueid IN (' . implode(', ', $venues) . ')');
			}
		}

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res ? true : false;
	}

	/**
	 * return true if current user can view attendees
	 *
	 * @param   int  $xref_id  xref_id
	 */
	public function canViewAttendees($xref_id)
	{
		if (!$this->_userid)
		{
			return false;
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$venues  = $this->getAuthorisedVenues('re.manageevents');
		$venuescats  = $this->getAuthorisedVenuesCategories('re.manageevents');
		$canViewAttendees = $this->getUser()->authorise('re.viewattendees', 'com_redevent') || $this->getUser()->authorise('re.manageattendees', 'com_redevent');

		if (!$canViewAttendees || !count($cats) || (!count($venuescats) && !count($venues)))
		{
			return false;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.id');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.event_id = e.id');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
		$query->join('LEFT', '#__redevent_venue_category_xref AS xvcat ON xvcat.venue_id = x.venueid');
		$query->where('x.id = ' . $xref_id);

		if (!$this->superuser())
		{
			$query->where('xcat.category_id IN (' . implode(', ', $cats) . ')');

			if (count($venuescats) && count($venues))
			{
				$query->where('(xvcat.category_id IN (' . implode(', ', $venuescats) . ') OR x.venueid IN (' . implode(', ', $venues) . '))');
			}
			elseif (count($venuescats))
			{
				$query->where('xvcat.category_id IN (' . implode(', ', $venuescats) . ')');
			}
			else
			{
				$query->where('x.venueid IN (' . implode(', ', $venues) . ')');
			}
		}

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res ? true : false;
	}

	/**
	 * return true if the user can edit specified venue
	 *
	 * @param   int  $id  venue id
	 *
	 * @return boolean
	 */
	public function canEditVenue($id)
	{
		if (!$this->_userid)
		{
			return false;
		}

		if ($this->superuser())
		{
			return true;
		}

		$cats    = $this->getAuthorisedVenuesCategories('re.managevenues');
		$canAdd = $this->getUser()->authorise('re.createvenue', 'com_redevent');
		$canEdit = $this->getUser()->authorise('re.editvenue', 'com_redevent');

		if ((!$canEdit && !$canAdd) || !count($cats))
		{
			return false;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('v.id');
		$query->from('#__redevent_venues AS v');
		$query->join('INNER', '#__redevent_venue_category_xref AS xcat ON xcat.venue_id = v.id');
		$query->where('v.id = ' . $id);
		$query->where('xcat.category_id IN (' . implode(', ', $cats) . ')');
		if (!$canEdit)
		{
			$query->where('v.created_by = ' . $db->Quote($this->_userid));
		}
		$db->setQuery($query);

		return ($db->loadResult() ? true : false);
	}


	/**
	 * returns true if user can publish specified venue
	 *
	 * @param   int  $id  venue id, or 0 for a new venue
	 *
	 * @return boolean
	 */
	public function canPublishVenue($id = 0)
	{
		if (!$this->_userid)
		{
			return false;
		}

		if ($this->superuser())
		{
			return true;
		}

		$cats    = $this->getAuthorisedVenuesCategories('re.managevenues');
		$canPublishOwn = $this->getUser()->authorise('re.publishvenueown', 'com_redevent');
		$canPublishAny = $this->getUser()->authorise('re.publishvenueany', 'com_redevent');

		if ((!$canPublishOwn && !$canPublishAny) || !count($cats))
		{
			return false;
		}

		if ($eventid == 0)
		{
			// New venue, so it's own and should be in allowed cats...
			return true;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('v.id');
		$query->from('#__redevent_venues AS v');
		$query->join('INNER', '#__redevent_venue_category_xref AS xcat ON xcat.venue_id = v.id');
		$query->where('v.id = ' . $id);
		$query->where('xcat.category_id IN (' . implode(', ', $cats) . ')');
		if (!$canPublishAny)
		{
			$query->where('v.created_by = ' . $db->Quote($this->_userid));
		}
		$db->setQuery($query);

		return ($db->loadResult() ? true : false);
	}

	/**
	 * get user groups
	 *
	 * @return array
	 */
	public function getUserGroups()
	{
		if (empty($this->_groups))
		{
			$db = &JFactory::getDBO();

			$query = ' SELECT g.id AS group_id, g.name AS group_name, g.parameters, g.isdefault, g.edit_events AS gedit_events, g.edit_venues AS gedit_venues, '
			. '   gm.member AS user_id, gm.manage_events, gm.manage_xrefs, gm.edit_venues '
			. ' FROM #__redevent_groups AS g '
			. ' LEFT JOIN #__redevent_groupmembers AS gm ON gm.group_id = g.id '
			. ' WHERE isdefault = 1 '
			. '    OR gm.member = '. $db->Quote($this->_userid)
			. ' GROUP BY g.id ';
			$db->setQuery($query);
			$this->_groups = $db->loadObjectList('group_id');
		}
		return $this->_groups;
	}

	/**
	 * return user group ids
	 *
	 * @return array
	 */
	public function getUserGroupsIds()
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
	public function getDefaultGroup()
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
	public function getManagedCategories()
	{
		return $this->getAuthorisedCategories('re.manageevents');
	}

	/**
	 * get venues managed by the user
	 *
	 * @return array
	 */
	public function getManagedVenues()
	{
		// Explicitely managed
		$venues = $this->getAuthorisedVenues('re.managevenue');

		// Then managed through venue category
		$cats = $this->getAuthorisedVenuesCategories('re.managevenues');

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('v.id');
		$query->from('#__redevent_venues AS v');
		$query->join('LEFT', '#__redevent_venue_category_xref as xcat ON xcat.venue_id = v.id');
		$query->where('v.created_by = ' . $this->_userid, 'OR');
		if ($cats && count($cats))
		{
			$query->where('xcat.category_id IN (' . implode($glue, $cats) . ')');
		}

		$db->setQuery($query);
		$res = $db->loadColumn();

		$venues = array_merge($venues, $res);
		$venues = array_unique($venues);

		return $venues;
	}

	/**
	 * get venues where user is allowed to manage events
	 *
	 * @return array
	 */
	public function getAllowedForEventsVenues()
	{
		// Explicitely managed
		$venues = $this->getAuthorisedVenues('re.manageevents');

		// Then managed through venue category
		$cats = $this->getAuthorisedVenuesCategories('re.manageevents');

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('v.id');
		$query->from('#__redevent_venues AS v');
		$query->join('LEFT', '#__redevent_venue_category_xref as xcat ON xcat.venue_id = v.id');
		$query->where('v.created_by = ' . $this->_userid, 'OR');
		if ($cats && count($cats))
		{
			$query->where('xcat.category_id IN (' . implode($glue, $cats) . ')');
		}

		$db->setQuery($query);
		$res = $db->loadColumn();

		$venues = array_merge($venues, $res);
		$venues = array_unique($venues);

		return $venues;
	}

	/**
	 * get venues categories managed by user
	 *
	 * @return array
	 */
	function getManagedVenuesCategories()
	{
		return $this->getAuthorisedVenuesCategories('re.managevenues', 'com_redevent');
	}

	/**
	 * Checks if the user is a superuser
	 * A superuser will allways have access if the feature is activated
	 *
	 * @since 0.9
	 *
	 * @return boolean True on success
	 */
	public static function superuser($user = null)
	{
		if ($user == null)
		{
			$user = JFactory::getUser();
		}

		if ($user->authorise('core.admin', 'com_redevent'))
		{
			return true;
		}

		return false;
	}

	/**
	 * return true if the user is allowed to manage events in this category
	 *
	 * @param   int  $id  category id
	 *
	 * @return boolean
	 */
	public function manageCategory($id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
		->select('c.id AS id, a.name AS asset_name')
		->from('#__redevent_categories AS c')
		->innerJoin('#__assets AS a ON c.asset_id = a.id')
		->where('c.id = ' . $id);
		$db->setQuery($query);
		$category = $db->loadObject();

		return $this->getUser()->authorise('re.manageevents', $category->asset_name);
	}

	/**
	 * return the list of authorised categories for specified action
	 *
	 * @param   string  $action  action name
	 *
	 * @return array
	 */
	public function getAuthorisedCategories($action)
	{
		// Brute force method: get all published category rows for the component and check each one
		// TODO: Modify the way permissions are stored in the db to allow for faster implementation and better scaling
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
		->select('c.id AS id, a.name AS asset_name')
		->from('#__redevent_categories AS c')
		->innerJoin('#__assets AS a ON c.asset_id = a.id');
		$db->setQuery($query);
		$allCategories = $db->loadObjectList('id');
		$allowedCategories = array();

		foreach ($allCategories as $category)
		{
			if ($this->getUser()->authorise($action, $category->asset_name))
			{
				$allowedCategories[] = (int) $category->id;
			}
		}

		return $allowedCategories;
	}

	/**
	 * return the list of authorised venues categories for specified action
	 *
	 * @param   string  $action  action name
	 *
	 * @return array
	 */
	public function getAuthorisedVenuesCategories($action)
	{
		// Brute force method: get all published category rows for the component and check each one
		// TODO: Modify the way permissions are stored in the db to allow for faster implementation and better scaling
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
		->select('c.id AS id, a.name AS asset_name')
		->from('#__redevent_venues_categories AS c')
		->innerJoin('#__assets AS a ON c.asset_id = a.id');
		$db->setQuery($query);
		$allCategories = $db->loadObjectList('id');
		$allowedCategories = array();
		foreach ($allCategories as $category)
		{
			if ($this->getUser()->authorise($action, $category->asset_name))
			{
				$allowedCategories[] = (int) $category->id;
			}
		}
		return $allowedCategories;
	}

	/**
	 * return the list of authorised venues for specified action
	 *
	 * @param   string  $action  action name
	 *
	 * @return array
	 */
	public function getAuthorisedVenues($action)
	{
		// Brute force method: get all published venues rows for the component and check each one
		// TODO: Modify the way permissions are stored in the db to allow for faster implementation and better scaling
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
		->select('c.id AS id, a.name AS asset_name, c.created_by')
		->from('#__redevent_venues AS c')
		->innerJoin('#__assets AS a ON c.asset_id = a.id');
		$db->setQuery($query);
		$all = $db->loadObjectList('id');
		$allowed = array();
		foreach ($all as $item)
		{
			if ($item->created_by == $this->_userid || $this->getUser()->authorise($action, $item->asset_name))
			{
				$allowed[] = (int) $item->id;
			}
		}
		return $allowed;
	}

	/**
	 * return JUser object
	 *
	 * @return JUser
	 */
	protected function getUser()
	{
		if (empty($this->user))
		{
			$this->user = JFactory::getUser($this->_userid);
		}

		return $this->user;
	}
}