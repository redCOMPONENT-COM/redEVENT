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
	 * Associated Session data
	 *
	 * @var object
	 */
	private $beforeDeleteSessions;

	/**
	 * @var  integer
	 */
	public $created_by;

	/**
	 * @var  string
	 */
	public $created;

	/**
	 * @var  integer
	 */
	public $modified_by;

	/**
	 * @var  string
	 */
	public $modified;

	/**
	 * @var  integer
	 */
	public $checked_out;

	/**
	 * @var  string
	 */
	public $checked_out_time;

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
		if (!$this->eventid)
		{
			$this->setError(JText::_('COM_REDEVENT_SESSION_EVENTID_IS_REQUIRED'));

			return false;
		}

		// Allow credit to be null
		if ($this->course_credit === '')
		{
			$this->course_credit = null;
		}

		if ($this->times === '')
		{
			$this->times = null;
		}

		if ($this->endtimes === '')
		{
			$this->endtimes = null;
		}

		$alias = JFilterOutput::stringURLSafe($this->title);

		if (empty($this->alias) && $alias)
		{
			$this->alias = $alias;
		}

		return true;
	}

	/**
	 * Set prices
	 *
	 * @param   array  $prices  prices
	 *
	 * @return bool
	 */
	public function setPrices($prices = array())
	{
		// First remove current rows
		$query = $this->_db->getQuery(true);

		$query->delete('#__redevent_sessions_pricegroups')
			->where('xref = ' . $this->_db->Quote($this->id));

		$this->_db->setQuery($query);

		if (!$this->_db->execute())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Then recreate them if any
		foreach ((array) $prices as $k => $price)
		{
			if (!isset($price->pricegroup_id) || !isset($price->price))
			{
				continue;
			}

			$new = RTable::getAdminInstance('Sessionpricegroup', array(), 'com_redevent');
			$new->set('xref',          $this->id);
			$new->set('pricegroup_id', $price->pricegroup_id);
			$new->set('price',         $price->price);
			$new->set('currency',      $price->currency);

			if (!($new->check() && $new->store()))
			{
				$this->setError($new->getError());

				return false;
			}
		}
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
}
