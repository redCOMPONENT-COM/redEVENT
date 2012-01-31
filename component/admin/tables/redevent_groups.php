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
 * EventList groups Model class
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEvent_groups extends JTable
{
	/**
	 * Primary Key
	 * @var int
	 */
	var $id 				= null;
	/** @var int */
	var $name				= '';
	/** @var string */
	var $description 		= null;
	/** @var string */
	var $parameters     = null;
	/** @var int */
	var $isdefault      = 0;
	/** @var int */
	var $edit_events    = 0;
	/** @var int */
	var $edit_venues	  = 0;
	/** @var int */
	var $publish_events    = 0;
	/** @var int */
	var $publish_venues	  = 0;
	/** @var int */
	var $checked_out 		= 0;
	/** @var date */
	var $checked_out_time	= 0;

	function redevent_groups(& $db) {
		parent::__construct('#__redevent_groups', 'id', $db);
	}

	function bind($array, $ignore = '')
	{
		if (key_exists( 'parameters', $array ) && is_array( $array['parameters'] ))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['parameters']);
			$array['parameters'] = $registry->toString();
		}
		return parent::bind($array, $ignore);
	}
	
	// overloaded check function
	function check()
	{
		// Not typed in a category name?
		if (trim( $this->name ) == '') {
			$this->_error = JText::_('COM_REDEVENT_ADD_GROUP_NAME' );
			RedeventError::raiseWarning('REDEVENT_GENERIC_ERROR', $this->_error );
			return false;
		}
		
		/** check for existing name */
		$query = 'SELECT id FROM #__redevent_groups WHERE name = '.$this->_db->Quote($this->name);
		$this->_db->setQuery($query);

		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			JError::raiseWarning('REDEVENT_GENERIC_ERROR', JText::sprintf('COM_REDEVENT_GROUP_NAME_S_ALREADY_EXISTS', $this->name));
			return false;
		}
		
		/** check it's the only with default set to 1 **/
		if ($this->isdefault)
		{
			/** check for existing name */
			$query = 'SELECT id FROM #__redevent_groups WHERE isdefault = 1';
			$this->_db->setQuery($query);
	
			$xid = intval($this->_db->loadResult());
			if ($xid && $xid != intval($this->id)) {
				JError::raiseWarning('REDEVENT_GENERIC_ERROR', JText::_('COM_REDEVENT_THERE_IS_ALREADY_A_DEFAULT_GROUP'));
				return false;
			}
		}

		return true;
	}
}
