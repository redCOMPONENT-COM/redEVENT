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

require_once 'baseeventslist.php';

/**
 * redEVENT Component search Model
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       2.0
 */
class RedeventModelSearch extends RedeventModelBaseEventList
{
	/**
	 * the query
	 */
	protected $_query = null;

	protected $_filter = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$mainframe = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params    = $mainframe->getParams('com_redevent');

		$filter_continent = $mainframe->getUserStateFromRequest('com_redevent.search.filter_continent', 'filter_continent', null, 'string');
		$filter_country   = $mainframe->getUserStateFromRequest('com_redevent.search.filter_country',   'filter_country', null, 'string');
		$filter_state     = $mainframe->getUserStateFromRequest('com_redevent.search.filter_state',     'filter_state', null, 'string');
		$filter_city      = $mainframe->getUserStateFromRequest('com_redevent.search.filter_city',      'filter_city', null, 'string');
		$filter_venue     = $mainframe->getUserStateFromRequest('com_redevent.search.filter_venue',     'filter_venue', null, 'int');

		$filter_date_from     = $mainframe->getUserStateFromRequest('com_redevent.search.filter_date_from',          'filter_date_from',          '', 'string');
		$filter_date_to       = $mainframe->getUserStateFromRequest('com_redevent.search.filter_date_to',          'filter_date_to',          '', 'string');
		$filter_venuecategory = $mainframe->getUserStateFromRequest('com_redevent.search.filter_venuecategory', 'filter_venuecategory', 0, 'int');
		$filter_category      = $mainframe->getUserStateFromRequest('com_redevent.search.filter_category',      'filter_category',      $params->get('category', 0), 'int');
		$filter_event         = $mainframe->getUserStateFromRequest('com_redevent.search.filter_event',         'filter_event',         0, 'int');

		// Saving state
		$this->setState('filter_continent',     $filter_continent);
		$this->setState('filter_country',       $filter_country);
		$this->setState('filter_state',         $filter_state);
		$this->setState('filter_city',          $filter_city);
		$this->setState('filter_venue',         $filter_venue);
		$this->setState('filter_date_from',     $filter_date_from);
		$this->setState('filter_date_to',       $filter_date_to);
		$this->setState('filter_venuecategory', $filter_venuecategory);
		$this->setState('filter_category',      $filter_category);
		$this->setState('filter_event',         $filter_event);

		$results_type = $params->get('results_type', $params->get('default_search_results_type', 1));
		$this->setState('results_type', $results_type);

		// If searching for events
		if ($results_type == 0)
		{
			// Get the filter request variables
			$this->setState('filter_order',     JRequest::getCmd('filter_order', 'a.title'));
			$this->setState('filter_order_Dir', strtoupper(JRequest::getCmd('filter_order_Dir', 'ASC')) == 'DESC' ? 'DESC' : 'ASC');
		}
	}

	/**
	 * override to take into account search type
	 * @see RedeventModelBaseEventList::getData()
	 */
	public function &getData()
	{

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
	 * override to take into account search type
	 * @see RedeventModelBaseEventList::_buildQuery()
	 */
	protected function _buildQuery()
	{
		$query = parent::_buildQuery();

		if ($this->getState('results_type') == 0)
		{
			$query->clear('group');
			$query->group('a.id');
		}

		return $query;
	}

	/**
	 * (non-PHPdoc)
	 * @see RedeventModelBaseEventList::_buildWhere()
	 */
	protected function _buildWhere($query)
	{
		$app = JFactory::getApplication();

		$user		= JFactory::getUser();
		$gid		= max($user->getAuthorisedViewLevels());

		// Get the paramaters of the active menu item
		$params 	= $app->getParams();

		$query->where('x.published = 1');
		$query->where('a.published <> 0');

		if ($this->getState('filter.language'))
		{
			$query->where('(a.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR a.language IS NULL)');
			$query->where('(c.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR c.language IS NULL)');
		}

		$filter = $this->getFilter();
		if ( $params->get('requires_filter', 0) && (!$filter || empty($filter)) )
		{
			$filter = array('0'); // wil force query to return false...
		}

		foreach ($filter as $f)
		{
			$query->where($filter);
		}

		return $query;
	}

	/**
	 * return array of filters for where part of sql query
	 *
	 * @return array
	 */
	public function getFilter()
	{
		if (empty($this->_filter))
		{
			// Get the paramaters of the active menu item
			$mainframe = &Jfactory::getApplication();
			$params    = & $mainframe->getParams();
			$post = JRequest::get('request');

			$filter_continent = $this->getState('filter_continent');
			$filter_country   = $this->getState('filter_country');
			$filter_state     = $this->getState('filter_state');
			$filter_city      = $this->getState('filter_city');
			$filter_venue     = $this->getState('filter_venue');

			$filter_date_from     = $this->getState('filter_date_from');
			$filter_date_to       = $this->getState('filter_date_to');
			$filter_venuecategory = $this->getState('filter_venuecategory');
			$filter_category      = $this->getState('filter_category');
			$filter_event         = $this->getState('filter_event');

			$customs              = $this->getState('filtercustom');

			$filter 		      = $this->getState('filter');
			$filter_type 	    = $this->getState('filter_type');

			$where = array();

			if ($filter)
			{
				// clean filter variables
				$filter 		= JString::strtolower($filter);
				$filter			= $this->_db->Quote( '%'.$this->_db->getEscaped( $filter, true ).'%', false );
				$filter_type 	= JString::strtolower($filter_type);

				switch ($filter_type)
				{
					case 'title' :
						$where[] = ' LOWER( a.title ) LIKE '.$filter;
						break;

					case 'venue' :
						$where[] = ' LOWER( l.venue ) LIKE '.$filter;
						break;

					case 'city' :
						$where[] = ' LOWER( l.city ) LIKE '.$filter;
						break;
				}
			}
			// filter date
			if (strtotime($filter_date_from)) {
				$date = $this->_db->Quote(strftime('%F', strtotime($filter_date_from)));
				$where[] = " CASE WHEN (x.enddates) THEN $date <= x.enddates ELSE $date <= x.dates END ";
			}
			if (strtotime($filter_date_to)) {
				$date = $this->_db->Quote(strftime('%F', strtotime($filter_date_to)));
				$where[] = " $date >= x.dates ";
			}

			if ($filter_venue)
			{
				$where[] = ' l.id = ' . $this->_db->Quote($filter_venue);
			}
			else if (!is_null($filter_city) && $filter_city != "0") {
				$where[] = ' l.city = ' . $this->_db->Quote($filter_city);
			}
			else if (!is_null($filter_state) && $filter_state != "0") {
				$where[] = ' l.state = ' . $this->_db->Quote($filter_state);
			}
			// filter country
			else if (!is_null($filter_country) && $filter_country != "0") {
				$where[] = ' l.country = ' . $this->_db->Quote($filter_country);
			}
			else if (!is_null($filter_continent) && $filter_continent != "0") {
				$where[] = ' c.continent = ' . $this->_db->Quote($filter_continent);
			}

			// filter category
			if ($filter_category) {
				$category = $this->getCategory((int) $filter_category);
				if ($category) {
					$where[] = '(c.id = '.$this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
				}
			}
			// filter venue category
			if ($filter_venuecategory) {
				$category = $this->getVenueCategory((int) $filter_venuecategory);
				if ($category) {
					$where[] = '(vc.id = '.$this->_db->Quote($category->id) . ' OR (vc.lft > ' . $this->_db->Quote($category->lft) . ' AND vc.rgt < ' . $this->_db->Quote($category->rgt) . '))';
				}
			}

			if ($filter_event)
			{
				$where[] = ' a.id = ' . $this->_db->Quote($filter_event);
			}

			//custom fields
			foreach ((array) $customs as $key => $custom)
			{
				if ($custom != '')
				{
					if (is_array($custom)) {
						$custom = implode("/n", $custom);
					}
					$where[] = ' custom'.$key.' LIKE ' . $this->_db->Quote('%'.$custom.'%');
				}
			}

			$this->_filter = $where;
		}
		return $this->_filter;
	}

	public function getCountryOptions()
	{
		$mainframe = &JFactory::getApplication();
		$filter_continent = $mainframe->getUserState('com_redevent.search.filter_continent');

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('DISTINCT c.iso2 as value, c.name as text');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->join('INNER', '#__redevent_countries as c ON c.iso2 = v.country');
		$query->order('c.name');

		if ($filter_continent) {
			$query->where('c.continent = ' . $this->_db->Quote($filter_continent));
		}

		if ($this->getState('filter.language'))
		{
			$query->join('INNER', '#__redevent_events AS a ON a.id = x.eventid');
			$query->where('(a.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR a.language IS NULL)');
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function getStateOptions()
	{
		$mainframe = JFactory::getApplication();
		$filter_country = $mainframe->getUserState('com_redevent.search.filter_country');

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('DISTINCT v.state as value, v.state as text');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->join('LEFT', '#__redevent_countries as c ON c.iso2 = v.country');
		$query->order('v.state');

		if ($this->getState('filter.language'))
		{
			$query->join('INNER', '#__redevent_events AS a ON a.id = x.eventid');
			$query->where('(a.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR a.language IS NULL)');
		}

		if (!empty($filter_country))
		{
			$query->where('v.country = ' . $this->_db->Quote($filter_country));
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function getCityOptions()
	{
		$mainframe = JFactory::getApplication();
		$filter_country = $mainframe->getUserState('com_redevent.search.filter_country');
		$state =   $mainframe->getUserState('com_redevent.search.filter_state');

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('DISTINCT v.city as value, v.city as text');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->join('LEFT', '#__redevent_countries as c ON c.iso2 = v.country');
		$query->order('v.city');

		if (!empty($filter_country))
		{
			$query->where('v.country = ' . $this->_db->Quote($filter_country));
		}

		if (!empty($state))
		{
			$query->where('v.state = ' . $this->_db->Quote($state));
		}

		if ($this->getState('filter.language'))
		{
			$query->join('INNER', '#__redevent_events AS a ON a.id = x.eventid');
			$query->where('(a.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR a.language IS NULL)');
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}


	/**
	 * get list of events as options, according to category, venue, and venue category criteria
	 * @return unknown_type
	 */
	public function getEventsOptions()
	{
		$app = &JFactory::getApplication();
		$params = & $app->getParams();
		$filter_venuecategory = JRequest::getVar('filter_venuecategory');
		$filter_category = JRequest::getVar('filter_category', $params->get('category', 0));
		$filter_venue = JRequest::getVar('filter_venue');
		$task 		= JRequest::getWord('task');

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id AS value, a.title AS text');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_events AS a ON a.id = x.eventid');
		$query->join('INNER', '#__redevent_venues AS l ON l.id = x.venueid');
		$query->join('LEFT',  '#__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id');
		$query->join('LEFT',  '#__redevent_venues_categories AS vc ON xvcat.category_id = vc.id');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
		$query->join('INNER', '#__redevent_categories AS c ON c.id = xcat.category_id');

		// First thing we need to do is to select only needed events
		if ($task == 'archive')
		{
			$query->where(' x.published = -1');
		}
		else
		{
			$query->where(' x.published = 1');
		}

		$where = array();
		// filter category
		if ($filter_category)
		{
			$category = $this->getCategory((int) $filter_category);
			$where[] = '(c.id = '.$this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
		}

		// filter venue category
		if ($filter_venuecategory)
		{
			$category = $this->getVenueCategory((int) $filter_venuecategory);
			$where[] = '(vc.id = '.$this->_db->Quote($category->id) . ' OR (vc.lft > ' . $this->_db->Quote($category->lft) . ' AND vc.rgt < ' . $this->_db->Quote($category->rgt) . '))';
		}

		if ($filter_venue)
		{
			$where[] = ' l.id = ' . $this->_db->Quote($filter_venue);
		}

		if (count($where))
		{
			foreach ($where as $w)
			{
				$query->where($w);
			}
		}
		$query->group('a.id');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	/**
	 * get a category
	 * @param int id
	 * @return object
	 */
	public function getCategory($id)
	{
		$query = ' SELECT c.id, c.catname, c.lft, c.rgt '
		. ' FROM #__redevent_categories AS c '
		. ' WHERE c.id = '. $this->_db->Quote($id)
		;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();
		return $res;
	}

	/**
	 * get a venue category
	 * @param int id
	 * @return object
	 */
	public function getVenueCategory($id)
	{
		$query = ' SELECT vc.id, vc.name, vc.lft, vc.rgt '
		. ' FROM #__redevent_venues_categories as vc '
		. ' WHERE vc.id = '. $this->_db->Quote($id)
		;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();
		return $res;
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
