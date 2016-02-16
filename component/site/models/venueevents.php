<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Model Venue events
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventModelVenueevents extends RedeventModelBasesessionlist
{
	/**
	 * venue id
	 *
	 * @var int
	 */
	protected $id = 0;

	/**
	 * venue data array
	 *
	 * @var array
	 */
	protected $venue = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$mainframe = JFactory::getApplication();

		$id = JFactory::getApplication()->input->getInt('id');
		$this->setId((int) $id);

		$params    = $mainframe->getParams('com_redevent');

		if ($params->exists('results_type'))
		{
			$results_type = $params->get('results_type', $params->get('default_categoryevents_results_type', 1));
		}
		else
		{
			$results_type = $params->get('default_categoryevents_results_type', 1);
		}

		// If searching for events
		if ($results_type == 0)
		{
			// Get the filter request variables
			$this->setState('filter_order',     JFactory::getApplication()->input->getCmd('filter_order', 'a.title'));
			$this->setState('filter_order_Dir', strtoupper(JFactory::getApplication()->input->getCmd('filter_order_Dir', 'ASC')) == 'DESC' ? 'DESC' : 'ASC');
		}

		$this->setState('results_type', $results_type);
	}

	/**
	 * Method to set the venue id
	 *
	 * @param   int  $id  venue ID number
	 *
	 * @return void
	 *
	 * @access	public
	 */
	public function setId($id)
	{
		// Set new venue ID and wipe data
		$this->id			= $id;
		$this->data		= null;
	}

	/**
	 * Method to get the Events
	 *
	 * @return array
	 */
	public function getData()
	{
		if ($this->getState('results_type', 1) == 1)
		{
			return parent::getData();
		}

		// Lets load the content if it doesn't already exist
		if (empty($this->data))
		{
			$query = $this->_buildQuery();

			$pagination = $this->getPagination();
			$this->data = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			$this->data = $this->_categories($this->data);
			$this->data = $this->_getSessions($this->data);
		}

		return $this->data;
	}

	/**
	 * Build the where clause
	 *
	 * @param   object  $query  query
	 *
	 * @return object
	 */
	protected function _buildWhere($query)
	{
		$query = parent::_buildWhere($query);

		/* Check if a venue ID is set */
		if ($this->id > 0)
		{
			$query->where('x.venueid = ' . $this->id);
		}

		if ($this->getState('results_type') == 0)
		{
			$query->clear('group');
			$query->group('a.id');
		}

		return $query;
	}

	/**
	 * Method to get the Venue
	 *
	 * @return array
	 */
	public function getVenue()
	{
		$user		= JFactory::getUser();
		$gids = $user->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*, id AS venueid');
		$query->select('CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as slug');
		$query->from('#__redevent_venues');
		$query->where('id = ' . $this->id);
		$query->where('access IN (' . $gids . ')');

		$db->setQuery($query);
		$venue = $db->loadObject();

		if ($venue)
		{
			$helper = new RedeventHelperAttachment;
			$venue->attachments = $helper->getAttachments('venue' . $venue->id, $user->getAuthorisedViewLevels());
		}

		return $venue;
	}

	/**
	 * get Sessions associated to events data
	 *
	 * @param   array  $data  event data objects
	 *
	 * @return array
	 */
	protected function _getSessions($data)
	{
		if (!$data || ! count($data))
		{
			return $data;
		}

		$event_ids = array();

		foreach ($data as $k => $ev)
		{
			$event_ids[] = $ev->id;
			$map[$ev->id] = $k;
		}

		$query = parent::_buildQuery();
		$query->clear('order');
		$query->where('a.id IN (' . implode(",", $event_ids) . ')');

		$this->_db->setQuery($query);
		$sessions = $this->_db->loadObjectList();

		foreach ($sessions as $s)
		{
			if (!isset($data[$map[$s->id]]))
			{
				$data[$map[$s->id]]->sessions = array($s);
			}
			else
			{
				$data[$map[$s->id]]->sessions[] = $s;
			}
		}

		return $data;
	}
}
