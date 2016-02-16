<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent attachments Table class
 *
 * @package  Redevent.admin
 * @since    2.5
 */
class RedeventTableAttachment extends RedeventTable
{
	/**
	 * The name of the table with category
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableName = 'redevent_attachments';

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
		if (!parent::check())
		{
			return false;
		}

		return $this->checkExists();
	}

	/**
	 * Check if attachement already exists
	 *
	 * @return bool
	 */
	private function checkExists()
	{
		$query = $this->_db->getQuery(true)
			->select('id')
			->from('#__redevent_attachments')
			->where('object = ' . $this->_db->q($this->object))
			->where('file = ' . $this->_db->q($this->file));

		$this->_db->setQuery($query);
		$res = $this->_db->loadColumn();

		if ($res && (!$this->id || in_array($this->id, $res)))
		{
			$this->setError(JText::_('LIB_REDEVENT_ATTACHMENT_ALREADY_EXISTS'));

			return false;
		}

		return true;
	}
}
