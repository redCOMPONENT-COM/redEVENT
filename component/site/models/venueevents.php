<?php
/**
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
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

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Redevent Model Venue events
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       0.9
 */
class RedeventModelVenueevents extends RedeventModelBaseeventlist
{
	/**
	 * venue id
	 *
	 * @var int
	 */
	protected $_id = 0;

	/**
	 * venue data array
	 *
	 * @var array
	 */
	protected $_venue = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$mainframe = &JFactory::getApplication();

		$id = JRequest::getInt('id');
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
			$this->setState('filter_order',     JRequest::getCmd('filter_order', 'a.title'));
			$this->setState('filter_order_Dir', strtoupper(JRequest::getCmd('filter_order_Dir', 'ASC')) == 'DESC' ? 'DESC' : 'ASC');
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
		$this->_id			= $id;
		$this->_data		= null;
	}

	/**
	 * override to take into account search type
	 * @see RedeventModelBaseeventlist::getData()
	 */
	public function &getData()
	{
		if ($this->getState('results_type', 1) == 1)
		{
			return parent::getData();
		}

		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();

			$pagination = $this->getPagination();
			$this->_data = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			$this->_data = $this->_categories($this->_data);
			$this->_data = $this->_getSessions($this->_data);
		}

		return $this->_data;
	}

	/**
	 * (non-PHPdoc)
	 * @see RedeventModelBaseeventlist::_buildWhere()
	 */
	protected function _buildWhere($query)
	{
		$query = parent::_buildWhere($query);

		/* Check if a venue ID is set */
		if ($this->_id > 0)
		{
			$query->where('x.venueid = ' . $this->_id);
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
	 * @access public
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
		$query->where('id = ' . $this->_id);
		$query->where('access IN (' . $gids . ')');

		$db->setQuery($query);
		$_venue = $db->loadObject();

		$helper = new RedeventHelperAttachment;
		$_venue->attachments = $helper->getAttachments('venue' . $_venue->id, $user->getAuthorisedViewLevels());

		return $_venue;
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
