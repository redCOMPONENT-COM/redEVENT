<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Role
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedEventModelRole extends RModelAdmin
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

		if ($pk > 0)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('r.*');
			$query->select('rr.usertype, rr.fields');
			$query->from('#__redevent_roles AS r');
			$query->join('LEFT', '#__redevent_roles_redmember AS rr ON rr.role_id = r.id');
			$query->where('r.id = ' . $pk);

			$db->setQuery($query);
			$item = $db->loadObject();

			// Check for a table object error.
			if ($item === false && $db->getError())
			{
				$this->setError($db->getError());

				return false;
			}
		}
		else
		{
			$table = $this->getTable();

			// Convert to the JObject before adding other data.
			$properties = $table->getProperties(1);
			$item = JArrayHelper::toObject($properties, 'JObject');
			$item->usertype = null;
			$item->fields = null;
		}

		if (property_exists($item, 'params'))
		{
			$registry = new JRegistry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		return $item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{
		if (!parent::save($data))
		{
			return false;
		}

		$table = RTable::getAdminInstance('Roleredmember');
		$table->load(array('role_id' => $this->getState($this->getName() . '.id')));

		$table->set('role_id', $this->getState($this->getName() . '.id'));
		$table->set('usertype', $data['usertype']);
		$table->set('fields', $data['fields']);

		if (!$table->store())
		{
			$this->setError('Error saving redmember fields');

			return false;
		}

		return true;
	}
}
