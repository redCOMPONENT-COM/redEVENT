<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent events table class
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventTableSession extends RedeventTable
{
	/**
	 * The name of the table with category
	 *
	 * @var string
	 */
	protected $_tableName = 'redevent_event_venue_xref';

	/**
	 * The primary key of the table
	 *
	 * @var string
	 */
	protected $_tableKey = 'id';

	/**
	 * Field name to publish/unpublish table registers. Ex: state
	 *
	 * @var  string
	 */
	protected $_tableFieldState = 'published';

	/**
	 * Field name to keep created date (created_date)
	 *
	 * @var  string
	 */
	protected $_tableFieldCreatedDate = 'created';

	/**
	 * Field name to keep latest modified user (modified_date)
	 *
	 * @var  string
	 */
	protected $_tableFieldModifiedDate = 'modified';

	/**
	 * Associated Session data
	 *
	 * @var object
	 */
	private $beforeDeleteSessions;

	/**
	 * @var  array
	 */
	public $event;

	/**
	 * @var array
	 * @since 3.2.3
	 */
	public $prices;

	/**
	 * @var array
	 * @since 3.2.3
	 */
	public $roles;

	/**
	 * @var array
	 * @since 3.2.3
	 */
	public $new_prices;

	/**
	 * @var array
	 * @since 3.2.3
	 */
	public $new_roles;

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		if (!$this->eventid && !$this->event)
		{
			$this->setError(JText::_('COM_REDEVENT_SESSION_EVENTID_IS_REQUIRED'));

			return false;
		}

		// Allow credit to be null
		if ($this->course_credit === '')
		{
			$this->course_credit = null;
		}

		$alias = JFilterOutput::stringURLSafe($this->title);

		if (empty($this->alias) && $alias)
		{
			$this->alias = $alias;
		}

		return true;
	}

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link	http://docs.joomla.org/JTable/store
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		if (!$this->language)
		{
			// Make sure the language is same as event
			$db = $this->_db;
			$query = $db->getQuery(true);

			$query->select('language');
			$query->from('#__redevent_events');
			$query->where('id = ' . $this->eventid);

			$db->setQuery($query);
			$res = $db->loadResult();

			$this->language = $res;
		}

		return parent::store($updateNulls);
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the JTable instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 */
	public function load($keys = null, $reset = true)
	{
		if (parent::load($keys, $reset))
		{
			if (!$this->loadEvent())
			{
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * Load the associated event
	 *
	 * @return  boolean
	 */
	private function loadEvent()
	{
		if (!empty($this->eventid))
		{
			$table = RTable::getAdminInstance('Event', array(), 'com_redevent');

			if (!$table->load($this->eventid))
			{
				return false;
			}

			$this->event = $table->getProperties();
		}

		return true;
	}

	/**
	 * Called before delete().
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeDelete($pk = null)
	{
		$pk = $this->sanitizePk($pk);

		if (!$this->checkNoAttendees($pk))
		{
			return false;
		}

		// Save sessions data for postDelete
		$query = $this->_db->getQuery(true)
			->select('*')
			->from('#__redevent_event_venue_xref')
			->where('id IN (' . $pk . ')');
		$this->_db->setQuery($query);
		$this->beforeDeleteSessions = $this->_db->loadObjectList();

		return parent::beforeDelete($pk);
	}

	/**
	 * Called after delete().
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterDelete($pk = null)
	{
		$pk = $this->sanitizePk($pk);

		$this->deleteRoles($pk);
		$this->deletePricegroups($pk);
		$this->deleteRepeats($pk);

		// Trigger event
		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();

		foreach ($this->beforeDeleteSessions as $deleted)
		{
			$dispatcher->trigger('onAfterSessionDelete', array($deleted->session_code));
		}

		return parent::afterDelete($pk);
	}

	/**
	 * Check that there are no attendees for sessions
	 *
	 * @param   string  $pk  imploded ids
	 *
	 * @return bool
	 */
	private function checkNoAttendees($pk)
	{
		$query = $this->_db->getQuery(true);

		$query->select('COUNT(*)')
			->from('#__redevent_register')
			->where('xref IN (' . $pk . ')');

		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();

		if ($res)
		{
			$this->setError(JText::_('COM_REDEVENT_EVENT_DATE_HAS_ATTENDEES'));

			return false;
		}

		return true;
	}

	/**
	 * Delete session(s) roles
	 *
	 * @param   string  $pk  imploded session ids
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function deleteRoles($pk)
	{
		// Get id of rows
		$query = $this->_db->getQuery(true);

		$query->select('id')
			->from('#__redevent_sessions_roles')
			->where('xref in (' . $pk . ')');

		$this->_db->setQuery($query);
		$ids = $this->_db->loadColumn();

		if (!$ids)
		{
			return;
		}

		$table = RTable::getAdminInstance('Sessionrole');

		if (!$table->delete($ids))
		{
			throw new Exception($table->getError());
		}
	}

	/**
	 * Delete session(s) roles
	 *
	 * @param   string  $pk  imploded session ids
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function deletePricegroups($pk)
	{
		// Get id of rows
		$query = $this->_db->getQuery(true);

		$query->select('id')
			->from('#__redevent_sessions_pricegroups')
			->where('xref in (' . $pk . ')');

		$this->_db->setQuery($query);
		$ids = $this->_db->loadColumn();

		if (!$ids)
		{
			return;
		}

		$table = RTable::getAdminInstance('Sessionpricegroup');

		if (!$table->delete($ids))
		{
			throw new Exception($table->getError());
		}
	}

	/**
	 * Delete session(s) roles
	 *
	 * @param   string  $pk  imploded session ids
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function deleteRepeats($pk)
	{
		// Get id of rows
		$query = $this->_db->getQuery(true);

		$query->select('id')
			->from('#__redevent_repeats')
			->where('xref_id in (' . $pk . ')');

		$this->_db->setQuery($query);
		$ids = $this->_db->loadColumn();

		if (!$ids)
		{
			return;
		}

		$table = RTable::getAdminInstance('Repeat');

		if (!$table->delete($ids))
		{
			throw new Exception($table->getError());
		}
	}

	/**
	 * Called after load().
	 *
	 * @param   mixed   $keys    An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean $reset   True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 */
	protected function afterLoad($keys = null, $reset = true)
	{
		$this->loadPrices();

		$this->loadRoles();

		return parent::afterLoad($keys, $reset);
	}

	/**
	 * Load associated prices
	 *
	 * @return bool
	 *
	 * @since 3.2.3
	 */
	private function loadPrices()
	{
		if (is_null($this->prices))
		{
			$this->prices = [];

			if (empty($this->id))
			{
				return true;
			}

			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->select('spg.*')
				->from($db->qn('#__redevent_sessions_pricegroups', 'spg'))
				->where($db->qn('spg.xref') . ' = ' . (int) $this->id);
			$db->setQuery($query);

			$this->prices = $db->loadObjectList() ?: [];
		}

		return true;
	}

	/**
	 * Load associated roles
	 *
	 * @return bool
	 *
	 * @since 3.2.3
	 */
	private function loadRoles()
	{
		$this->roles = [];

		if (empty($this->id))
		{
			return true;
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('r.*')
			->from($db->qn('#__redevent_sessions_roles', 'r'))
			->where($db->qn('r.xref') . ' = ' . (int) $this->id);
		$db->setQuery($query);

		$this->roles = $db->loadObjectList() ?: [];

		return true;
	}

	/**
	 * Called after store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterStore($updateNulls = false)
	{
		$this->savePrices();

		$this->saveRoles();

		return parent::afterStore($updateNulls);
	}

	/**
	 * Save prices
	 *
	 * @return bool
	 *
	 * @since 3.2.3
	 */
	private function savePrices()
	{
		if (empty($this->id))
		{
			return true;
		}

		$this->cleanPrices();

		$newPrices = $this->new_prices;

		// Save them
		if (empty($newPrices['pricegroup']))
		{
			return true;
		}

		foreach ((array) $newPrices['pricegroup'] as $k => $r)
		{
			if (!($newPrices['pricegroup'][$k]))
			{
				continue;
			}

			$new = RTable::getInstance('Sessionpricegroup', 'RedeventTable');
			$new->set('xref', $this->id);
			$new->set('pricegroup_id', $r);
			$new->set('price', $newPrices['price'][$k]);
			$new->set('vatrate', $newPrices['vatrate'][$k]);
			$new->set('sku', $newPrices['sku'][$k]);
			$new->set('currency', $newPrices['currency'][$k]);
			$new->set('active', 1);

			if ($found = $this->findSessionPricegroup($new))
			{
				$new->set('id', $found->id);
			}

			if (!($new->check() && $new->store()))
			{
				$this->setError($new->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Save roles
	 *
	 * @return bool
	 *
	 * @since 3.2.3
	 */
	private function saveRoles()
	{
		if (empty($this->id))
		{
			return true;
		}

		$this->cleanRoles();

		$newRoles = $this->new_roles;

		// Save them
		if (empty($newRoles['rrole']))
		{
			return true;
		}

		// Then recreate them if any
		foreach ((array) $newRoles['rrole'] as $k => $r)
		{
			if (!($newRoles['rrole'][$k] && $newRoles['urole'][$k]))
			{
				continue;
			}

			$new = RTable::getAdminInstance('Sessionrole');
			$new->set('xref', $this->id);
			$new->set('role_id', $r);
			$new->set('user_id', $newRoles['urole'][$k]);

			if (!($new->check() && $new->store()))
			{
				$this->setError($new->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Clean roles from db before saving
	 *
	 * @return void
	 *
	 * @since 3.2.3
	 */
	private function cleanRoles()
	{
		if (empty($this->id))
		{
			return true;
		}

		$query = $this->_db->getQuery(true);

		$query->delete('#__redevent_sessions_roles')
			->where('xref = ' . $this->id);
		$this->_db->setQuery($query);
		$this->_db->execute();
	}

	/**
	 * Clean prices from db before saving
	 *
	 * @return void
	 *
	 * @since 3.2.3
	 */
	private function cleanPrices()
	{
		if (empty($this->id))
		{
			return true;
		}

		// We can only remove delete a price group if there is no attendee associated to it, so first list those
		$query = $this->_db->getQuery(true)
			->select('spg.id')
			->from('#__redevent_sessions_pricegroups AS spg')
			->join('LEFT', '#__redevent_register AS r ON r.	sessionpricegroup_id = spg.id')
			->where('r.id IS NULL')
			->where('spg.xref = ' . $this->id);

		$this->_db->setQuery($query);

		// Them we can safely delete them
		if ($res = $this->_db->loadColumn())
		{
			$query = $this->_db->getQuery(true)
				->delete('#__redevent_sessions_pricegroups')
				->where('id IN (' . implode(",", $res) . ')');

			$this->_db->setQuery($query);
			$this->_db->execute();
		}

		// Disable the one that are remaining
		$query = $this->_db->getQuery(true);

		$query->update('#__redevent_sessions_pricegroups')
			->set('active = 0')
			->where('xref = ' . $this->id);
		$this->_db->setQuery($query);
		$this->_db->execute();

		// Empty cache
		$this->prices = null;
	}

	/**
	 * Check if row is already in table
	 *
	 * @param   RTable  $row  session price group row
	 *
	 * @return bool
	 *
	 * @since 3.2.3
	 */
	private function findSessionPricegroup(RTable $row)
	{
		$this->loadPrices();

		if (empty($this->prices))
		{
			return false;
		}

		foreach ($this->prices as $sessionPriceGroup)
		{
			if ($sessionPriceGroup->pricegroup_id == $row->pricegroup_id
				&& $sessionPriceGroup->price == $row->price
				&& $sessionPriceGroup->vatrate == $row->vatrate
				&& $sessionPriceGroup->sku == $row->sku
				&& $sessionPriceGroup->currency == $row->currency)
			{
				return $sessionPriceGroup;
			}
		}

		return false;
	}
}
