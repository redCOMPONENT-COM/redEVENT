<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Base class foe events lists models
 *
 * @package     Joomla
 * @subpackage  redevent
 * @since       2.0
 */
class RedeventModelBasesessionlist extends RModel
{
	/**
	 * Events data array
	 *
	 * @var array
	 */
	protected $data = null;

	/**
	 * custom fields data array
	 *
	 * @var array
	 */
	protected $customfields = null;

	/**
	 * xref custom fields data array
	 *
	 * @var array
	 */
	protected $xrefcustomfields = null;

	/**
	 * Events total
	 *
	 * @var integer
	 */
	protected $total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $pagination = null;

	/**
	 * set limit
	 *
	 * @param   int  $value  value
	 *
	 * @return void
	 */
	public function setLimit($value)
	{
		$this->setState('limit', (int) $value);
	}

	/**
	 * set limitstart
	 *
	 * @param   int  $value  value
	 *
	 * @return void
	 */
	public function setLimitStart($value)
	{
		$this->setState('limitstart', (int) $value);
	}

	/**
	 * Method to get the Events
	 *
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->data))
		{
			$query = $this->buildQuery();

			$pagination = $this->getPagination();
			$this->data = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			$this->data = $this->_categories($this->data);
			$this->data = $this->_getPlacesLeft($this->data);
			$this->data = $this->_getPrices($this->data);
		}

		return $this->data;
	}

	/**
	 * Total nr of events
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotal()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->total))
		{
			$query = $this->buildQuery();
			$this->total = $this->_getListCount($query);
		}

		return $this->total;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination))
		{
			jimport('joomla.html.pagination');
			$total = $this->getTotal();
			$limit = $this->getState('limit');
			$limitstart = $this->getState('limitstart');

			if ($limitstart > $total)
			{
				$limitstart = floor($total / $limit) * $limit;
				$this->setState('limitstart', $limitstart);
			}

			$this->pagination = new JPagination($total, $limitstart, $limit);
		}

		return $this->pagination;
	}

	/**
	 * Build the query
	 *
	 * @return JDatabaseQuery
	 */
	protected function buildQuery()
	{
		$query = $this->buildSelectFrom();
		$query = $this->buildWhere($query);
		$query = $this->buildOrderBy($query);

		return $query;
	}

	/**
	 * Build the select and from parts
	 *
	 * @return JDatabaseQuery
	 */
	protected function buildSelectFrom()
	{
		$customs = $this->getCustomFields();
		$xcustoms = $this->getXrefCustomFields();

		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('x.dates, x.enddates, x.allday, x.times, x.endtimes, x.registrationend, x.id AS xref, x.session_code, x.details');
		$query->select('x.session_code');
		$query->select('x.maxattendees, x.maxwaitinglist, x.course_credit, x.featured, x.icaldetails, x.icalvenue, x.title as session_title');
		$query->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title');
		$query->select('a.*');

		// B/C compatibility as template table was extracted from event table
		$query->select('t.*, a.id AS id');
		$query->select('l.venue, l.city, l.state, l.url, l.street, l.country, l.locdescription, l.venue_code, l.id AS venue_id');
		$query->select('c.name AS catname, c.id AS catid');
		$query->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug');
		$query->select('CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug');
		$query->select('CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug');

		foreach ((array) $customs as $c)
		{
			$query->select('a.custom' . $c->id);
		}

		foreach ((array) $xcustoms as $c)
		{
			$query->select('x.custom' . $c->id);
		}

		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_events AS a ON a.id = x.eventid');
		$query->join('INNER', '#__redevent_event_template AS t ON t.id = a.template_id');
		$query->join('INNER', '#__redevent_venues AS l ON l.id = x.venueid');
		$query->join('LEFT', '#__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id');
		$query->join('LEFT', '#__redevent_venues_categories AS vc ON xvcat.category_id = vc.id');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
		$query->join('INNER', '#__redevent_categories AS c ON c.id = xcat.category_id');
		$query->group('x.id');

		return $query;
	}

	/**
	 * Build the order clause
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return object
	 */
	protected function buildOrderBy($query)
	{
		$filter_order = $this->getState('filter_order');
		$filter_order_dir = $this->getState('filter_order_Dir');

		if (preg_match("/field([0-9]+)/", $filter_order, $regs))
		{
			$filter_order = 'c' . $regs[1] . '.value';
		}

		$open_order = JComponentHelper::getParams('com_redevent')->get('open_dates_ordering', 0);
		$ordering_def = ($open_order ? 'x.dates IS NULL ' : 'x.dates IS NOT NULL ') . $filter_order_dir
			. ', x.dates ' . $filter_order_dir . ', x.times ' . $filter_order_dir . ', x.featured DESC';

		switch ($filter_order)
		{
			case 'x.dates':
				$ordering = $ordering_def;
				break;

			default:
				$ordering = $filter_order . ' ' . $filter_order_dir . ', ' . $ordering_def;
		}

		$query->order($ordering);

		return $query;
	}

	/**
	 * Build the where clause
	 *
	 * @param   object  $query  query
	 *
	 * @return object
	 */
	protected function buildWhere($query)
	{
		$app = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params = $app->getParams();

		// First thing we need to do is to select only needed events
		if ($filter_published = $this->getState('filter_published'))
		{
			$query->where('x.published = ' . (int) $filter_published);
		}
		else
		{
			$query->where('x.published = 1');
		}

		$query->where('a.published <> 0');

		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		* for the filter onto the WHERE clause of the item query.
		*/
		if ($params->get('filter_text'))
		{
			$filter = $this->getState('filter');

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

				if ($params->get('filter_text_search_descriptions', 0))
				{
					$filterOr[] = 'a.datdescription LIKE ' . $filter;
					$filterOr[] = 'a.summary LIKE ' . $filter;
					$filterOr[] = 'x.details LIKE ' . $filter;
				}

				$query->where('(' . implode(' OR ', $filterOr) . ')');
			}
		}

		if (is_numeric($this->getState('filter_event')) && $this->getState('filter_event'))
		{
			$query->where('a.id = ' . (int) $this->getState('filter_event'));
		}

		if ($filter_venuecategory = $this->getState('filter_venuecategory'))
		{
			$query->where('vc.id = ' . $filter_venuecategory);
		}

		if ($filter_venuecategory = $this->getState('filter_venuecategory'))
		{
			$query->where('vc.id = ' . $filter_venuecategory);
		}

		if ($filter_multivenue = $this->getState('filter_multivenue'))
		{
			$or = array();

			foreach ($filter_multivenue as $v)
			{
				$or[] = ' l.id = ' . (int) $v;
			}

			$query->where('(' . implode(' OR ', $or) . ')');
		}
		elseif ($filter_venue = $this->getState('filter_venue'))
		{
			$query->where(' l.id = ' . $this->_db->Quote($filter_venue));
		}

		if ($ev = $this->getState('filter_event'))
		{
			$query->where('a.id = ' . $this->_db->Quote($ev));
		}

		if ($filter_multicategory = $this->getState('filter_multicategory'))
		{
			$or = array();

			foreach ($filter_multicategory as $cat)
			{
				$category = $this->getCategory((int) $cat);

				if ($category)
				{
					$or[] = '(c.id = ' . (int) $category->id . ' OR (c.lft > ' . (int) $category->lft . ' AND c.rgt < ' . (int) $category->rgt . '))';
				}
			}

			if (!empty($or))
			{
				$query->where('(' . implode(' OR ', $or) . ')');
			}
		}
		elseif ($cat = $this->getState('filter_category'))
		{
			$category = $this->getCategory((int) $cat);

			if ($category)
			{
				$query->where(
					'(c.id = ' . (int) $category->id . ' OR (c.lft > ' . (int) $category->lft . ' AND c.rgt < ' . (int) $category->rgt . '))'
				);
			}
		}

		$sstate = $params->get('session_state', '0');

		if ($sstate == 1)
		{
			$now = strftime('%Y-%m-%d %H:%M');
			$query->where('(CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > ' . $this->_db->Quote($now));
		}
		elseif ($sstate == 2)
		{
			$query->where('x.dates IS NULL');
		}

		// State
		if ($state = $this->getState('state', '', 'request', 'string'))
		{
			$query->where(' STRCMP(l.state, ' . $this->_db->Quote($state) . ') = 0 ');
		}

		// Country
		if ($country = $this->getState('country', '', 'request', 'string'))
		{
			$query->where(' STRCMP(l.country, ' . $this->_db->Quote($country) . ') = 0 ');
		}

		$customs = $this->getState('filter_customs');

		foreach ((array) $customs as $key => $custom)
		{
			if ($custom)
			{
				if (is_array($custom))
				{
					$or = array();

					foreach ($custom as $c)
					{
						$or[] = 'custom' . $key . ' LIKE ' . $this->_db->Quote('%' . $c . '%');
					}

					$query->where('(' . implode(" OR ", $or) . ')');
				}
				else
				{
					$query->where('custom' . $key . ' LIKE ' . $this->_db->Quote('%' . $custom . '%'));
				}
			}
		}

		if ($this->getState('filter.language'))
		{
			$query->where(
				'(a.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag())
				. ',' . $this->_db->quote('*') . ') OR a.language IS NULL)'
			);
			$query->where(
				'(c.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag())
				. ',' . $this->_db->quote('*') . ') OR c.language IS NULL)'
			);
		}

		// ACL
		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$query->where('(l.access IN (' . $gids . '))');
		$query->where('(c.access IN (' . $gids . '))');
		$query->where('(vc.id IS NULL OR vc.access IN (' . $gids . '))');

		return $query;
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	protected function _buildEventsOptionsWhere()
	{
		$app = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params 	= $app->getParams();

		$where = array();

		// First thing we need to do is to select only needed events
		if ($filter_published = $this->getState('filter_published'))
		{
			$where[] = ' x.published = ' . (int) $filter_published;
		}
		else
		{
			$where[] = ' x.published = 1';
		}

		$where[] = ' a.published <> 0';

		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		* for the filter onto the WHERE clause of the item query.
		*/
		if ($params->get('filter_text'))
		{
			$filter 		  = $this->getState('filter');
			$filter_type 	= $this->getState('filter_type');

			if ($filter)
			{
				// Clean filter variables
				$filter 		= JString::strtolower($filter);
				$filter			= $this->_db->Quote('%' . $this->_db->escape($filter, true) . '%', false);
				$filter_type 	= JString::strtolower($filter_type);

				switch ($filter_type)
				{
					case 'title' :
						$where[] = ' LOWER( a.title ) LIKE ' . $filter;
						break;

					case 'venue' :
						$where[] = ' LOWER( l.venue ) LIKE ' . $filter;
						break;

					case 'city' :
						$where[] = ' LOWER( l.city ) LIKE ' . $filter;
						break;

					case 'type' :
						$where[] = '  LOWER( c.name ) LIKE ' . $filter;
						break;
				}
			}
		}

		if ($filter_venue = $this->getState('filter_venue'))
		{
			$where[] = ' l.id = ' . $this->_db->Quote($filter_venue);
		}

		if ($cat = $this->getState('filter_category'))
		{
			$category = $this->getCategory((int) $cat);

			if ($category)
			{
				$where[] = '(c.id = ' . $this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft)
					. ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
			}
		}

		$sstate = $params->get('session_state', '0');

		if ($sstate == 1)
		{
			$now = strftime('%Y-%m-%d %H:%M');
			$where[] = '(CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > ' . $this->_db->Quote($now);
		}
		elseif ($sstate == 2)
		{
			$where[] = 'x.dates IS NULL';
		}

		return ' WHERE ' . implode(' AND ', $where);
	}

	/**
	 * adds categories property to event rows
	 *
	 * @param   array  $rows  rows of events
	 *
	 * @return array
	 */
	protected function _categories($rows)
	{
		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('c.id, c.name AS name, c.color');
			$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug');
			$query->from('#__redevent_categories as c');
			$query->join('INNER', '#__redevent_event_category_xref as x ON x.category_id = c.id');
			$query->where('c.published = 1');
			$query->where('x.event_id = ' . $this->_db->Quote($rows[$i]->id));
			$query->where('c.access IN (' . $gids . ')');
			$query->group('c.id');
			$query->order('c.ordering');

			if ($this->getState('filter.language'))
			{
				$query->where(
					'(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)'
				);
			}

			$db->setQuery($query);

			$rows[$i]->categories = $db->loadObjectList();
		}

		return $rows;
	}

	/**
	 * adds registered (int) and waiting (int) properties to rows.
	 *
	 * @param   array  $rows  sessions array
	 *
	 * @return array
	 */
	protected function _getPlacesLeft($rows)
	{
		foreach ((array) $rows as $k => $r)
		{
			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('r.waitinglist, COUNT(r.id) AS total');
			$query->from('#__redevent_register AS r');
			$query->where('r.xref = ' . $this->_db->Quote($r->xref));
			$query->where('r.confirmed = 1');
			$query->where('r.cancelled = 0');
			$query->group('r.waitinglist');
			$db->setQuery($query);

			$res = $db->loadObjectList();

			$rows[$k]->registered = (isset($res[0]) ? $res[0]->total : 0);
			$rows[$k]->waiting = (isset($res[1]) ? $res[1]->total : 0);
		}

		return $rows;
	}

	/**
	 * adds registered (int) and waiting (int) properties to rows.
	 *
	 * @param   array  $rows  sessions array
	 *
	 * @return array
	 */
	protected function _getPrices($rows)
	{
		if (!$rows)
		{
			return $rows;
		}

		$ids = array();

		foreach ($rows as $k => $r)
		{
			$ids[$r->xref] = $k;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('sp.*, p.name, p.alias, p.image, p.tooltip');
		$query->select('CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\', p.id, p.alias) ELSE p.id END as slug');
		$query->select('CASE WHEN CHAR_LENGTH(sp.currency) THEN sp.currency ELSE f.currency END as currency');
		$query->from('#__redevent_sessions_pricegroups AS sp');
		$query->join('INNER', '#__redevent_pricegroups AS p on p.id = sp.pricegroup_id');
		$query->join('INNER', '#__redevent_event_venue_xref AS x on x.id = sp.xref');
		$query->join('INNER', '#__redevent_events AS e on e.id = x.eventid');
		$query->join('INNER', '#__redevent_event_template AS t ON t.id =  e.template_id');
		$query->join('LEFT', '#__rwf_forms AS f on t.redform_id = f.id');
		$query->where('sp.xref IN (' . implode(",", array_keys($ids)) . ')');
		$query->order('p.ordering ASC');
		$db->setQuery($query);
		$res = $db->loadObjectList();

		// Sort this out
		$prices = array();

		foreach ((array) $res as $p)
		{
			if (!isset($prices[$p->xref]))
			{
				$prices[$p->xref] = array($p);
			}
			else
			{
				$prices[$p->xref][] = $p;
			}
		}

		// Add to rows
		foreach ($rows as $k => $r)
		{
			if (isset($prices[$r->xref]))
			{
				$rows[$k]->prices = $prices[$r->xref];
			}
			else
			{
				$rows[$k]->prices = null;
			}
		}

		return $rows;
	}

	/**
	 * returns all custom fields for events
	 *
	 * @return array
	 */
	public function getCustomFields()
	{
		return RedeventHelper::getEventCustomFields();
	}

	/**
	 * returns all custom fields for xrefs
	 *
	 * @return array
	 */
	public function getXrefCustomFields()
	{
		return RedeventHelper::getSessionCustomFields();
	}

	/**
	 * returns custom fields to be shown in lists
	 *
	 * @return array
	 */
	public function getListCustomFields()
	{
		$res = array();

		$fields = array_merge((array) $this->getCustomFields(), (array) $this->getXrefCustomFields());

		if (!empty($fields))
		{
			uasort(
				$fields,
				function ($a, $b) {
					return $a->ordering - $b->ordering;
				}
			);

			foreach ((array) $fields as $f)
			{
				if ($f->in_lists)
				{
					$res[$f->id] = $f;
				}
			}
		}

		return $res;
	}

	/**
	 * compare custom fields by ordering
	 *
	 * @param   object  $a  field
	 * @param   object  $b  field
	 *
	 * @return number
	 */
	protected function _cmpCustomFields($a, $b)
	{
		return $a->ordering - $b->ordering;
	}

	/**
	 * returns searchable custom fields
	 *
	 * @return array
	 */
	public function getSearchableCustomFields()
	{
		$fields = $this->getCustomFields();
		$res = array();

		foreach ((array) $fields as $f)
		{
			if ($f->searchable)
			{
				$res[] = $f;
			}
		}

		return $res;
	}

	/**
	 * return filter for event custom fields
	 *
	 * @return RedeventAbstractCustomfield[]
	 */
	public function getCustomFilters()
	{
		$query = $this->_db->getQuery(true)
			->select('f.*')
			->from('#__redevent_fields AS f')
			->where('f.published = 1')
			->where('f.searchable = 1')
			->order('f.ordering ASC');

		if ($this->getState('filter.language'))
		{
			$query->where(
				'(f.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR f.language IS NULL)'
			);
		}

		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		$filters = array();

		foreach ($rows as $r)
		{
			$field = RedeventFactoryCustomfield::getField($r->type);
			$field->bind($r);
			$filters[] = $field;
		}

		return $filters;
	}

	/**
	 * get list of categories as options, according to acl
	 *
	 * @return array
	 */
	public function getCategoriesOptions()
	{
		$query = $this->buildQuery()
			->clear('select')
			->clear('group')
			->select('c.id')
			->group('c.id');

		$this->_db->setQuery($query);
		$res = $this->_db->loadColumn();

		return RedeventHelper::getEventsCatOptions(true, false, $res);
	}

	/**
	 * get venues options
	 *
	 * @return array
	 */
	public function getVenuesOptions()
	{
		$vcat    = $this->getState('filter_venuecategory');
		$city    = $this->getState('filter_city');
		$country = $this->getState('filter_country');

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$query = ' SELECT DISTINCT v.id AS value, '
		. ' CASE WHEN CHAR_LENGTH(v.city) AND v.city <> v.venue THEN CONCAT_WS(\' - \', v.venue, v.city) ELSE v.venue END as text '
		. ' FROM #__redevent_venues AS v '
		. ' LEFT JOIN #__redevent_venue_category_xref AS xcat ON xcat.venue_id = v.id '
		. ' LEFT JOIN #__redevent_venues_categories AS vcat ON vcat.id = xcat.category_id ';
		$where = array();

		if ($vcat)
		{
			$category = $this->getVenueCategory($vcat);
			$where[] = ' (vcat.id = ' . $this->_db->Quote($category->id) . ' OR (vcat.lft > ' . $this->_db->Quote($category->lft)
				. ' AND vcat.rgt < ' . $this->_db->Quote($category->rgt) . '))';
		}

		if ($city)
		{
			$where[] = ' v.city = ' . $this->_db->Quote($city);
		}

		if ($country)
		{
			$where[] = ' v.country = ' . $this->_db->Quote($country);
		}

		// Acl
		$where[] = ' (v.access IN (' . $gids . ')) ';
		$where[] = ' (vcat.id IS NULL OR vcat.access IN (' . $gids . ')) ';

		if (count($where))
		{
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		$query .= ' ORDER BY v.venue ';
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	/**
	 * returns events as options
	 *
	 * @return array
	 */
	public function getEventsOptions()
	{
		$query = $this->buildSelectFrom();
		$query->select('a.id AS value, a.title AS text');
		$query->where(' a.published <> 0');
		$query->clear('group')->group('a.id');
		$query->order('a.title, x.title ASC');

		// ACL
		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$query->where('(l.access IN (' . $gids . '))');
		$query->where('(c.access IN (' . $gids . '))');
		$query->where('(vc.id IS NULL OR vc.access IN (' . $gids . '))');

		if ($filter_venue = $this->getState('filter_venue'))
		{
			$query->where(' l.id = ' . $this->_db->Quote($filter_venue));
		}

		if ($cat = $this->getState('filter_category'))
		{
			$category = $this->getCategory((int) $cat);

			if ($category)
			{
				$query->where(
					'(c.id = ' . $this->_db->Quote($category->id)
					. ' OR (c.lft > ' . $this->_db->Quote($category->lft)
					. ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))'
				);
			}
		}

		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}

	/**
	 * get a category
	 *
	 * @param   int  $id  category id
	 *
	 * @return object
	 */
	public function getCategory($id)
	{
		$query = ' SELECT c.id, c.name, c.lft, c.rgt '
		. ' FROM #__redevent_categories AS c '
		. ' WHERE c.id = ' . $this->_db->Quote($id);
		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();

		return $res;
	}

	/**
	 * get a category
	 *
	 * @param   int  $id  category id
	 *
	 * @return object
	 */
	public function getVenueCategory($id)
	{
		$query = ' SELECT c.id, c.name, c.lft, c.rgt '
			. ' FROM #__redevent_venues_categories AS c '
			. ' WHERE c.id = ' . $this->_db->Quote($id);
		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();

		return $res;
	}

	/**
	 * Get countries as options
	 *
	 * @return mixed
	 */
	public function getCountryOptions()
	{
		$db      = $this->_db;
		$query = $db->getQuery(true);

		$query->select('DISTINCT c.iso2 as value, c.name as text');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->join('INNER', '#__redevent_countries as c ON c.iso2 = v.country');
		$query->order('c.name');

		if ($filter_continent = $this->getState('filter_continent'))
		{
			$query->where('c.continent = ' . $this->_db->Quote($filter_continent));
		}

		if ($this->getState('filter.language'))
		{
			$query->join('INNER', '#__redevent_events AS a ON a.id = x.eventid');
			$query->where('(a.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag())
				. ',' . $this->_db->quote('*') . ') OR a.language IS NULL)'
			);
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get states as options
	 *
	 * @return mixed
	 */
	public function getStateOptions()
	{
		$db      = $this->_db;
		$query = $db->getQuery(true);

		$query->select('DISTINCT v.state as value, v.state as text');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->join('LEFT', '#__redevent_countries as c ON c.iso2 = v.country');
		$query->order('v.state');

		if ($this->getState('filter.language'))
		{
			$query->join('INNER', '#__redevent_events AS a ON a.id = x.eventid');
			$query->where('(a.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag())
				. ',' . $this->_db->quote('*') . ') OR a.language IS NULL)'
			);
		}

		if ($filter_country = $this->getState('filter_country'))
		{
			$query->where('v.country = ' . $this->_db->Quote($filter_country));
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get cities as options
	 *
	 * @return mixed
	 */
	public function getCityOptions()
	{
		$filter_country = $this->getState('filter_country');
		$state = $this->getState('filter_state');

		$db      = $this->_db;
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
			$query->where('(a.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag())
				. ',' . $this->_db->quote('*') . ') OR a.language IS NULL)'
			);
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Add payment info to items
	 *
	 * @param   array  $items  items
	 *
	 * @return array
	 */
	protected function addPaymentInfo($items)
	{
		if (!$items)
		{
			return $items;
		}

		$sids = array();

		foreach ($items as $item)
		{
			$sids[] = $item->sid;
		}

		$paymentRequests = RdfCore::getSubmissionsPaymentRequests($sids);

		foreach ($items as &$item)
		{
			$item->paid = 1;

			if (isset($paymentRequests[$item->sid]))
			{
				$item->paymentRequests = $paymentRequests[$item->sid];

				foreach ($paymentRequests[$item->sid] as $pr)
				{
					if ($pr->paid == 0)
					{
						$item->paid = 0;
						break;
					}
				}
			}
			else
			{
				$item->paymentRequests = false;
			}
		}

		return $items;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		parent::populateState();

		$app = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params = $app->getParams('com_redevent');

		// Get the number of events from database
		$limit       	= $app->getUserStateFromRequest('com_redevent.limit', 'limit', $params->def('display_num', 0), 'int');
		$limitstart		= $app->input->getInt('limitstart', 0);

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get the filter request variables
		$filterOrder = $params->get('session_orderby') ?: $app->input->getCmd('filter_order', 'x.dates');
		$this->setState('filter_order', $filterOrder ?: 'x.dates');

		$filterOrderDir = $params->get('session_orderby_dir') ?:
			(strtoupper($app->input->getCmd('filter_order_Dir', 'ASC')) == 'DESC' ? 'DESC' : 'ASC');
		$this->setState('filter_order_Dir', $filterOrderDir);

		$this->setState('filter',      $app->input->get('filter', '', 'string'));
		$this->setState('filter_type', $app->input->get('filter_type', '', 'string'));

		$this->setState('filter_event',    $app->input->get('filter_event', 0, 'int'));
		$this->setState('filter_category', $app->input->get('filter_category', 0, 'int'));
		$this->setState('filter_venue', $app->input->get('filter_venue', 0, 'int'));
		$this->setState('filter_date', $app->input->get('filter_date', '', 'string'));
		$this->setState('filter_date_from', $app->input->get('filter_date_from', '', 'string'));
		$this->setState('filter_date_to', $app->input->get('filter_date_to', '', 'string'));

		$this->setState('filter_multicategory', $app->input->get('filter_multicategory', null, 'array'));
		$this->setState('filter_multivenue',    $app->input->get('filter_multivenue',    null, 'array'));

		$this->setState('filter_continent', $app->input->get('filter_continent', '', 'string'));
		$this->setState('filter_country', $app->input->get('filter_country', '', 'string'));
		$this->setState('filter_state', $app->input->get('filter_state', '', 'string'));
		$this->setState('filter_city', $app->input->get('filter_city', '', 'string'));

		$this->setState('filter_venuecategory', $app->input->getInt('filter_venuecategory', 0));

		$customs      = $app->input->get('filtercustom', array(), 'array');
		$this->setState('filter_customs', $customs);

		$this->setState('filter.language', $app->getLanguageFilter());
	}
}
