<?php
/**
 * @version 1.0 $Id$
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

defined('_JEXEC') or die('Restricted access');

/**
 * EventList registration Model class
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEvent_register extends JTable
{
	function __construct(& $db) {
		parent::__construct('#__redevent_register', 'id', $db);
	}

	function loadBySid($sid)
	{
		$db =& $this->getDBO();

		$query = 'SELECT *'
		. ' FROM '.$this->_tbl
		. ' WHERE sid = '.$db->Quote($sid);
		$db->setQuery( $query );

		if ($result = $db->loadAssoc( )) {
			return $this->bind($result);
		}
		else
		{
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}

	function check()
	{
		if (!$this->sid) {
			$this->setError(JText::_('COM_REDEVENT_missing_sid'));
			return false;
		}
		if (!$this->xref) {
			$this->setError(JText::_('COM_REDEVENT_missing_xref'));
			return false;
		}
		if (!$this->submit_key) {
			$this->setError(JText::_('COM_REDEVENT_missing_submit_key'));
			return false;
		}

		if (!$this->uregdate) {
			$this->uregdate = gmdate('Y-m-d H:i:s');
		}
		return true;
	}
}
