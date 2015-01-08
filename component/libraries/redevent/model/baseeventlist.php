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
 * Base class foe events lists models
 *
 * @package     Joomla
 * @subpackage  redevent
 * @since       2.0
 */
class RedeventModelBaseeventlist extends RModel
{
	/**
	 * Events data array
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * custom fields data array
	 *
	 * @var array
	 */
	protected $_customfields = null;

	/**
	 * xref custom fields data array
	 *
	 * @var array
	 */
	protected $_xrefcustomfields = null;

	/**
	 * Events total
	 *
	 * @var integer
	 */
	protected $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $_pagination = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$app = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params 	= & $app->getParams('com_redevent');

		// Get the number of events from database
		$limit       	= $app->getUserStateFromRequest('com_redevent.limit', 'limit', $params->def('display_num', 0), 'int');
		$limitstart		= $app->input->getInt('limitstart', 0);

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get the filter request variables
		$this->setState('filter_order',     $app->input->getCmd('filter_order', 'x.dates'));
		$this->setState('filter_order_Dir', strtoupper($app->input->getCmd('filter_order_Dir', 'ASC')) == 'DESC' ? 'DESC' : 'ASC');

		$this->setState('filter',      $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter',      'filter', '', 'string'));
		$this->setState('filter_type', $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_type', 'filter_type', '', 'string'));

		$this->setState('filter_event',    $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_event',    'filter_event', 0, 'int'));
		$this->setState('filter_category', $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_category', 'filter_category', 0, 'int'));
		$this->setState('filter_venue',    $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_venue',    'filter_venue',    0, 'int'));

		$this->setState('filter_multicategory', $app->input->get('filter_multicategory', null, 'array'));
		$this->setState('filter_multivenue',    $app->input->get('filter_multivenue',    null, 'array'));

		$customs      = $app->input->get('filtercustom', array(), 'array');
		$this->setState('filter_customs', $customs);

		$this->setState('filter.language', $app->getLanguageFilter());
	}

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
	 * @access public
	 * @return array
	 */
	public function &getData()
	{
		$pop	= JRequest::getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();

			if ($pop)
			{
				// Put a limit for print pagination
				// $this->setLimit(5);
			}

			$pagination = $this->getPagination();
			$this->_data = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			$this->_data = $this->_categories($this->_data);
			$this->_data = $this->_getPlacesLeft($this->_data);
			$this->_data = $this->_getPrices($this->_data);
		}

		return $this->_data;
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
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
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
		if (empty($this->_pagination))
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

			$this->_pagination = new JPagination($total, $limitstart, $limit);
		}

		return $this->_pagination;
	}

	/**
	 * Build the query
	 *
	 * @access private
	 * @return string
	 */
	protected function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$customs = $this->getCustomFields();
		$xcustoms = $this->getXrefCustomFields();
		$acl = RedeventUserAcl::getInstance();

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$db = &JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.dates, x.enddates, x.times, x.endtimes, x.registrationend, x.id AS xref, x.session_code');
		$query->select('x.maxattendees, x.maxwaitinglist, x.course_credit, x.featured, x.icaldetails, x.icalvenue, x.title as session_title');
		$query->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title');
		$query->select('a.id, a.title, a.created, a.datdescription, a.registra, a.datimage, a.summary, a.submission_type_external');
		$query->select('a.redform_id');
		$query->select('l.venue, l.city, l.state, l.url, l.street, l.country, l.locdescription, l.venue_code, l.id AS venue_id');
		$query->select('c.name AS catname, c.id AS catid');
		$query->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug');
		$query->select('CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug');
		$query->select('CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug');

		// Add the custom fields
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
		$query->join('INNER', '#__redevent_venues AS l ON l.id = x.venueid');
		$query->join('LEFT', '#__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id');
		$query->join('LEFT', '#__redevent_venues_categories AS vc ON xvcat.category_id = vc.id');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
		$query->join('INNER', '#__redevent_categories AS c ON c.id = xcat.category_id');
		$query->where('(l.access IN (' . $gids . '))');
		$query->where('(c.access IN (' . $gids . '))');
		$query->where('(vc.id IS NULL OR vc.access IN (' . $gids . '))');
		$query->group('x.id');

		$query = $this->_buildWhere($query);
		$query = $this->_buildOrderBy($query);

		return $query;
	}

	/**
	 * Build the order clause
	 *
	 * @param   object  $query  query
	 *
	 * @return object
	 */
	protected function _buildOrderBy($query)
	{
		$filter_order		  = $this->getState('filter_order');
		$filter_order_dir	= $this->getState('filter_order_dir');

		if (preg_match("/field([0-9]+)/", $filter_order, $regs))
		{
			$filter_order = 'c' . $regs[1] . '.value';
		}

		$open_order = JComponentHelper::getParams('com_redevent')->get('open_dates_ordering', 0);
		$ordering_def = $open_order ? 'x.dates = 0 ' . $filter_order_dir . ', x.dates ' . $filter_order_dir
			: 'x.dates > 0 ' . $filter_order_dir . ', x.dates ' . $filter_order_dir;

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
	protected function _buildWhere($query)
	{
		$app = JFactory::getApplication();

		$user		= JFactory::getUser();
		$gid		= max($user->getAuthorisedViewLevels());

		// Get the paramaters of the active menu item
		$params 	= $app->getParams();

		// First thing we need to do is to select only needed events
		if ($app->input->getCmd('task') == 'archive')
		{
			$query->where(' x.published = -1');
		}
		else
		{
			$query->where(' x.published = 1');
		}

		$query->where('a.published <> 0');

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
				$filter			= $this->_db->Quote('%' . $this->_db->getEscaped($filter, true) . '%', false);
				$filter_type 	= JString::strtolower($filter_type);

				switch ($filter_type)
				{
					case 'venue' :
						$query->where(' LOWER( l.venue ) LIKE ' . $filter);
						break;

					case 'city' :
						$query->where(' LOWER( l.city ) LIKE ' . $filter);
						break;

					case 'type' :
						$query->where('  LOWER( c.name ) LIKE ' . $filter);
						break;

					case 'title' :
					default:
						$query->where(' LOWER( a.title ) LIKE ' . $filter);
						break;
				}
			}
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

			$query->where('(' . implode(' OR ', $or) . ')');
		}
		elseif ($cat = $this->getState('filter_category'))
		{
			$category = $this->getCategory((int) $cat);

			if ($category)
			{
				$query->where('(c.id = ' . (int) $category->id . ' OR (c.lft > ' . (int) $category->lft . ' AND c.rgt < ' . (int) $category->rgt . '))');
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
			$query->where('x.dates = 0');
		}

		// State
		if ($state = JRequest::getVar('state', '', 'request', 'string'))
		{
			$query->where(' STRCMP(l.state, ' . $this->_db->Quote($state) . ') = 0 ');
		}

		// Country
		if ($country = JRequest::getVar('country', '', 'request', 'string'))
		{
			$query->where(' STRCMP(l.country, ' . $this->_db->Quote($country) . ') = 0 ');
		}

		$customs = $this->getState('filter_customs');

//		echo '<pre>'; echo print_r($customs, true); echo '</pre>'; exit;
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
			$query->where('(a.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR a.language IS NULL)');
			$query->where('(c.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR c.language IS NULL)');
		}

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
		$app = &JFactory::getApplication();

		$user		= & JFactory::getUser();
		$gid		= max($user->getAuthorisedViewLevels());

		// Get the paramaters of the active menu item
		$params 	= & $app->getParams();

		$task 		= JRequest::getWord('task');

		$where = array();

		// First thing we need to do is to select only needed events
		if ($task == 'archive')
		{
			$where[] = ' x.published = -1 ';
		}
		else
		{
			$where[] = ' x.published = 1 ';
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
				$filter			= $this->_db->Quote('%' . $this->_db->getEscaped($filter, true) . '%', false);
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
				$where[] = '(c.id = ' . $this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
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
			$where[] = 'x.dates = 0';
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
			$db = &JFactory::getDbo();
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
				$query->where('(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)');
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
		$query->join('LEFT', '#__rwf_forms AS f on e.redform_id = f.id');
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
		if (empty($this->_customfields))
		{
			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('f.id, f.name, f.in_lists, f.searchable, f.ordering, f.tips');
			$query->from('#__redevent_fields AS f');
			$query->where('f.published = 1');
			$query->where('f.object_key = ' . $db->Quote('redevent.event'));
			$query->order('f.ordering ASC');

			if ($this->getState('filter.language'))
			{
				$query->where('(f.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR f.language IS NULL)');
			}

			$db->setQuery($query);
			$this->_customfields = $db->loadObjectList();
		}

		return $this->_customfields;
	}

	/**
	 * returns all custom fields for xrefs
	 *
	 * @return array
	 */
	public function getXrefCustomFields()
	{
		if (empty($this->_xrefcustomfields))
		{
			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('f.id, f.name, f.in_lists, f.searchable, f.ordering, f.tips');
			$query->from('#__redevent_fields AS f');
			$query->where('f.published = 1');
			$query->where('f.object_key = ' . $db->Quote('redevent.xref'));
			$query->order('f.ordering ASC');

			if ($this->getState('filter.language'))
			{
				$query->where('(f.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR f.language IS NULL)');
			}

			$db->setQuery($query);
			$this->_xrefcustomfields = $db->loadObjectList();
		}

		return $this->_xrefcustomfields;
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
			uasort($fields, array('RedeventModelBaseeventlist', '_cmpCustomFields'));

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
	 * @return void
	 */
	public function getCustomFilters()
	{
		$query = ' SELECT f.* FROM #__redevent_fields AS f '
		. ' WHERE f.published = 1 '
		. '   AND f.searchable = 1 '
		//           . '   AND f.object_key = '. $this->_db->Quote("redevent.event")
		. ' ORDER BY f.ordering ASC ';
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
		$app = &JFactory::getApplication();
		$filter_venuecategory = JRequest::getVar('filter_venuecategory');
		$filter_venue         = JRequest::getVar('filter_venue');
		$task 		            = JRequest::getWord('task');

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		// Get Events from Database
		$query  = ' SELECT c.id '
		. ' FROM #__redevent_event_venue_xref AS x'
		. ' INNER JOIN #__redevent_events AS a ON a.id = x.eventid'
		. ' INNER JOIN #__redevent_venues AS l ON l.id = x.venueid'
		. ' LEFT JOIN #__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id'
		. ' LEFT JOIN #__redevent_venues_categories AS vc ON xvcat.category_id = vc.id'
		. ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
		. ' INNER JOIN #__redevent_categories AS c ON c.id = xcat.category_id';

		$where = array();

		// First thing we need to do is to select only needed events
		if ($task == 'archive')
		{
			$where[] = ' x.published = -1';
		}
		else
		{
			$where[] = ' x.published = 1';
		}
		$where[] = ' a.published <> 0 ';

		// Filter category
		if ($filter_venuecategory)
		{
			$category = $this->getVenueCategory((int) $filter_venuecategory);
			$where[] = '(vc.id = ' . $this->_db->Quote($category->id) . ' OR (vc.lft > ' . $this->_db->Quote($category->lft) . ' AND vc.rgt < ' . $this->_db->Quote($category->rgt) . '))';
		}

		if ($filter_venue)
		{
			$where[] = ' l.id = ' . $this->_db->Quote($filter_venue);
		}

		// Acl
		$where[] = ' (l.access IN (' . $gids . ')) ';
		$where[] = ' (c.access IN (' . $gids . ')) ';
		$where[] = ' (vc.id IS NULL OR vc.access IN (' . $gids . ')) ';

		if (count($where))
		{
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		$query .= ' GROUP BY c.id ';

		$this->_db->setQuery($query);
		$res = $this->_db->loadResultArray();

		return RedeventHelper::getEventsCatOptions(true, false, $res);
	}

	/**
	 * get venues options
	 *
	 * @return array
	 */
	public function getVenuesOptions()
	{
		$app = &JFactory::getApplication();
		$vcat    = JRequest::getVar('filter_venuecategory');
		$city    = JRequest::getVar('filter_city');
		$country = JRequest::getVar('filter_country');

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
			$category = $this->getCategory($vcat);
			$where[] = ' (vcat.id = ' . $this->_db->Quote($category->id) . ' OR (vcat.lft > ' . $this->_db->Quote($category->lft) . ' AND vcat.rgt < ' . $this->_db->Quote($category->rgt) . '))';
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
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildEventsOptionsWhere();
		$customs = $this->getCustomFields();
		$xcustoms = $this->getXrefCustomFields();

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		// Get Events from Database
		$query = 'SELECT a.id AS value, a.title AS text ';

		// Add the custom fields
		foreach ((array) $customs as $c)
		{
			$query .= ', a.custom' . $c->id;
		}

		// Add the custom fields
		foreach ((array) $xcustoms as $c)
		{
			$query .= ', x.custom' . $c->id;
		}

		$query .= ' FROM #__redevent_event_venue_xref AS x'
		. ' INNER JOIN #__redevent_events AS a ON a.id = x.eventid'
		. ' INNER JOIN #__redevent_venues AS l ON l.id = x.venueid'
		. ' LEFT JOIN #__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id'
		. ' LEFT JOIN #__redevent_venues_categories AS vc ON xvcat.category_id = vc.id'
		. ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
		. ' INNER JOIN #__redevent_categories AS c ON c.id = xcat.category_id ';

		$query .= $where
		. ' AND (l.access IN (' . $gids . ')) '
		. ' AND (c.access IN (' . $gids . ')) '
		. ' AND (vc.access IN (' . $gids . ') OR vc.id IS NULL) '
		. ' GROUP BY (a.id) '
		. ' ORDER BY a.title, x.title ASC ';
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
		$query = ' SELECT c.id, c.name AS catname, c.lft, c.rgt '
		. ' FROM #__redevent_categories AS c '
		. ' WHERE c.id = ' . $this->_db->Quote($id);
		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();

		return $res;
	}
}
