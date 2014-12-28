<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008-2014 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent session price groups Table class
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventTableSessionpricegroup extends RedeventTable
{
	/**
	 * The name of the table with category
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableName = 'redevent_sessions_pricegroups';

	/**
	 * The primary key of the table
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableKey = 'id';

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @return boolean True on success
	 */
	public function check()
	{
		if (!$this->xref)
		{
			$this->setError(JText::_('COM_REDEVENT_TABLE_SESSIONS_PRICEGROUPS_CHECK_XREF_IS_REQUIRED'));

			return false;
		}

		if (!$this->pricegroup_id)
		{
			$this->setError(JText::_('COM_REDEVENT_TABLE_SESSIONS_PRICEGROUPS_CHECK_PRICEGROUP_IS_REQUIRED'));

			return false;
		}

		return true;
	}
}
