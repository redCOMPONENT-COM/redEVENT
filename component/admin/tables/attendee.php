<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Attendee Table class
 *
 * @package  Redevent.admin
 * @since    2.5
 */
class RedeventTableAttendee extends RedeventTable
{
	/**
	 * The name of the table with category
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableName = 'redevent_register';

	/**
	 * The primary key of the table
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableKey = 'id';

	/**
	 * Store associated submitter ids for delete
	 * @var array
	 */
	private $submitterIds;

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
		if (!$this->sid)
		{
			$this->setError(JText::_('COM_REDEVENT_missing_sid'));

			return false;
		}

		if (!$this->xref)
		{
			$this->setError(JText::_('COM_REDEVENT_missing_xref'));

			return false;
		}

		if (!$this->submit_key)
		{
			$this->setError(JText::_('COM_REDEVENT_missing_submit_key'));

			return false;
		}

		if (!$this->uregdate)
		{
			$this->uregdate = gmdate('Y-m-d H:i:s');
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
		$this->storeSubmitterIds($pk);

		return parent::beforeDelete($pk);
	}

	/**
	 * Store submitter ids associated to submission for after delete
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return array
	 */
	private function storeSubmitterIds($pk = null)
	{
		$pk = $this->sanitizePk($pk);

		$query = $this->_db->getQuery(true);

		$query->select('sid');
		$query->from('#__redevent_register');
		$query->where('id IN (' . $pk . ')');

		$this->_db->setQuery($query);
		$this->submitterIds = $this->_db->loadColumn();

		return $this->submitterIds;
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
		$core = new RdfCore;
		$core->deleteSubmission($this->submitterIds);

		return parent::afterDelete($pk);
	}
}
