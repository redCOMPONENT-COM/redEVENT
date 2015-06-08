<?php
/**
 * @package     Redevent.Site
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component Eventhelper Model
 *
 * @package     Redevent.Site
 * @subpackage  Models
 * @since       2.0
 */
class RedeventModelEventhelper extends RModel
{
	/**
	 * event data caching
	 *
	 * @var array
	 */
	protected $event = null;

	/**
	 * event id
	 * @var int
	 */
	protected $id   = null;

	/**
	 * session id xref
	 * @var int
	 */
	protected $xref = null;

	/**
	 * Method to set the session event id
	 *
	 * @param   int  $id  event id
	 *
	 * @return void
	 */
	public function setId($id)
	{
		// Set new details ID and wipe data
		$this->id    = $id;
		$this->event = null;
	}

	/**
	 * Method to set the session id
	 *
	 * @param   int  $xref  session id
	 *
	 * @return void
	 */
	public function setXref($xref)
	{
		// Set new details ID and wipe data
		$this->xref  = $xref;
		$this->event = null;
	}

	/**
	 * Method to get event data
	 *
	 * @return array
	 *
	 * @throws RuntimeException
	 */
	public function getData()
	{
		/*
		 * Load the Category data
		 */
		if ($this->loadDetails())
		{
			$user	= JFactory::getUser();

			// Is the category published?
			if (!count($this->event->categories))
			{
				RedeventError::raiseError(404, JText::_("COM_REDEVENT_CATEGORY_NOT_PUBLISHED"));
			}

			// Do we have access to any category ?
			$access = false;

			foreach ($this->event->categories as $cat)
			{
				if (in_array($cat->access, $user->getAuthorisedViewLevels()))
				{
					$access = true;
					break;
				}
			}

			if (!$access)
			{
				throw new RuntimeException(JText::_("COM_REDEVENT_ALERTNOTAUTH"), 403);
			}
		}

		return $this->event;
	}

	/**
	 * Method to load required data
	 *
	 * @return array
	 *
	 * @since 0.9
	 */
	protected function loadDetails()
	{
		if (empty($this->event))
		{
			$user  = JFactory::getUser();

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('x.*, x.id AS xref, x.title as session_title');
			$query->select('a.*, a.id AS did');
			$query->select('v.id AS venue_id, v.venue, v.city AS location, v.country, v.locimage, v.street, v.plz, v.state');
			$query->select('v.locdescription as venue_description, v.map, v.url as venueurl');
			$query->select('v.city, v.latitude, v.longitude, v.company AS venue_company, v.venue_code');
			$query->select('u.name AS creator_name, u.email AS creator_email');
			$query->select('f.formname, f.currency');
			$query->select('IF (x.course_credit = 0, "", x.course_credit) AS course_credit');
			$query->select('c.name AS catname, c.access');
			$query->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title');
			$query->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug');
			$query->select('CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug');
			$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug');
			$query->select('CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(":", v.id, v.alias) ELSE v.id END as venueslug');

			$query->from('#__redevent_events AS a');
			$query->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = a.id');
			$query->join('LEFT', '#__redevent_venues AS v ON x.venueid = v.id');
			$query->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
			$query->join('LEFT', '#__redevent_categories AS c ON c.id = xcat.category_id');
			$query->join('LEFT', '#__rwf_forms AS f ON f.id = a.redform_id');
			$query->join('LEFT', '#__users AS u ON a.created_by = u.id');

			$this->buildDetailsWhere($query);

			$db->setQuery($query);
			$this->event = $db->loadObject();

			if ($this->event)
			{
				$this->event = $this->_getEventCategories($this->event);
				$helper = new RedeventHelperAttachment;
				$this->event->attachments = $helper->getAttachments('event' . $this->event->did, $user->getAuthorisedViewLevels());
			}

			return (boolean) $this->event;
		}

		return true;
	}

	/**
	 * Method to build the WHERE clause of the query to select the details
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return  JDatabaseQuery
	 */
	protected function buildDetailsWhere(JDatabaseQuery $query)
	{
		if ($this->xref)
		{
			$query->where('x.id = ' . $this->xref);
		}
		elseif ($this->id)
		{
			$query->where('x.eventid = ' . $this->id);
		}

		return $query;
	}

	/**
	 * Adds categories property to event row
	 *
	 * @param   object  $row  event data
	 *
	 * @return object
	 */
	protected function _getEventCategories($row)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('c.id, c.name AS name, c.access, c.image');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug');
		$query->from('#__redevent_categories as c');
		$query->join('INNER', '#__redevent_event_category_xref as x ON x.category_id = c.id');
		$query->where('c.published = 1');
		$query->where('x.event_id = ' . $db->quote($row->did));
		$query->order('c.ordering');

		$db->setQuery($query);
		$row->categories = $db->loadObjectList();

		return $row;
	}

	/**
	 * return places left for session
	 *
	 * @return int
	 */
	public function getPlacesLeft()
	{
		$session = $this->getData();

		if ($session->maxattendees == 0)
		{
			return '-';
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(r.id) AS total');
		$query->from('#__redevent_register AS r');
		$query->where('r.xref = ' . $db->quote($this->xref));
		$query->where('r.confirmed = 1');
		$query->where('r.cancelled = 0');
		$query->where('r.waitinglist = 0');
		$query->group('r.waitinglist');

		$db->setQuery($query);
		$res = $db->loadResult();

		$left = $session->maxattendees - $res;

		return ($left > 0 ? $left : 0);
	}

	/**
	 * return places left on waiting list for session
	 *
	 * @return int
	 */
	public function getWaitingPlacesLeft()
	{
		$session = $this->getData();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(r.id) AS total');
		$query->from('#__redevent_register AS r');
		$query->where('r.xref = ' . $db->quote($this->xref));
		$query->where('r.confirmed = 1');
		$query->where('r.cancelled = 0');
		$query->where('r.waitinglist = 1');
		$query->group('r.waitinglist');

		$db->setQuery($query);
		$res = $db->loadResult();

		$left = $session->maxwaitinglist - $res;

		return ($left > 0 ? $left : 0);
	}

	/**
	 * get current session prices
	 *
	 * @return array
	 */
	public function getPrices()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('sp.*, p.name, p.alias, p.image, p.tooltip, f.currency AS form_currency');
		$query->select('CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\', sp.id, p.alias) ELSE sp.id END as slug');
		$query->select('CASE WHEN CHAR_LENGTH(sp.currency) THEN sp.currency ELSE f.currency END as currency');
		$query->from('#__redevent_sessions_pricegroups AS sp');
		$query->join('INNER', '#__redevent_pricegroups AS p on p.id = sp.pricegroup_id');
		$query->join('INNER', '#__redevent_event_venue_xref AS x on x.id = sp.xref');
		$query->join('INNER', '#__redevent_events AS e on e.id = x.eventid');
		$query->join('LEFT', '#__rwf_forms AS f on e.redform_id = f.id');
		$query->where('sp.xref = ' . $db->quote($this->xref));
		$query->order('p.ordering ASC');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}
}
