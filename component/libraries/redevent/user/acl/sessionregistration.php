<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Acl session registration helper
 *
 * @since  3.0
 */
class RedeventUserAclSessionregistration
{
	/**
	 * @var int
	 */
	private $sessionId;

	/**
	 * @var RedeventUserAcl
	 */
	private $acl;

	/**
	 * @var JDatabaseDriver
	 */
	private $db;

	/**
	 * constructor
	 *
	 * @param   int              $sessionId  session id
	 * @param   RedeventUserAcl  $acl        acl instance
	 * @param   array            $options    options array
	 */
	public function __construct($sessionId, $acl, $options = array())
	{
		$this->sessionId = (int) $sessionId;
		$this->acl = $acl;

		$this->db = !empty($options['db']) ? $options['db'] : JFactory::getDbo();
	}

	/**
	 * get ids of user allowed to receive notifications for a session
	 *
	 * @return array
	 */
	public function getRecipients()
	{
		// Get all users globally allowed to receive notifications
		$allowedGlobal = $this->acl->getAllowedUsers('re.receiveregistrations');

		if (!$allowedGlobal)
		{
			return false;
		}

		if (!$allowedByCategory = $this->getAllowedToManageEventsByCategories())
		{
			return false;
		}

		if (!$allowedByVenue = $this->getAllowedToManageEventsByVenue())
		{
			return false;
		}

		// Intersect to find allowed users
		$allowed = array_intersect($allowedGlobal, $allowedByCategory, $allowedByVenue);

		return $allowed;
	}

	/**
	 * Get users allowed to managed events by categories
	 *
	 * @return array
	 */
	private function getAllowedToManageEventsByCategories()
	{
		$allowedByCategory = array();
		$db = $this->db;

		// Get categories asset names
		$query = $db->getQuery(true)
			->select('a.name')
			->from('#__redevent_event_venue_xref AS x')
			->innerJoin('#__redevent_events AS e ON e.id = x.eventid')
			->innerJoin('#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id')
			->innerJoin('#__redevent_categories AS c ON xcat.category_id = c.id')
			->innerJoin('#__assets AS a ON c.asset_id = a.id')
			->where('x.id = ' . $this->sessionId);

		$db->setQuery($query);
		$categoriesAssets = $db->loadColumn() ?: array();

		foreach ($categoriesAssets as $asset)
		{
			if ($res = $this->acl->getAllowedUsers('re.manageevents', $asset))
			{
				$allowedByCategory = array_merge($allowedByCategory, $res);
			}
		}

		return $allowedByCategory;
	}

	/**
	 * Get users allowed to managed events by venue
	 *
	 * @return array
	 */
	private function getAllowedToManageEventsByVenue()
	{
		$allowedByVenue = array();
		$db = $this->db;

		// Get venue asset name
		$query = $db->getQuery(true)
			->select('a.name')
			->from('#__redevent_event_venue_xref AS x')
			->innerJoin('#__redevent_venues AS v ON v.id = x.venueid')
			->innerJoin('#__assets AS a ON a.id = v.asset_id')
			->where('x.id = ' . $this->sessionId);

		$db->setQuery($query);
		$venueAsset = $db->loadResult();

		$allowedByVenue = array();

		if ($res = $this->acl->getAllowedUsers('re.manageevents', $venueAsset))
		{
			$allowedByVenue = $res;
		}

		// Get venue categories asset names
		$query = $db->getQuery(true)
			->select('a.name')
			->from('#__redevent_event_venue_xref AS x')
			->innerJoin('#__redevent_venues AS v ON v.id = x.venueid')
			->innerJoin('#__redevent_venue_category_xref AS xvcat ON xvcat.venue_id = v.id')
			->innerJoin('#__redevent_venues_categories AS vcat ON vcat.id = xvcat.category_id')
			->join('INNER', '#__assets AS a ON a.id = vcat.asset_id')
			->where('x.id = ' . $this->sessionId);

		$db->setQuery($query);
		$venueCategoriesAssets = $db->loadColumn() ?: array();

		foreach ($venueCategoriesAssets as $asset)
		{
			if ($res = $this->acl->getAllowedUsers('re.manageevents', $asset))
			{
				$allowedByVenue = array_merge($allowedByVenue, $res);
			}
		}

		return $allowedByVenue;
	}
}
