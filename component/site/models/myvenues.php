<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevents Component my venues Model
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventModelMyvenues extends RModelList
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$mainframe = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params = $mainframe->getParams('com_redevent');

		// Get the number of events from database
		$limit = $mainframe->getUserStateFromRequest('com_redevent.myevents.limit', 'limit', $params->def('display_num', 0), 'int');
		$limitstart_venues = $mainframe->input->get('limitstart', 0, '', 'int');

		$this->setState('list.limit', $limit);
		$this->setState('list.start', $limitstart_venues);
	}

	/**
	 * Method to cache the last query constructed.
	 *
	 * This method ensures that the query is constructed only once for a given state of the model.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object
	 */
	protected function getListQuery()
	{
		$allowed = RedeventUserAcl::getInstance()->getAllowedForEventsVenues();

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('l.id, l.venue, l.city, l.state, l.url, l.published');
		$query->select('CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug');
		$query->from('#__redevent_venues AS l');
		$query->group('l.id');
		$query->order('l.venue ASC');

		if ($allowed && count($allowed))
		{
			$query->where('l.id IN (' . implode(',', $allowed) . ') ');
		}
		else
		{
			$query->where('0');
		}

		return $query;
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
