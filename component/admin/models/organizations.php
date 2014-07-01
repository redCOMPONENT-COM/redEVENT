<?php
/**
 * @package    Redevent.Administrator
 *
 * @copyright  redEVENT (C) 2014 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Organizations
 *
 * @package  Redevent.Administrator
 * @since    2.5
 */
class RedeventModelOrganizations extends FOFModel
{
	/**
	 * Builds the SELECT query
	 *
	 * @param   boolean  $overrideLimits  Are we requested to override the set limits?
	 *
	 * @return  JDatabaseQuery
	 */
	public function buildQuery($overrideLimits = false)
	{
		// Initialise variables.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id AS id,' .
				'a.checked_out AS checked_out,' .
				'a.checked_out_time AS checked_out_time,' .
				'o.organization_name AS name'
			)
		);
		$query->from($db->quoteName('#__redevent_organizations') . ' AS a');
		$query->join('INNER ', $db->quoteName('#__redmember_organization') . ' AS o ON o.organization_id = a.organization_id');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

		if (!$overrideLimits)
		{
			$order = $this->getState('filter_order', 'o.organization_name', 'cmd');
			$order = $db->qn($order);

			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			$query->order($order . ' ' . $dir);
		}

		// Call the behaviors
		$this->modelDispatcher->trigger('onAfterBuildQuery', array(&$this, &$query));

		return $query;
	}

	/**
	 * Sync organizations from redmember
	 *
	 * @return bool
	 */
	public function sync()
	{
		$missing = $this->getMissingRedmemberOrganizationsId();

		if (!$missing)
		{
			return true;
		}

		$table = FOFTable::getInstance('Organization', 'RedeventTable');

		foreach ($missing as $mid)
		{
			$table->reset();
			$table->id = null;
			$table->organization_id = $mid;

			if (!($table->check() && $table->store()))
			{
				$this->setError('Error syncing organization');

				return false;
			}
		}

		return true;
	}

	/**
	 * Get id of redmember orgs not in redevent config table
	 *
	 * @return mixed
	 */
	private function getMissingRedmemberOrganizationsId()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('rm.organization_id');
		$query->from('#__redmember_organization AS rm');
		$query->join('LEFT', '#__redevent_organizations AS o ON o.organization_id = rm.organization_id');
		$query->where('o.id IS NULL');

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}
}
