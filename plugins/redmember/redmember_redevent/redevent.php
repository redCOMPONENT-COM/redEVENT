<?php
/**
 * @version 1.0 $Id: admin.class.php 662 2008-05-09 22:28:53Z schlu $
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
defined( '_JEXEC' ) or die( 'Restricted access' );
 
// Import library dependencies
jimport('joomla.event.plugin');

JPlugin::loadLanguage( 'plg_redmember_redevent', JPATH_ADMINISTRATOR );

class plgRedmemberredevent extends JPlugin {
	
	private $_db;
 
	public function plgRedmemberredevent(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
		
		$this->_db = &JFactory::getDBO();
	}
	
	public function onGroupSaved($group_id, $isNew)
	{
		if (!$this->_checkDB())  {
			return false;
		}
		if (!$isNew) 
		{
			// just sync the group names
			$query = ' UPDATE #__redmember_accessgroup AS a ' 
			       . ' INNER JOIN #__redevent_redmember_groups AS s ON a.group_id = s.redmember_group ' 
			       . ' INNER JOIN #__redevent_groups AS g ON g.id = s.redevent_group ' 
			       . ' SET g.name = a.group_title '
			       . ' WHERE a.group_id = ' . $this->_db->Quote($group_id);
			$this->_db->setQuery($query);
			$res = $this->_db->query();
			
			if (!$res) {
				JError::raiseWarning(0, JText::_('PLG_REDMEMBER_REDEVENT_ERROR_UPDATING_GROUP'));
				return false;
			}
		}
		else
		{
			// add group in redevent
			
			$query = ' INSERT INTO #__redevent_groups (name) '
		       . ' SELECT group_title FROM #__redmember_accessgroup AS rg '
		       . ' WHERE rg.group_id = ' . $group_id
		       ;
			$this->_db->setQuery($query);
			if (!$this->_db->query()) 
			{
				JError::raiseWarning(0, JText::_('PLG_REDMEMBER_REDEVENT_ERROR_CREATING_GROUP').': '.$this->_db->getErrorMsg());
				return false;	
			}
			
			// update sync table
			$new = new stdclass();
			$new->redevent_group = $this->_db->insertid();
			$new->redmember_group = $group_id;
			if (!$this->_db->insertObject('#__redevent_redmember_groups', $new)) 
			{
				JError::raiseWarning(0, JText::_('PLG_REDMEMBER_REDEVENT_ERROR_INSERT_GROUP_SYNC_ROW').': '.$this->_db->getErrorMsg());
				return false;
			}			
		}
					
		// now the users
		$query = ' SELECT s.redevent_group, s.redmember_group, g.userid  ' 
		       . ' FROM #__redevent_redmember_groups AS s '
		       . ' INNER JOIN #__redmember_accessgroup AS g ON g.group_id = s.redmember_group '
		       . ' WHERE s.redmember_group = ' . $this->_db->Quote($group_id)
		       ;
		$this->_db->setQuery($query);
		$gp = $this->_db->loadObject();
		if (empty($gp->userid)) { // no users
			return true;
		}
		$members = explode(",", $gp->userid);
		JArrayHelper::toInteger($members);
		
		$query = ' SELECT gm.member ' 
		       . ' FROM #__redevent_groupmembers AS gm ' 
		       . ' WHERE gm.group_id = ' . $this->_db->Quote($gp->redevent_group)
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadResultArray();
		
		if ($res === false) { // an error in query
			JError::raiseWarning(0, JText::_('PLG_REDMEMBER_REDEVENT_ERROR_DIFFTO_GROUP_MEMBERS').': '.$this->_db->getErrorMsg());
			return false;
		}
		
		// difference
		$current = array_diff($members, $res);
		$removed = array_diff($res, $members);
		if (count($current)) 
		{ 		
			$values = array();
			foreach ($current as $member) {
				$values[] = '('.$member.','.$gp->redevent_group.')';
			}
			//updating
			$query = ' INSERT INTO #__redevent_groupmembers (member, group_id)' 
			       . ' VALUES ' . implode(", ", $values);
			$this->_db->setQuery($query);
			$res = $this->_db->query();
			
			if (!$res) 
			{
				JError::raiseWarning(0, JText::_('PLG_REDMEMBER_REDEVENT_ERROR_INSERT_NEW_MEMBERS_TO').': '.$this->_db->getErrorMsg());
				return false;
			}
		}
		
		// now remove the ones that are no longer in the group
		if (count($removed))
		{
			$query = ' DELETE FROM #__redevent_groupmembers ' 
			       . ' WHERE member IN (' . implode(", ", $removed).')'
			       ;
			$this->_db->setQuery($query);
			$res = $this->_db->query();
			
			if (!$res) 
			{
				JError::raiseWarning(0, JText::_('PLG_REDMEMBER_REDEVENT_ERROR_REMOVING_MEMBERS_TO').': '.$this->_db->getErrorMsg());
				return false;
			}			
		}

		return true;
	}
	
	public function onGroupsRemoved($cids)
	{
		if (!is_array($cids) || !count($cids)) {
			return true;
		}
		$query = ' DELETE a, s '
		       . ' FROM #__redevent_redmember_groups AS s ' 
		       . ' INNER JOIN #__redevent_groups AS a ON a.id = s.redevent_group ' 
		       . ' WHERE s.redmember_group IN (' . implode(",", $cids).')';
		$this->_db->setQuery($query);
		$res = $this->_db->query();
		
		if (!$res) {
			JError::raiseWarning(0, JText::_('PLG_REDMEMBER_REDEVENT_ERROR_DELETING_GROUP'));
			return false;
		}
		return true;
	}
		
	/**
	 * check that the sync table exists, if not create it
	 */
	private function _checkDB()
	{
		$db = &JFactory::getDBO();
		
		$query = ' SHOW TABLES LIKE '.$db->Quote('%redevent_redmember_groups');
		$db->setQuery($query);
		$res = $db->loadObject();
		
		if ($res) {
			return true;
		}
		else 
		{
			$query = ' CREATE TABLE IF NOT EXISTS `#__redevent_redmember_groups` (
			               `redevent_group` int(11) NOT NULL,
			               `redmember_group` int(11) NOT NULL,
			               KEY `redevent_group` (`redevent_group`),
			               KEY `redmember_group` (`redmember_group`)
			           ) ENGINE=MyISAM DEFAULT CHARSET=latin1; ';
			$db->setQuery($query);
			if ($db->query()) {
				return true;
			}
			else {
				JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_CREATING_TABLE'));
				return false;
			}
		}
		return true;
	}
	
}
