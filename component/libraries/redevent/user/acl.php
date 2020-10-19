<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT specific UserAcl
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventUserAcl
{
	protected $userid = 0;

	protected $user = null;

	protected $db = null;

	private $managedCategories = null;

	/**
	 * constructor
	 *
	 * @param   int  $userid  user id
	 */
	protected function __construct($userid = 0)
	{
		$this->db = JFactory::getDBO();

		if (!$userid)
		{
			$user = JFactory::getUser();
			$userid = $user->get('id');
		}

		$this->userid = $userid;
	}

	/**
	 * Returns a reference to the global User object, only creating it if it
	 * doesn't already exist.
	 *
	 * This method must be invoked as:
	 * <pre>  $useracl = RedeventUserAcl::getInstance($id);</pre>
	 *
	 * @param   int  $id  The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 *
	 * @return RedeventUserAcl The User object.
	 */
	public static function getInstance($id = 0)
	{
		static $instances;

		if (!isset($instances))
		{
			$instances = array ();
		}

		// Find the user id
		if (!$id)
		{
			$user = Jfactory::getUser();
			$id = $user->get('id');
		}

		if (empty($instances[$id]))
		{
			$inst = new RedeventUserAcl($id);
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
		if (!$this->userid)
		{
			return false;
		}

		if ($this->superuser() || $this->getUser()->authorise('core.create', 'com_redevent'))
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
		if (!$this->userid)
		{
			return false;
		}

		if ($this->superuser() || $this->getUser()->authorise('core.create', 'com_redevent'))
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
		if (!$this->userid)
		{
			return false;
		}

		if ($this->superuser() || $this->getUser()->authorise('core.edit', 'com_redevent'))
		{
			return true;
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$canEdit = $this->getUser()->authorise('re.editevent', 'com_redevent');
		$canEditOwn = $this->getUser()->authorise('core.edit.own', 'com_redevent');

		if ((!$canEdit && !$canEditOwn) || !count($cats))
		{
			return false;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('e.id');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
		$query->where('e.id = ' . (int) $eventid);
		$query->where('xcat.category_id IN (' . implode(', ', $cats) . ')');

		if (!$canEdit)
		{
			$query->where('e.created_by = ' . $db->Quote($this->userid));
		}

		$db->setQuery($query);

		return ($db->loadResult() ? true : false);
	}

	/**
	 * returns true if user can publish specified event
	 *
	 * @param   int  $eventid  event id, or 0 for a new event
	 *
	 * @return boolean
	 */
	public function canPublishEvent($eventid = 0)
	{
		if (!$this->userid)
		{
			return false;
		}

		if ($this->superuser() || $this->getUser()->authorise('core.edit', 'com_redevent'))
		{
			return true;
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$canPublishOwn = $this->getUser()->authorise('re.publishown', 'com_redevent');
		$canPublishAny = $this->getUser()->authorise('re.publishany', 'com_redevent');

		if (!$canPublishOwn && !$canPublishAny)
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

		if (!empty($cats) && $canPublishAny)
		{
			$query->where(
				'(xcat.category_id IN (' . implode(', ', $cats) . ')'
				. ' OR e.created_by = ' . $db->Quote($this->userid) . ')'
			);
		}
		else
		{
			$query->where('e.created_by = ' . $db->Quote($this->userid));
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
		if (!$this->userid)
		{
			return false;
		}

		if ($this->superuser() || $this->getUser()->authorise('core.edit', 'com_redevent'))
		{
			return true;
		}

		$canPublishOwn = $this->getUser()->authorise('re.publishown', 'com_redevent');
		$canPublishAny = $this->getUser()->authorise('re.publishany', 'com_redevent');

		if (!$canPublishOwn && !$canPublishAny)
		{
			return false;
		}

		if ($xref == 0)
		{
			// New session, so it's own and should be for allowed event...
			return true;
		}

		// Otherwise find corresponding event, and check for this event
		$db    = JFactory::getDbo();
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
		if (!$this->userid)
		{
			return false;
		}

		if ($this->superuser() || $this->getUser()->authorise('core.edit', 'com_redevent'))
		{
			return true;
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$venues  = $this->getAuthorisedVenues('re.manageevents');
		$venuescats  = $this->getAuthorisedVenuesCategories('re.manageevents');
		$canEdit = $this->getUser()->authorise('re.editsession', 'com_redevent');
		$canEditOwn  = $this->getUser()->authorise('core.edit.own', 'com_redevent');

		if ((!$canEdit && !$canEditOwn) || !count($cats) || (!count($venuescats) && !count($venues)))
		{
			return false;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.id');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = e.id');
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
			$query->where('e.created_by = ' . $db->Quote($this->userid));
		}

		$db->setQuery($query);

		return ($db->loadResult() ? true : false);
	}

	/**
	 * get array of all the ids of events the user can edit
	 *
	 * @return array int event ids
	 */
	public function getCanEditEvents()
	{
		if (!$this->userid)
		{
			return array();
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('e.id');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
		$query->group('e.id');

		$allowedAll = $this->superuser() || $this->getUser()->authorise('core.edit', 'com_redevent');

		if (!$allowedAll)
		{
			$cats    = $this->getAuthorisedCategories('re.manageevents');
			$canEdit = $this->getUser()->authorise('re.editevent', 'com_redevent');
			$canEditOwn  = $this->getUser()->authorise('core.edit.own', 'com_redevent');

			if ((!$canEdit) || !count($cats))
			{
				// Only edit own
				if ($canEditOwn)
				{
					$query->where('e.created_by = ' . $this->userid);
				}
				else
				{
					$query->where('0');
				}
			}
			else
			{
				$query->where('(xcat.category_id IN (' . implode(', ', $cats) . ') OR e.created_by = ' . $this->userid . ')');
			}
		}

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}

	/**
	 * get array of all the xrefs the user can edit
	 *
	 * @return array int xrefs
	 */
	public function getCanEditXrefs()
	{
		if (!$this->userid)
		{
			return false;
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$venues  = $this->getAuthorisedVenues('re.manageevents');
		$venuescats  = $this->getAuthorisedVenuesCategories('re.manageevents');
		$canEdit = $this->getUser()->authorise('re.editsession', 'com_redevent');
		$canEditOwn  = $this->getUser()->authorise('core.edit.own', 'com_redevent');

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.id');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = e.id');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
		$query->join('LEFT', '#__redevent_venue_category_xref AS xvcat ON xvcat.venue_id = x.venueid');

		$allowedAll = $this->superuser() || $this->getUser()->authorise('core.edit', 'com_redevent');

		if (!$allowedAll)
		{
			if (!$canEdit && !$canEditOwn)
			{
				return false;
			}

			if ((empty($cats) || (empty($venuescats) && empty($cats))) && !$canEditOwn)
			{
				return false;
			}

			$conditionsAcl = array('e.created_by = ' . $db->Quote($this->userid));
			$conditionsAnd = array();

			if (!empty($cats))
			{
				$conditionsAnd[] = 'xcat.category_id IN (' . implode(', ', $cats) . ')';
			}

			if (count($venuescats) && count($venues))
			{
				$conditionsAnd[]
					= '(xvcat.category_id IN (' . implode(', ', $venuescats) . ') OR x.venueid IN (' . implode(', ', $venues) . '))';
			}
			elseif (count($venuescats))
			{
				$conditionsAnd[] = 'xvcat.category_id IN (' . implode(', ', $venuescats) . ')';
			}
			elseif (count($venues))
			{
				$conditionsAnd[] = 'x.venueid IN (' . implode(', ', $venues) . ')';
			}

			if (!empty($conditionsAnd))
			{
				$conditionsAcl[] = '(' . implode(' AND ', $conditionsAnd) . ')';
			}

			$query->where('(' . implode(' OR ', $conditionsAcl) . ')');
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
	private function getSessionsCanViewAttendees()
	{
		if (!$this->userid)
		{
			return array();
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$venues  = $this->getAuthorisedVenues('re.manageevents');
		$venuescats  = $this->getAuthorisedVenuesCategories('re.manageevents');
		$canViewAttendees = $this->getUser()->authorise('re.viewattendees', 'com_redevent')
			|| $this->getUser()->authorise('re.manageattendees', 'com_redevent');

		if (!$canViewAttendees || !count($cats) || (!count($venuescats) && !count($venues)))
		{
			return array();
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.id AS xref, e.id AS event_id');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = e.id');
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
		$res = $db->loadObjectList();

		return $res ?: array();
	}

	/**
	 * get array of all the events ids the user can view attendees from
	 *
	 * @return array int event ids
	 */
	public function getEventsCanViewAttendees()
	{
		if (!$obj = $this->getSessionsCanViewAttendees())
		{
			return $obj;
		}

		$res = array();

		foreach ($obj as $o)
		{
			$res[] = $o->event_id;
		}

		return array_unique($res);
	}

	/**
	 * get array of all the xrefs the user can view attendees from
	 *
	 * @return array int xrefs
	 */
	public function getXrefsCanViewAttendees()
	{
		if (!$obj = $this->getSessionsCanViewAttendees())
		{
			return $obj;
		}

		$res = array();

		foreach ($obj as $o)
		{
			$res[] = $o->xref;
		}

		return $res;
	}

	/**
	 * check if user is allowed to addxrefs
	 *
	 * @return boolean
	 *
	 * @deprecated  see  canAddSession
	 */
	public function canAddXref()
	{
		return self::canAddSession();
	}

	/**
	 * check if user is allowed to add a session
	 *
	 * @return boolean
	 */
	public function canAddSession()
	{
		if (!$this->userid)
		{
			return false;
		}

		if ($this->superuser() || $this->getUser()->authorise('core.create', 'com_redevent'))
		{
			return true;
		}

		$user = $this->getUser();

		$canAdd = $user->authorise('re.createsession', 'com_redevent');
		$cats = $this->getAuthorisedCategories('re.manageevents');

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
		if (!$this->userid)
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
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = e.id');
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
	 *
	 * @return boolean
	 */
	public function canViewAttendees($xref_id)
	{
		if (!$this->userid)
		{
			return false;
		}

		$cats    = $this->getAuthorisedCategories('re.manageevents');
		$venues  = $this->getAuthorisedVenues('re.manageevents');
		$venuescats  = $this->getAuthorisedVenuesCategories('re.manageevents');
		$canViewAttendees = $this->getUser()->authorise('re.viewattendees', 'com_redevent')
			|| $this->getUser()->authorise('re.manageattendees', 'com_redevent');

		if (!$canViewAttendees || !count($cats) || (!count($venuescats) && !count($venues)))
		{
			return false;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.id');
		$query->from('#__redevent_events AS e');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = e.id');
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
		if (!$this->userid)
		{
			return false;
		}

		if ($this->superuser() || $this->getUser()->authorise('core.edit', 'com_redevent'))
		{
			return true;
		}

		// Get venue data
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('v.id, asset.name AS asset_name, v.created_by, xcat.category_id AS cat_id')
			->from('#__redevent_venues AS v')
			->leftJoin(('#__redevent_venue_category_xref AS xcat ON xcat.venue_id = v.id'))
			->leftJoin('#__assets AS asset ON v.asset_id = asset.id')
			->where('v.id = ' . $id);

		$db->setQuery($query);
		$items = $db->loadObjectList();

		if (!$items)
		{
			throw new OutOfBoundsException('venue not found', 500);
		}

		$canEditOwn = $this->getUser()->authorise('core.edit.own', 'com_redevent');

		if ($canEditOwn && $items[0]->created_by == $this->userid)
		{
			return true;
		}

		if ($this->getUser()->authorise('re.managevenue', $items[0]->asset_name))
		{
			return true;
		}

		$cats = $this->getAuthorisedVenuesCategories('re.managevenues');

		foreach ($items as $item)
		{
			if (in_array($item->cat_id, $cats))
			{
				return true;
			}
		}

		return false;
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
		if (!$this->userid)
		{
			return false;
		}

		if ($this->superuser() || $this->getUser()->authorise('core.edit', 'com_redevent'))
		{
			return true;
		}

		$cats    = $this->getAuthorisedVenuesCategories('re.managevenues');
		$canPublishOwn = $this->getUser()->authorise('re.publishvenueown', 'com_redevent');
		$canPublishAny = $this->getUser()->authorise('re.publishvenueany', 'com_redevent');

		if (!$canPublishOwn && !$canPublishAny)
		{
			return false;
		}

		if ($id == 0)
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

		$filterOr = array('v.created_by = ' . $db->Quote($this->userid));

		if (count($cats) && $canPublishAny)
		{
			$filterOr[] = 'xcat.category_id IN (' . implode(', ', $cats) . ')';
		}

		$query->where('(' . implode(' OR ', $filterOr) . ')');

		$db->setQuery($query);

		return ($db->loadResult() ? true : false);
	}

	/**
	 * get categories managed by user
	 *
	 * @return array
	 */
	public function getManagedCategories()
	{
		if (is_null($this->managedCategories))
		{
			$this->managedCategories = $this->getAuthorisedCategories('re.manageevents');
		}

		return $this->managedCategories;
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
		$query->where('v.created_by = ' . $this->userid, 'OR');

		if ($cats && count($cats))
		{
			$query->where('xcat.category_id IN (' . implode(", ", $cats) . ')');
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
		$query->where('v.created_by = ' . $this->userid, 'OR');

		if ($cats && count($cats))
		{
			$query->where('xcat.category_id IN (' . implode(', ', $cats) . ')');
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
	public function getManagedVenuesCategories()
	{
		return $this->getAuthorisedVenuesCategories('re.managevenues');
	}

	/**
	 * Checks if the user is a superuser
	 * A superuser will allways have access if the feature is activated
	 *
	 * @param   object  $user  user
	 *
	 * @return boolean True on success
	 */
	public function superuser($user = null)
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
			if ($item->created_by == $this->userid || $this->getUser()->authorise($action, $item->asset_name))
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
			$this->user = JFactory::getUser($this->userid);
		}

		return $this->user;
	}

	/**
	 * return array of groups ids allowed to perform the action
	 *
	 * @param   string  $action  the action
	 * @param   string  $asset   the asset
	 *
	 * @return array
	 */
	public function getAllowedGroups($action, $asset = null)
	{
		$allowed = array();

		if (!$asset)
		{
			$asset = 'com_redevent';
		}

		// First get the groups
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__usergroups');

		$db->setQuery($query);
		$groupIds = $db->loadColumn();

		foreach ($groupIds as $groupId)
		{
			if (JAccess::checkGroup($groupId, $action, $asset))
			{
				$allowed[] = $groupId;
			}
		}

		return $allowed;
	}

	/**
	 * return array of users ids allowed to perform the action
	 *
	 * @param   string  $action  the action
	 * @param   string  $asset   the asset
	 *
	 * @return array
	 */
	public function getAllowedUsers($action, $asset = null)
	{
		$allowedgroups = $this->getAllowedGroups($action, $asset);

		if (!$allowedgroups)
		{
			return false;
		}

		$users = array();

		foreach ($allowedgroups as $groupId)
		{
			$users = array_merge($users, JAccess::getUsersByGroup($groupId, true));
		}

		$users = array_unique($users);

		return $users;
	}

	/**
	 * Get ids of user allowed to receive notifications for a session
	 *
	 * @param   int  $xref  session id
	 *
	 * @return array
	 */
	public function getXrefRegistrationRecipients($xref)
	{
		static $cache = array();

		if (empty($cache[$xref]))
		{
			$helper = new RedeventUserAclSessionregistration($xref, $this);

			$cache[$xref] = $helper->getRecipients();
		}

		return $cache[$xref];
	}
}
