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
class RedeventModelOrganizations extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_organizations';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'organizations_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  Configs
	 *
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'name', 'o.name',
				'id', 'obj.id',
				'b2b_attendee_notification_mailflow', 'obj.b2b_attendee_notification_mailflow'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string       A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id	.= ':' . $this->getState('filter.b2b_attendee_notification_mailflow');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  object  Query object
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->_db;
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'obj.*, o.name'));
		$query->from($db->qn('#__redevent_organizations', 'obj'));
		$query->join('INNER ', $db->qn('#__redmember_organization') . ' AS o ON o.id = obj.organization_id');

		// Filter: like / search
		$search = $this->getState('filter.search', '');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('obj.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('o.name LIKE ' . $search);
			}
		}

		$filter = $this->getState('filter.b2b_attendee_notification_mailflow', '');

		if (is_numeric($filter))
		{
			$query->where($db->qn('b2b_attendee_notification_mailflow') . ' = ' . $filter);
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'o.name')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Sync organizations from redmember
	 *
	 * @return boolean
	 */
	public function sync()
	{
		$missing = $this->getMissingRedmemberOrganizationsId();

		if (!$missing)
		{
			return true;
		}

		$table = RTable::getAdminInstance('Organization');

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

		$query->select('rm.id');
		$query->from('#__redmember_organization AS rm');
		$query->join('LEFT', '#__redevent_organizations AS o ON o.organization_id = rm.id');
		$query->where('o.id IS NULL');

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}
}
