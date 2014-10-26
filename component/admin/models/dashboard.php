<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Home Model
 *
 * @package  Redevent
 * @since    0.9
 */
class RedeventModelDashboard extends RModelAdmin
{
	/**
	 * Get the current version
	 *
	 * @return  string  The version
	 *
	 * @since   3.0
	 */
	public function getVersion()
	{
		$xmlfile = JPATH_SITE . '/administrator/components/com_redevent/redevent.xml';
		$version = JText::_('COM_redevent_FILE_NOT_FOUND');

		if (file_exists($xmlfile))
		{
			$data = JApplicationHelper::parseXMLInstallFile($xmlfile);
			$version = $data['version'];
		}

		return $version;
	}

	/**
	 * Method to get events stats
	 *
	 * @return  array total/published/unpublished/archived
	 */
	public function getEventsStats()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(*) AS total');
		$query->select('SUM(IF(published = 1, 1, 0)) AS published');
		$query->select('SUM(IF(published = 0, 1, 0)) AS unpublished');
		$query->select('SUM(IF(published = -1, 1, 0)) AS archived');
		$query->from('#__redevent_events');
		$query->group('id');

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}

	/**
	 * Method to get Venues stats
	 *
	 * @return  array total/published/unpublished
	 */
	public function getVenuesStats()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(*) AS total');
		$query->select('SUM(IF(published = 1, 1, 0)) AS published');
		$query->select('SUM(IF(published = 0, 1, 0)) AS unpublished');
		$query->from('#__redevent_venues');
		$query->group('id');

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}

	/**
	 * Method to get categories stats
	 *
	 * @return  array total/published/unpublished
	 */
	public function getCategoriesStats()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(*) AS total');
		$query->select('SUM(IF(published = 1, 1, 0)) AS published');
		$query->select('SUM(IF(published = 0, 1, 0)) AS unpublished');
		$query->from('#__redevent_categories');
		$query->group('id');

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}
}
