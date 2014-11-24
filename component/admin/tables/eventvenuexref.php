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
class RedeventTableEventvenuexref extends RTable
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
	 * Delete check
	 *
	 * @param   int  $id  id to delete
	 *
	 * @return bool
	 */
	public function canDelete($id)
	{
		$query = $this->_db->getQuery(true);

		$query->select('COUNT(*)')
			->from('#__redevent_register')
			->where('xref = ' . intval($id));

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
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 */
	public function bind($src, $ignore = array())
	{
		if (!parent::bind($src, $ignore))
		{
			return false;
		}

		$customs = $this->_getCustomFieldsColumns();

		foreach ($customs as $c)
		{
			if (isset($src[$c]))
			{
				$src[$c] = is_array($src[$c]) ? implode("\n", $src[$c]) : $src[$c];
			}
			else
			{
				$src[$c] = '';
			}
		}

		return true;
	}

	/**
	 * get custom fields for table
	 *
	 * @return array
	 */
	protected function _getCustomFieldsColumns()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('CONCAT("custom", id)');
		$query->from('#__redevent_fields');
		$query->where('object_key = ' . $db->Quote('redevent.xref'));

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}

	public function setPrices($prices = array())
	{
		// first remove current rows
		$query = $this->_db->getQuery(true);

		$query->delete('#__redevent_sessions_pricegroups')
			->where('xref = ' . $this->_db->Quote($this->id));

		$this->_db->setQuery($query);

		if (!$this->_db->execute())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// then recreate them if any
		foreach ((array) $prices as $k => $price)
		{
			if (!isset($price->pricegroup_id) || !isset($price->price))
			{
				continue;
			}

			$new = RTable::getInstance('Sessionspricegroups', 'RedeventTable');
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
	 * Return pk for query
	 *
	 * @param $pk
	 *
	 * @return array|string
	 */
	protected function sanitizePk($pk)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Received an array of ids?
		if (is_array($pk))
		{
			// Sanitize input.
			JArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}

		$pk = (is_null($pk)) ? $this->$k : $pk;

		return $pk;
	}
}
