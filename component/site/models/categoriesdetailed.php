<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Model Categories detailed
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventModelCategoriesdetailed extends RedeventModelBasesessionlist
{
	/**
	 * Top category for the view.
	 *
	 * @var object
	 */
	protected $parent = null;

	/**
	 * Categories data array
	 *
	 * @var integer
	 */
	protected $categories = null;

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
		$params = $mainframe->getParams('com_redevent');

		if ($params->get('parentcategory', 0))
		{
			$this->setParent($params->get('parentcategory', 0));
		}
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
		$parent = RedeventEntityCategory::load($id);

		if (!$parent->isValid())
		{
			JError::raiseWarning(0, JText::_('COM_REDEVENT_PARENT_CATEGORY_NOT_FOUND'));
		}
		else
		{
			$this->parent = $parent;
			$this->categories = null;
		}

		return true;
	}

	/**
	 * Method to get the Categories
	 *
	 * @access public
	 * @return array
	 */
	public function &getData( )
	{
		$mainframe = JFactory::getApplication();

		$params 	= $mainframe->getParams();
		$elsettings = RedeventHelper::config();

		// Lets load the content if it doesn't already exist
		if (empty($this->categories))
		{
			$query = $this->_buildQuery();
			$this->categories = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

			$count = count($this->categories);

			for ($i = 0; $i < $count; $i++)
			{
				$category = $this->categories[$i];
				$category->events = $this->_getEvents($category);
				$category->assignedevents = $this->_getEventsTotal($category);

				// Generate description
				if (empty ($category->description))
				{
					$category->description = JText::_('COM_REDEVENT_NO_DESCRIPTION');
				}
				else
				{
					// Execute plugins
					$category->description = JHTML::_('content.prepare', $category->description);
				}

				// Create target link
				$category->linktext = JText::_('COM_REDEVENT_SHOW_EVENTS');
				$category->linktarget = JRoute::_(RedeventHelperRoute::getCategoryEventsRoute($category->slug));
			}
		}

		return $this->categories;
	}

	/**
	 * Method to get the Categories events
	 *
	 * @param   object  &$category  a category object
	 *
	 * @access public
	 * @return array
	 */
	public function &_getEvents(&$category)
	{
		$mainframe = JFactory::getApplication();

		$params 	= $mainframe->getParams('com_redevent');

		// Lets load the content
		$query = $this->_buildDataQuery($category);
		$this->data = $this->_getList($query, 0, $params->get('detcat_nr'));
		$this->data = $this->_categories($this->data);
		$this->data = $this->_getPlacesLeft($this->data);
		$this->data = $this->_getPrices($this->data);

		return $this->data;
	}

	/**
	 * Method to get the Categories events total
	 *
	 * @param   object  &$category  a category object
	 *
	 * @access public
	 * @return array
	 */
	public function _getEventsTotal(&$category)
	{
		// Lets load the content
		$query = $this->_buildDataQuery($category);

		return $this->_getListCount($query, 0, 0);
	}

	/**
	 * Method get the event query
	 *
	 * @param   object  &$category  a category object
	 *
	 * @access protected
	 * @return array
	 */
	protected function _buildDataQuery(&$category)
	{
		$user		= JFactory::getUser();

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$customs = $this->getCustomFields();
		$xcustoms = $this->getXrefCustomFields();

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Get Events from Category
		$query->select('a.id, a.datimage, x.venueid, x.dates, x.enddates, x.times, x.title as session_title, x.featured');
		$query->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title');
		$query->select('x.endtimes, x.id AS xref, x.registrationend, x.id AS xref, x.maxattendees, x.maxwaitinglist, x.icaldetails, x.icalvenue');
		$query->select('a.title, a.registra, l.venue, l.city, l.state, l.url, c.name AS catname, c.id AS catid, a.summary, x.course_credit');
		$query->select('l.street, l.country');
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

		$query->from('#__redevent_events AS a');
		$query->join('INNER', '#__redevent_event_venue_xref AS x on x.eventid = a.id');
		$query->join('INNER', '#__redevent_venues AS l ON l.id = x.venueid');
		$query->join('LEFT', '#__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id');
		$query->join('LEFT', '#__redevent_venues_categories AS vc ON xvcat.category_id = vc.id');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
		$query->join('INNER', '#__redevent_categories AS c ON c.id = xcat.category_id');

		$query->where('c.lft BETWEEN ' . $db->Quote($category->lft) . ' AND ' . $db->Quote($category->rgt));
		$query->where('(l.access IN (' . $gids . ')) ');
		$query->where('(c.access IN (' . $gids . ')) ');
		$query->where('(vc.id IS NULL OR vc.access IN (' . $gids . ')) ');

		if ($this->getState('filter.language'))
		{
			$query->where('(a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR a.language IS NULL)');
			$query->where('(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)');
		}

		$query->group('x.id');
		$query->order('x.dates, x.times');

		return $query;
	}

	/**
	 * Method get the categories query
	 *
	 * @access protected
	 * @return array
	 */
	protected function _buildQuery()
	{
		$mainframe = JFactory::getApplication();
		$params    = $mainframe->getParams('com_redevent');
		$user      = JFactory::getUser();

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('c.*');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug');

		$query->from('#__redevent_categories AS c');

		$query->where('c.published = 1');
		$query->where('(c.access IN (' . $gids . '))');

		if ($this->parent)
		{
			$query->where('c.parent_id = ' . $db->Quote($this->parent->id));
		}

		// Optionally only get categories having events
		if (!$params->get('display_all_categories', 1))
		{
			$query->join('INNER', '#__redevent_categories AS child ON child.lft BETWEEN c.lft AND c.rgt ');
			$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.category_id = child.id ');
			$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = xcat.event_id ');
			$query->where('child.published = 1 ');
			$query->where('child.access IN (' . $gids . ')');
		}

		if ($this->getState('filter.language'))
		{
			$query->where('(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)');
		}

		$query->group('c.id ');
		$query->group('c.ordering ASC ');

		return $query;
	}
}
