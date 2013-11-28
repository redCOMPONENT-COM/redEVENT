<?php
/**
 * @package    Redevent.Admin
 * @copyright  redEVENT (C) 2008-2013 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Include library dependencies
jimport('joomla.filter.input');

/**
 * Table class
 *
 * @package  Redevent.Admin
 * @since    2.0
*/
class Redevent_sessions_pricegroups extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;

	/**
	 * Session id
	 *
	 * @var int
	 */
	var $xref = null;

	/**
	 * Price group
	 *
	 * @var int
	 */
	var $pricegroup_id = null;

	/**
	 * Price
	 *
	 * @var float
	 */
	var $price = 0;

	/**
	 * Currency.
	 *
	 * @var string
	 */
	var $currency = null;

	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__redevent_sessions_pricegroups', 'id', $db);
	}

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
