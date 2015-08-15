<?php
/**
 * @package     Redevent.Frontend
 * @subpackage  Plugins
 *
 * @copyright   Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Class plgReSimplistModel
 *
 * @package     Redevent.Frontend
 * @subpackage  Plugins
 * @since       2.5
 */
class plgReSimplistModel extends RedeventModelBasesessionlist
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		// Get the number of events from database
		$limit = 20;
		$limitstart = 0;

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get the filter request variables
		$this->setState('filter_order', 'x.dates');
		$this->setState('filter_order_dir', 'ASC');

		$this->setState('filter', '');
		$this->setState('filter_type', '');

		$this->setState('filter_event', 0);
		$this->setState('filter_category', 0);
		$this->setState('filter_venue', 0);
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
		$user = JFactory::getUser();
		$gid = (int) max($user->getAuthorisedViewLevels());

		$where = array();

		// First thing we need to do is to select only needed events
		if ($this->getState('archived'))
		{
			$where[] = ' x.published = -1 ';
		}
		else
		{
			$where[] = ' x.published = 1 ';
		}
		if ($this->getState('featured'))
		{
			$where[] = ' x.featured = 1 ';
		}

		if ($ev = $this->getState('eventid'))
		{
			$cond = array();
			foreach ($ev as $e)
			{
				$cond[] = ' a.id = ' . $this->_db->Quote($e);
			}
			$where[] = '(' . implode(' OR ', $cond) . ')';
		}

		if ($cat = $this->getState('filter_category'))
		{
			$category = $this->getCategory((int) $cat);
			if ($category)
			{
				$where[] = '(c.id = ' . $this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
			}
		}

		if ($cats = $this->getState('categoryid'))
		{
			$cond = array();
			foreach ($cats as $c)
			{
				$category = $this->getCategory((int) $c);
				if ($category)
				{
					$cond[] = '(c.id = ' . $this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
				}
			}
			$where[] = '(' . implode(' OR ', $cond) . ')';
		}

		if ($cities = $this->getState('city'))
		{
			$city_cond = array();
			foreach ($cities as $c)
			{
				$city_cond[] = ' STRCMP(l.city, ' . $this->_db->Quote($c) . ') = 0 ';
			}
			$where[] = '(' . implode(' OR ', $city_cond) . ')';
		}

		if ($venues = $this->getState('venueid'))
		{
			$cond = array();
			foreach ($venues as $v)
			{
				$cond[] = ' l.id = ' . $this->_db->Quote($v);
			}
			$where[] = '(' . implode(' OR ', $cond) . ')';
		}

		if ($customs = $this->getState('customs'))
		{
			foreach ($customs as $f => $v)
			{
				if ($field = $this->_getCustom($f))
				{
					$prefix = 'a.';
					$where[] = ' a.' . $f . ' = ' . $this->_db->Quote($v);
				}
				else if ($field = $this->_getXrefCustom($f))
				{
					$prefix = 'x.';
					$where[] = ' x.' . $f . ' = ' . $this->_db->Quote($v);
				}
				else
				{
					continue;
				}
				$where[] = $prefix . $f . ' LIKE ' . $this->_db->Quote("%$v%");
			}
		}

		$sstate = $this->getState('type');
		$now = strftime('%Y-%m-%d %H:%M');
		if ($sstate == 'past')
		{
			$where[] = '(x.dates > 0 AND (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) < ' . $this->_db->Quote($now) . ')';
		}
		else if ($sstate == 'future')
		{
			$where[] = '(x.dates = 0 OR (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > ' . $this->_db->Quote($now) . ')';
		}

		return $query->where(implode(' AND ', $where));
	}

	/**
	 * return object if the field is a event custom field
	 *
	 * @param string $name db field name (custom<id>)
	 *
	 * @return mixed object or false if not exists
	 */
	protected function _getCustom($name)
	{
		$fields = $this->getCustomFields();
		foreach ($fields as $f)
		{
			if ('custom' . $f->id == $name)
			{
				return $f;
			}
		}
		return false;
	}

	/**
	 * return object if the field is a session custom field
	 *
	 * @param string $name db field name (custom<id>)
	 *
	 * @return mixed object or false if not exists
	 */
	protected function _getXrefCustom($name)
	{
		$fields = $this->getXrefCustomFields();
		foreach ($fields as $f)
		{
			if ('custom' . $f->id == $name)
			{
				return $f;
			}
		}
		return false;
	}
}
