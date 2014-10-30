<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Textsnippet Table class
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventTableVenue extends RTable
{
	/**
	 * The name of the table with category
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableName = 'redevent_venues';

	/**
	 * The primary key of the table
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableKey = 'id';

	/**
	 * Field name to publish/unpublish table registers. Ex: state
	 *
	 * @var  string
	 */
	protected $_tableFieldState = 'published';

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
		// Not typed in a venue name
		if (!trim($this->venue))
		{
			$this->setError(JText::_('COM_REDEVENT_ADD_VENUE'));

			return false;
		}

		$alias = JFilterOutput::stringURLSafe($this->venue);

		if (empty($this->alias) || $this->alias === $alias)
		{
			$this->alias = $alias;
		}

		if ($this->map && !($this->latitude || $this->longitude))
		{
			if ((!trim($this->street)) || (!trim($this->plz)) || (!trim($this->city)) || (!trim($this->country)))
			{
				$this->setError(JText::_('COM_REDEVENT_ADD_ADDRESS'));

				return false;
			}
		}

		return true;
	}

	/**
	 * Sets categories of venue
	 *
	 * @param   array  $catids  category ids
	 *
	 * @return boolean true on success
	 */
	public function setCategories($catids = array())
	{
		if (!$this->id)
		{
			$this->setError('COM_REDEVENT_VENUE_TABLE_NOT_INITIALIZED');

			return false;
		}

		// Update the event category xref
		// First, delete current rows for this event
		$query = ' DELETE FROM #__redevent_venue_category_xref WHERE venue_id = ' . $this->_db->Quote($this->id);
		$this->_db->setQuery($query);

		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Insert new ref
		foreach ((array) $catids as $cat_id)
		{
			$query = ' INSERT INTO #__redevent_venue_category_xref (venue_id, category_id) VALUES ('
				. $this->_db->Quote($this->id) . ', ' . $this->_db->Quote($cat_id)
				. ')';
			$this->_db->setQuery($query);

			if (!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
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

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		// Check if there are events assigned to these categories
		if (!$this->haveNoSessions($pk))
		{
			$this->setError('COM_REDEVENT_VENUE_DELETE_ERROR_HAS_SESSIONS');

			return false;
		}

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

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->delete('#__redevent_venue_category_xref');
		$query->where('venue_id IN (' . $pk . ')');

		$db->setQuery($query);
		$db->execute();

		// For finder plugins
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('finder');

		foreach ($pk as $row_id)
		{
			$obj = new stdclass;
			$obj->id = $row_id;

			// Trigger the onFinderAfterDelete event.
			$dispatcher->trigger('onFinderAfterDelete', array('com_redevent.venue', $obj));
		}

		return parent::afterDelete($pk);
	}

	/**
	 * Check that specified categogries have no events assigned
	 *
	 * @param   array  $quotedIds  quoted ids
	 *
	 * @return bool
	 */
	private function haveNoSessions($quotedIds)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('COUNT(*)');
		$query->from('#__redevent_event_venue_xref');
		$query->where('venueid IN (' . $quotedIds . ')');

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res ? false : true;
	}


}
