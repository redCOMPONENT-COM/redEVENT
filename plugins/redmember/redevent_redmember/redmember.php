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

JPlugin::loadLanguage( 'plg_redevent_redmember', JPATH_ADMINISTRATOR );

class plgRedeventredmember extends JPlugin {
	
	private $_db;
 
	public function plgRedeventredmember(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
		
		$this->_db = &JFactory::getDBO();
	}

	/**
	 * performs the one time sync when requested from redevent groups view toolbar
	 * 
	 */
	public function onSync()
	{
		if (!$this->_checkDB())  {
			return array('plugin' => 'redMEMBER', 'result' => false);
		}
		if (!$this->_syncFrom() || !$this->_syncTo()) {
			return array('plugin' => 'redMEMBER', 'result' => false);
		}
		
		return array('plugin' => 'redMEMBER', 'result' => true);
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
			       . ' SET a.group_title = g.name '
			       . ' WHERE g.id = ' . $this->_db->Quote($group_id);
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();
			return true;
		}
		else
		{
			if (!$this->_addFromGroup($group_id)) {
				return false;
			}
			return true;
		}
	}
	
	public function onGroupsRemoved($cids)
	{
		if (!is_array($cids) || !count($cids)) {
			return true;
		}
		$query = ' DELETE a, s '
		       . ' FROM #__redevent_redmember_groups AS s ' 
		       . ' INNER JOIN #__redmember_accessgroup AS a ON a.group_id = s.redmember_group ' 
		       . ' WHERE s.redevent_group IN (' . implode(",", $cids).')';
		$this->_db->setQuery($query);
		$res = $this->_db->query();
		
		if (!$res) {
			JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_DELETING_GROUP'));
			return false;
		}
		return true;
	}
	
	public function onGroupMemberSaved($id, $isNew)
	{		
		if (!$this->_checkDB())  {
			return false;
		}
		if (!$isNew) 
		{
			// nothing to do
			return true;
		}
		else
		{
			//get user
			$query = ' SELECT gm.member, a.userid, s.redmember_group ' 
			       . ' FROM #__redevent_groupmembers AS gm '  
			       . ' INNER JOIN #__redevent_redmember_groups AS s ON gm.group_id = s.redevent_group '  
			       . ' INNER JOIN #__redmember_accessgroup AS a ON a.group_id = s.redmember_group ' 
			       . ' WHERE gm.id = ' . $this->_db->Quote($id);
			$this->_db->setQuery($query);
			$res = $this->_db->loadObject();
			
			$rm_users = explode(",", $res->userid);
			if (in_array($res->member, $rm_users)) { // already in...
				return true;
			}
			
			// add to the redmember group
			$rm_users[] = $res->member;
			
			$query = ' UPDATE #__redmember_accessgroup SET userid = '.$this->_db->Quote(implode(",", $rm_users))
			       . ' WHERE group_id = ' . $res->redmember_group
			       ;
			$this->_db->setQuery($query);
			$res = $this->_db->query();
				
			if (!$res) 
			{
				JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_INSERT_USER_QUERY').': '.$this->_db->getErrorMsg());
				return false;	
			}
		}		
	}
	
	public function onGroupMembersRemoved($cids)
	{
		if (!is_array($cids) || !count($cids)) {
			return true;
		}
	
		//get user
		$query = ' SELECT gm.member, a.userid, s.redmember_group ' 
		       . ' FROM #__redevent_groupmembers AS gm '  
		       . ' INNER JOIN #__redevent_redmember_groups AS s ON gm.group_id = s.redevent_group '  
		       . ' INNER JOIN #__redmember_accessgroup AS a ON a.group_id = s.redmember_group ' 
		       . ' WHERE gm.id IN ('. implode(",", $cids) .')';
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		
		$rm_users = explode(",", current($res)->userid);
		
		$remove = array();
		foreach ($res as $r) {
			$remove[] = $r->member;
		}
		$diff = array_diff($rm_users, $remove);
		
		$query = ' UPDATE #__redmember_accessgroup SET userid = '.$this->_db->Quote(implode(",", $diff))
		       . ' WHERE group_id = ' . reset($res)->redmember_group
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->query();
			
		if (!$res) 
		{
			JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_INSERT_USER_QUERY').': '.$this->_db->getErrorMsg());
			return false;	
		}
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
	
	/**
	 * perform the global sync, from redEVENT to redMEMBER
	 * 
	 * @return boolean true on success
	 */
	private function _syncFrom()
	{		
		// first sync the groups
		$query = ' SELECT re.id, re.name ' 
		       . ' FROM #__redevent_groups AS re ' 
		       . ' WHERE re.id NOT IN (SELECT redevent_group FROM #__redevent_redmember_groups) ';
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		
		if ($res === false) {
			JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_DIFF_QUERY'));
			return false;
		}
		
		foreach ((array) $res as $group) 
		{
			// create new access group in redmember
			if (!$this->_addFromGroup($group->id)) {
				return false;
			}
		}
		
		// then sync members
		// redmember users are written directly in access group table as a list...
		$query = ' SELECT s.redevent_group, s.redmember_group, g.userid  ' 
		       . ' FROM #__redevent_redmember_groups AS s '
		       . ' INNER JOIN #__redmember_accessgroup AS g ON g.group_id = s.redmember_group '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		foreach ((array) $res as $gp)
		{
			$query = ' SELECT gm.member ' 
			       . ' FROM #__redevent_groupmembers AS gm ' 
			       . ' WHERE gm.group_id = ' . $this->_db->Quote($gp->redevent_group)
			       . (empty($gp->userid) ? '' : ' AND gm.member NOT IN ('.$gp->userid.')')
			       ;
			$this->_db->setQuery($query);
			$res = $this->_db->loadResultArray();
			
			if ($res === false) { // an error in query
				JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_DIFF_GROUP_MEMBERS').': '.$this->_db->getErrorMsg());
				return false;
			}
			if (!count($res)) { // users are sync for this group
				continue;
			}
			//updating
			$obj = new stdclass();
			$obj->group_id = $gp->redmember_group;
			$obj->userid = empty($gp->userid) ? implode(',', $res) : $gp->userid.','.implode(',', $res);
			if (!$this->_db->updateObject('#__redmember_accessgroup', $obj, 'group_id')) 
			{
				JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_INSERT_NEW_MEMBERS').': '.$this->_db->getErrorMsg());
				return false;
			}						
		}
		
		return true;	
	}

	/**
	 * perform the global sync, from redMEMBER to redEVENT
	 * 
	 * @return boolean true on success
	 */
	private function _syncTo()
	{		
		// first sync the groups
		$query = ' SELECT re.group_id AS id, re.group_title AS name ' 
		       . ' FROM #__redmember_accessgroup AS re ' 
		       . ' WHERE re.group_id NOT IN (SELECT redmember_group FROM #__redevent_redmember_groups) ';
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		
		if ($res === false) {
			JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_DIFF_TO_QUERY'));
			return false;
		}
		
		foreach ((array) $res as $group) 
		{
			// create new access group in redmember
			$gp = new stdclass();
			$gp->name = $group->name;
			$gp->isdefault = 0;
			if (!$this->_db->insertObject('#__redevent_groups', $gp, 'id')) 
			{
				JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_INSERT_REDEVENT_GROUP_QUERY').': '.$this->_db->getErrorMsg());
				return false;	
			}
			
			// update sync table
			$new = new stdclass();
			$new->redevent_group = $gp->id;
			$new->redmember_group = $group->id;
			if (!$this->_db->insertObject('#__redevent_redmember_groups', $new)) 
			{
				JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_INSERT_GROUP_SYNC_ROW').': '.$this->_db->getErrorMsg());
				return false;
			}			
		}
		
		// then sync members
		// redmember users are written directly in access group table as a list...
		$query = ' SELECT s.redevent_group, s.redmember_group, g.userid  ' 
		       . ' FROM #__redevent_redmember_groups AS s '
		       . ' INNER JOIN #__redmember_accessgroup AS g ON g.group_id = s.redmember_group '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		foreach ((array) $res as $gp)
		{
			if (empty($gp->userid)) { // no users
				continue;
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
				JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_DIFFTO_GROUP_MEMBERS').': '.$this->_db->getErrorMsg());
				return false;
			}
			
			// difference
			$diff = array_diff($members, $res);
			if (!count($diff)) { // in sync
				continue;
			}
			
			$values = array();
			foreach ($diff as $member) {
				$values[] = '('.$member.','.$gp->redevent_group.')';
			}
			//updating
			$query = ' INSERT INTO #__redevent_groupmembers (member, group_id)' 
			       . ' VALUES ' . implode(", ", $values);
			$this->_db->setQuery($query);
			$res = $this->_db->query();
			
			if (!$res) 
			{
				JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_INSERT_NEW_MEMBERS_TO').': '.$this->_db->getErrorMsg());
				return false;
			}						
		}
		
		return true;	
	}
	
	/**
	 * add redevent group to redmember
	 * 
	 * @param int $redevent_id
	 * @return boolean
	 */
	private function _addFromGroup($redevent_id)
	{	
		// create new access group in redmember
		
		$query = ' INSERT INTO #__redmember_accessgroup (group_title, usertypeid) '
		       . ' SELECT name, '.$this->params->get('default_usertype', 1).' FROM #__redevent_groups AS rg '
		       . ' WHERE rg.id = ' . $redevent_id
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->query();
			
		if (!$res) 
		{
			JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_INSERT_GROUP_QUERY').': '.$this->_db->getErrorMsg());
			return false;	
		}
		
		// update sync table
		$new = new stdclass();
		$new->redevent_group = $redevent_id;
		$new->redmember_group = $this->_db->insertid();
		if (!$this->_db->insertObject('#__redevent_redmember_groups', $new)) 
		{
			JError::raiseWarning(0, JText::_('PLG_REDEVENT_REDMEMBER_ERROR_INSERT_GROUP_SYNC_ROW').': '.$this->_db->getErrorMsg());
			return false;
		}
		return true;
	}
}
?>