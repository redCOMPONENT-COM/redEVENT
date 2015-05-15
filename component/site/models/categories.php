<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Model Categories
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventModelCategories extends RModelList
{
	/**
	 * category to use as a base for queries
	 *
	 * @var unknown_type
	 */
	protected $parent = null;

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
		$params = $app->getParams();

		if ($params->get('parentcategory', 0))
		{
			$this->setParent($params->get('parentcategory', 0));
		}

		$this->setState('filter.language', $app->getLanguageFilter());
	}

	/**
	 * set the parent category id
	 *
	 * @param   int  $id  parent category id
	 *
	 * @return boolean
	 */
	public function setParent($id)
	{
		$query = $this->_db->getQuery(true)
			->select('id, lft, rgt')
			->from('#__redevent_categories')
			->where('id = ' . $this->_db->Quote((int) $id));

		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();

		if (!$res)
		{
			throw new RuntimeException(JText::_('COM_REDEVENT_PARENT_CATEGORY_NOT_FOUND'));
		}

		$this->parent = $res;

		return true;
	}

	/**
	 * Method to get the Categories
	 *
	 * @access public
	 * @return array
	 */
	public function getItems()
	{
		if (!$items = parent::getItems())
		{
			return $items;
		}

		foreach ($items as &$item)
		{
			$item->linktext = JText::_('COM_REDEVENT_SHOW_EVENTS');
			$item->linktarget = RedeventHelperRoute::getCategoryEventsRoute($item->slug);
		}

		return $items;
	}

	/**
	 * Method to load the Categories
	 *
	 * @return array
	 */
	protected function _getListQuery()
	{
		// Initialize some vars
		$mainframe = JFactory::getApplication();
		$params   = $mainframe->getParams('com_redevent');

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		// Check archive task and ensure that only categories get selected if they contain a published/archived event
		$task 	= JRequest::getVar('task', '', '', 'string');

		$count = 'CASE WHEN x.published = 1 THEN 1 ELSE 0 END';

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('c.*, SUM(' . $count . ') AS assignedevents');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug');
		$query->from('#__redevent_categories AS c');
		$query->join('LEFT', '#__redevent_categories AS child ON child.lft BETWEEN c.lft AND c.rgt');
		$query->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.category_id = child.id');
		$query->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = xcat.event_id');
		$query->join('LEFT', '#__redevent_events AS e ON x.eventid = e.id');
		$query->where('child.published = 1');
		$query->where('child.access IN (' . $gids . ')');

		if ($params->get('display_all_categories', 0) == 0)
		{
			$query->where(' x.published = 1');
		}

		if ($this->parent)
		{
			$query->where('c.parent_id = ' . $this->_db->Quote($this->parent->id));
		}

		$query->where('(c.access IN (' . $gids . ')) ');

		if ($this->getState('filter.language'))
		{
			$query->where('(e.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR e.language IS NULL)');
			$query->where('(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)');
		}

		$query->group('c.id ');
		$query->order('c.ordering ASC');

		return $query;
	}
}
