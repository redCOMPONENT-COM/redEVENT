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
 * redEVENT Component search Model
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       2.0
 */
class RedeventModelSearch extends RedeventModelBaseeventlist
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

		if ($params->exists('results_type'))
		{
			$results_type = $params->get('results_type', $params->get('default_search_results_type', 1));
		}
		else
		{
			$results_type = $params->get('default_search_results_type', 1);
		}

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
	 * @see RedeventModelBaseeventlist::getData()
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
	 * override to take into account search type
	 * @see RedeventModelBaseeventlist::_buildQuery()
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
	 * @see RedeventModelBaseeventlist::_buildWhere()
	 */
	protected function _buildWhere($query)
	{
		$app = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params = $app->getParams();

		$query->where('x.published = 1');
		$query->where('a.published <> 0');

		if ($this->getState('filter.language'))
		{
			$query->where('(a.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag())
				. ',' . $this->_db->quote('*') . ') OR a.language IS NULL)');
			$query->where('(c.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag())
				. ',' . $this->_db->quote('*') . ') OR c.language IS NULL)');
		}

		$filter = $this->getFilter();

		if ($params->get('requires_filter', 0) && (!$filter || empty($filter)))
		{
			// Will force query to return false...
			$filter = array('0');
		}

		$query->where($filter);

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
			$mainframe = JFactory::getApplication();
			$params    = $mainframe->getParams();
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
				// Clean filter variables
				$filter = JString::strtolower($filter);
				$filter = $this->_db->Quote('%' . $this->_db->escape($filter, true) . '%', false);

				$filterOr = array();
				$filterOr[] = 'LOWER(l.venue) LIKE ' . $filter;
				$filterOr[] = 'LOWER(l.city) LIKE ' . $filter;
				$filterOr[] = 'LOWER(c.name) LIKE ' . $filter;
				$filterOr[] = 'LOWER(a.title) LIKE ' . $filter;
				$filterOr[] = 'LOWER(x.title) LIKE ' . $filter;

				$where[] = implode(' OR ', $filterOr);
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
			elseif ($filter_city)
			{
				$where[] = ' l.city = ' . $this->_db->Quote($filter_city);
			}
			elseif ($filter_state)
			{
				$where[] = ' l.state = ' . $this->_db->Quote($filter_state);
			}
			// filter country
			elseif ($filter_country)
			{
				$where[] = ' l.country = ' . $this->_db->Quote($filter_country);
			}
			elseif ($filter_continent)
			{
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

	/**
	 * get list of events as options, according to category, venue, and venue category criteria
	 * @return unknown_type
	 */
	public function getEventsOptions()
	{
		$app = JFactory::getApplication();
		$params = $app->getParams();
		$filter_venuecategory = $this->state->get('filter_venuecategory');
		$filter_category = $this->state->get('filter_category', $params->get('category', 0));
		$filter_venue = $this->state->get('filter_venue');

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

		$query->where(' x.published = 1');

		$where = array();

		// Filter category
		if ($filter_category)
		{
			$category = $this->getCategory((int) $filter_category);
			$where[] = '(c.id = '.$this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
		}

		// Filter venue category
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
		$query = ' SELECT c.id, c.name AS catname, c.lft, c.rgt '
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
