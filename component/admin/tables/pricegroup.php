<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Price group Table class
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventTablePricegroup extends RTable
{
	/**
	 * The name of the table with category
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableName = 'redevent_pricegroups';

	/**
	 * The primary key of the table
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableKey = 'id';

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
		if (empty($this->name))
		{
			$this->setError(JText::_('COM_REDEVENT_TABLE_PRICEGROUPS_CHECK_NAME_IS_REQUIRED'));

			return false;
		}

		$alias = JFilterOutput::stringURLSafe($this->name);

		if (empty($this->alias) || $this->alias === $alias)
		{
			$this->alias = $alias;
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

		// Make sure it's not being used in sessions
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('r.id');
		$query->from('#__redevent_sessions_pricegroups AS r');
		$query->where('r.pricegroup_id in (' . $pk . ')');
		$db->setQuery($query);
		$res = $db->loadObject();

		if ($res)
		{
			$this->setError(Jtext::_('COM_REDEVENT_PRICEGROUPS_DELETE_ERROR_PRICEGROUP_ASSIGNED'));

			return false;
		}

		return true;
	}
}
