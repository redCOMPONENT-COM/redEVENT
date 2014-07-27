<?php
/**
 * @package    Redevent.Administrator
 *
 * @copyright  redEVENT (C) 2014 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Table Organizations
 *
 * @package  Redevent.Administrator
 * @since    2.5
 */
class RedeventTableOrganization extends FOFTable
{
	/**
	 * Class Constructor.
	 *
	 * @param   string           $table  Name of the database table to model.
	 * @param   string           $key    Name of the primary key field in the table.
	 * @param   JDatabaseDriver  &$db    Database driver
	 */
	public function __construct($table, $key, &$db)
	{
		parent::__construct('#__redevent_organizations', 'id', $db);
	}
}
