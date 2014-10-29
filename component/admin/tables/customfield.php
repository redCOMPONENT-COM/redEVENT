<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Customfield Table class
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventTableCustomfield extends RTable
{
	/**
	 * The name of the table with category
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableName = 'redevent_fields';

	/**
	 * The primary key of the table
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableKey = 'id';

	/**
	 * object key before saving.
	 * We use it to keep track of destination table
	 *
	 * @var bool
	 */
	private $previousObjectKey = null;

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
		if ($this->checkTagExists())
		{
			$this->setError(JText::_('COM_REDEVENT_ERROR_TAG_ALREADY_EXISTS'));

			return false;
		}

		return parent::check();
	}

	/**
	 * checks whether the tag already exists
	 *
	 * @return mixed boolean false if doesn't exists, tag object if it does
	 */
	private function checkTagExists()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('tag');
		$query->from('#__redevent_fields');
		$query->where('tag = ' . $db->quote($this->tag));

		if ($this->id)
		{
			$query->where('id <> ' . $this->id);
		}

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res ? true : false;
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

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id, object_key');
		$query->from('#__redevent_fields');
		$query->where('id IN (' . $pk . ')');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		foreach ($res as $column)
		{
			if (!$this->dropColumn($column->id, $column->object_key))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Called after load().
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 */
	protected function afterLoad($keys = null, $reset = true)
	{
		$this->previousObjectKey = $this->object_key;

		return parent::afterLoad($keys, $reset);
	}

	/**
	 * Override to create / update associated column in object table
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterStore($updateNulls = false)
	{
		try
		{
			if (!$this->previousObjectKey)
			{
				$this->createColumn();
			}
			elseif ($this->previousObjectKey != $this->object_key)
			{
				$this->dropColumn($this->id, $this->previousObjectKey);
				$this->createColumn();
			}
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return parent::afterStore($updateNulls);
	}

	/**
	 * Create column in object table
	 *
	 * @return bool true on success
	 *
	 * @throws Exception
	 */
	private function createColumn()
	{
		switch ($this->object_key)
		{
			case 'redevent.event':
				$table = '#__redevent_events';
				break;

			case 'redevent.xref':
				$table = '#__redevent_event_venue_xref';
				break;

			default:
				throw new Exception('undefined custom field object_key');
		}

		$db = JFactory::getDbo();

		$query = 'ALTER IGNORE TABLE ' . $db->qn($table) . ' ADD COLUMN ' . $db->qn('custom' . $this->id) . ' TEXT';
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Drop column associated to custom field
	 *
	 * @param   int     $customId    custom field id
	 * @param   string  $object_key  type of object the custom field belongs to
	 *
	 * @return bool
	 */
	private function dropColumn($customId, $object_key)
	{
		switch ($object_key)
		{
			case 'redevent.event':
				$tablename = '#__redevent_events';
				break;

			case 'redevent.xref':
				$tablename = '#__redevent_event_venue_xref';
				break;

			default:
				return;
		}

		$db = JFactory::getDbo();
		$query = ' ALTER TABLE ' . $tablename . ' DROP custom' . $customId;
		$db->setQuery($query);

		if (!$res = $db->execute())
		{
			$this->setError(($db->getError()));

			return false;
		}

		return true;
	}
}
