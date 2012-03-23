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
 * EventList groupmembers Model class
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEvent_groupmembers extends JTable
{
	/**
	 * Primary Key
	 * @var int
	 */
	var $id 				= null;
	/**
	 * group id
	 * @var int
	 */
	var $group_id 				= null;
	/** 
	 * user id
	 * @var int 
	 * */
	var $member				= null;
	/**
	 * is group admin ?
	 * @var int
	 */
	var $is_admin = 0;
	/**
	 * allowed to add events
	 * @var int
	 */
	var $manage_events = 0;
	/**
	 * allowed to add xrefs
	 * @var int
	 */
	var $manage_xrefs = 0;
	/**
	 * allowed to view or edit attendees
	 * @var int
	 */
	var $manage_attendees = 0;
	/**
	 * allowed to add/edit venues
	 * @var int
	 */
	var $edit_venues = 0;
	/** @var int */
	var $publish_events    = 0;
	/** @var int */
	var $publish_venues	  = 0;
	/**
	 * receive registrations to events
	 * @var int
	 */
	var $receive_registrations = 0;
	
	/** @var int */
	var $checked_out 		= 0;
	/** @var date */
	var $checked_out_time	= 0;
	
	function redevent_groupmembers(& $db) {
		parent::__construct('#__redevent_groupmembers', 'id', $db);
	}
	
	function check()
	{
		if (!($this->member)) {
			$this->setError(JText::_('COM_REDEVENT_USER_ID_REQUIRED'));
			return false;
		}
		if (!($this->group_id)) {
			$this->setError(JText::_('COM_REDEVENT_GROUP_ID_REQUIRED'));
			return false;
		}
		return true;
	}
}
