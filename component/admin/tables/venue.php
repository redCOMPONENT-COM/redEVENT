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
class RedeventTableVenue extends RedeventTable
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
	 * Categories
	 *
	 * @var array
	 */
	public $categories;

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

		if (!$this->categories)
		{
			$this->setError(JText::_('COM_REDEVENT_TABLE_VENUE_CHECK_CATEGORIES_REQUIRED'));

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
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (isset($src['categories']) && is_array($src['categories']))
		{
			$categories = $src['categories'];
			JArrayHelper::toInteger($categories);
			$this->categories = $categories;
		}

		return parent::bind($src, $ignore);
	}

	/**
	 * Called before store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeStore($updateNulls = false)
	{
		$user = JFactory::getUser();

		// Get the current time in the database format.
		$time = JFactory::getDate()->toSql();

		$this->modified = $time;
		$this->modified_by = $user->get('id');

		if (!$this->id)
		{
			$params = JComponentHelper::getParams('com_redevent');

			// Get IP, time and user id
			$this->created = $time;
			$this->created_by = $user->get('id');
			$this->author_ip = $params->get('storeip', '1') ? getenv('REMOTE_ADDR') : 'DISABLED';
		}

		return parent::beforeStore($updateNulls);
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
		$this->setCategories($this->categories);

		return parent::afterStore($updateNulls);
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
