<?php
/**
 * @version 1.0 $Id: redevent_groups.php 298 2009-06-24 07:42:35Z julien $
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
 * redEVENT groups venues categories Table class
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEvent_groupsvenuescategories extends JTable
{
	/**
	 * Primary Key
	 * @var int
	 */
	var $id 				= null;
	/** @var int */
	var $group_id		= null;
	/** @var int */
	var $category_id		= null;
	/** @var int */
	var $accesslevel		= 0;

	function redevent_groupsvenuescategories(& $db) {
		parent::__construct('#__redevent_groups_venues_categories', 'id', $db);
	}

	// overloaded check function
	function check()
	{
		if (!$this->group_id) 
		{
			$this->_error = JText::_( 'GROUP REQUIRED' );
			RedeventError::raiseWarning('REDEVENT_GENERIC_ERROR', $this->_error );
			return false;
		}
		
		if (!$this->category_id) 
		{
			$this->_error = JText::_( 'CATEGORY REQUIRED' );
			RedeventError::raiseWarning('REDEVENT_GENERIC_ERROR', $this->_error );
			return false;
		}
		
		/** check for existing relationship */
		$query = ' SELECT id FROM #__redevent_groups_venues_categories '
		       . ' WHERE group_id = '.$this->_db->Quote($this->group_id)
		       . '   AND category_id = '.$this->_db->Quote($this->category_id)
		       ;
		$this->_db->setQuery($query);

		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			JError::raiseWarning('REDEVENT_GENERIC_ERROR', JText::_('GROUP VENUES CATEGORY RELATIONSHIP ALREADY EXISTS'));
			return false;
		}
		
		return true;
	}
}
?>