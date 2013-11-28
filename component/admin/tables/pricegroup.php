<?php
/**
 * @version 1.0 $Id: cleanup.php 298 2009-06-24 07:42:35Z julien $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
* Table class
*
* @package		Redevent
* @since 2.0
*/
class RedeventTablePricegroup extends FOFTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	public function __construct($table, $key, &$db)
	{
		parent::__construct('#__redevent_pricegroups', 'id', $db);
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.0
	 */
	public function check()
	{
		if (empty($this->name))
		{
			$this->setError(JText::_('COM_REDEVENT_TABLE_PRICEGROUPS_CHECK_NAME_IS_REQUIRED'));

			return false;
		}

		$alias = JFilterOutput::stringURLSafe($this->name);

		if( empty($this->alias) || $this->alias === $alias)
		{
			$this->alias = $alias;
		}

		return true;
	}

	public function onBeforeDelete($oid)
	{
		// make sure it's not being used in sessions
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('r.id');
		$query->from('#__redevent_sessions_pricegroups AS r');
		$query->where('r.pricegroup_id = '.$oid);
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
