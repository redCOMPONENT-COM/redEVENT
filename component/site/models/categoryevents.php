<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Model Category events
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventModelCategoryevents extends RedeventModelBaseeventlist
{
	/**
	 * category data array
	 *
	 * @var array
	 */
	protected $category = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$app = JFactory::getApplication();

		$id = $app->input->getInt('id');
		$this->setId((int) $id);

		// For the toggles
		$this->setState('filter_category', $id);

		$params    = $app->getParams('com_redevent');

		if ($params->exists('results_type'))
		{
			$results_type = $params->get('results_type', $params->get('default_categoryevents_results_type', 1));
		}
		else
		{
			$results_type = $params->get('default_categoryevents_results_type', 1);
		}

		$this->setState('results_type', $results_type);

		// If searching for events
		if ($results_type == 0)
		{
			// Get the filter request variables
			$this->setState('filter_order',     $app->input->getCmd('filter_order', 'a.title'));
			$this->setState('filter_order_Dir', strtoupper($app->input->getCmd('filter_order_Dir', 'ASC')) == 'DESC' ? 'DESC' : 'ASC');
		}
	}

	/**
	 * Method to set the category id
	 *
	 * @param   int  $id  category ID number
	 *
	 * @return void
	 *
	 * @access	public
	 */
	public function setId($id)
	{
		// Set new category ID and wipe data
		$this->_id			= $id;
		$this->data		= null;
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

		$category = $this->getItem();
		$query->where(' a.published <> 0 ');

		$query->where('(c.id = ' . $this->_db->Quote($category->id)
		. ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))');

		return $query;
	}

	/**
	 * Method to get the Category
	 *
	 * @return integer
	 */
	public function getItem()
	{
		if (!$this->category)
		{
			$user = JFactory::getUser();
			$gids = $user->getAuthorisedViewLevels();
			$gids = implode(',', $gids);

			$db      = $this->_db;
			$query = $db->getQuery(true);

			$query->select('c.*, asset.name AS asset_name');
			$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug');
			$query->from('#__redevent_categories AS c');
			$query->join('LEFT', '#__assets AS asset ON asset.id = c.asset_id');
			$query->where('c.id = ' . $this->_id);
			$query->where('c.access IN (' . $gids . ')');

			$db->setQuery($query);
			$this->category = $db->loadObject();

			if (!$this->category)
			{
				JError::raiseError(403, JText::_('COM_REDEVENT_ACCESS_NOT_ALLOWED'));
			}

			$helper = new RedeventHelperAttachment;
			$this->category->attachments = $helper->getAttachments('category' . $this->category->id, $user->getAuthorisedViewLevels());
		}

		return $this->category;
	}

	/**
	 * override to take into account search type
	 *
	 * @see RedeventModelBaseeventlist::getData()
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
	 * Build the query
	 *
	 * @return string
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
