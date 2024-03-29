<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevents Component my events Model
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventModelMysessions extends RedeventModelBasesessionlist
{
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

		$mainframe = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params = $mainframe->getParams('com_redevent');

		// Get the number of events from database
		$limit 					= $mainframe->getUserStateFromRequest('com_redevent.myevents.limit', 'limit', $params->def('display_num', 0), 'int');
		$limitstart 		= $mainframe->input->get('limitstart', 0, '', 'int');

		$this->setState('list.limit', $limit);
		$this->setState('list.start', $limitstart);

		// Get the filter request variables
		$this->setState('filter_order', $mainframe->input->getCmd('filter_order', 'x.dates'));
		$this->setState('filter_order_dir', $mainframe->input->getCmd('filter_order_Dir', 'ASC'));
	}

	/**
	 * Method to get the sessions
	 *
	 * @return array
	 */
	public function getItems()
	{
		$pop = JFactory::getApplication()->input->getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->data))
		{
			$query = $this->buildQuery();
			$pagination = $this->getPagination();

			if ($pop)
			{
				$this->data = $this->_getList($query);
			}
			else
			{
				$this->data = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			}

			$this->data = $this->_categories($this->data);
			$this->data = $this->_getPlacesLeft($this->data);
		}

		return $this->data;
	}

	/**
	 * Build the query
	 *
	 * @return JDatabaseQuery
	 */
	protected function buildQuery()
	{
		$query = $this->_buildQuerySelect();

		// Get the WHERE and ORDER BY clauses for the query
		$query = $this->_buildListWhere($query);
		$query = $this->_buildListOrderBy($query);

		return $query;
	}

	/**
	 * build base select and joins for sessions queries
	 *
	 * @return JDatabaseQuery
	 */
	protected function _buildQuerySelect()
	{
		$query = parent::buildSelectFrom();
		$query->select('l.id as locid');
		$query->select('x.published');
		$query->select('r.id AS attendee_id, r.sid, r.submit_key');
		$query->join('LEFT', '#__redevent_register AS r ON r.xref = x.id');

		return $query;
	}

	/**
	 * Build the order clause
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return string
	 */
	protected function _buildListOrderBy($query)
	{
		$filter_order = $this->getState('filter_order');
		$filter_order_dir = $this->getState('filter_order_dir');

		$query->order($filter_order . ' ' . $filter_order_dir . ', x.dates, x.times');

		return $query;
	}

	/**
	 * Build the where clause
	 *
	 * @param   JDatabaseQuery  $query  query object
	 *
	 * @return string
	 */
	protected function _buildListWhere($query)
	{
		$app = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params = $app->getParams();

		$where = array();

		$where[] = 'x.published > -1';

		$acl = RedeventUserAcl::getInstance();

		if (!$acl->superuser())
		{
			$xrefs = $acl->getCanEditXrefs();
			$xrefs = @array_merge($acl->getXrefsCanViewAttendees(), $xrefs);
			$xrefs = @array_unique($xrefs);

			if ($xrefs && count($xrefs))
			{
				$where[] = ' x.id IN (' . implode(",", $xrefs) . ')';
			}
			else
			{
				$where[] = '0';
			}
		}

		if ($params->get('showopendates', 1) == 0)
		{
			$where[] = ' x.dates IS NOT NULL AND x.dates > 0 ';
		}

		if ($params->get('shownonbookable', 1) == 0)
		{
			$where[] = ' a.registra > 0 ';
		}

		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		* for the filter onto the WHERE clause of the item query.
		*/
		if ($params->get('filter_text'))
		{
			$filter = $app->input->getString('filter', '', 'request');
			$filter_type = $app->input->getWord('filter_type', '', 'request');

			if ($filter)
			{
				// Clean filter variables
				$filter = JString::strtolower($filter);
				$filter = $this->_db->Quote('%' . $this->_db->escape($filter, true) . '%', false);
				$filter_type = JString::strtolower($filter_type);

				switch ($filter_type)
				{
					case 'title':
						$where[] = ' LOWER( a.title ) LIKE ' . $filter;
						break;

					case 'venue':
						$where[] = ' LOWER( l.venue ) LIKE ' . $filter;
						break;

					case 'city':
						$where[] = ' LOWER( l.city ) LIKE ' . $filter;
						break;

					case 'type':
						$where[] = ' LOWER( c.name ) LIKE ' . $filter;
						break;

					default:
						$filterOr = array(
							' LOWER( a.title ) LIKE ' . $filter,
							' LOWER( x.title ) LIKE ' . $filter,
							' LOWER( l.venue ) LIKE ' . $filter,
							' LOWER( l.city ) LIKE ' . $filter,
							' LOWER( c.name ) LIKE ' . $filter,
						);
						$where[] = '(' . implode(' OR ', $filterOr) . ')';
				}
			}
		}

		if ($app->input->getInt('filter_event'))
		{
			$where[] = ' a.id = ' . $app->input->getInt('filter_event');
		}

		$query->where(implode(' AND ', $where));

		return $query;
	}

	/**
	 * Get events as options
	 *
	 * @return mixed
	 */
	public function getEventsOptions()
	{
		$options = array();
		$model = RModel::getAdminInstance('Events', array('ignore_request' => true), 'com_redevent');
		$model->setState('list.ordering', 'obj.title');
		$model->setState('list.direction', 'asc');
		$model->setState('list.limit', 0);
		$model->setState('filter.published', 1);
		$model->setState('filter.acl', true);

		$rows = $model->getItems();

		if ($rows)
		{
			foreach ($rows as $row)
			{
				$options[] = JHtml::_('select.option', $row->id, $row->title);
			}
		}

		return $options;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination))
		{
			$this->pagination = new RedeventAjaxPagination(
				$this->getTotal(), $this->getState('list.start'), $this->getState('list.limit')
			);
		}

		return $this->pagination;
	}
}
