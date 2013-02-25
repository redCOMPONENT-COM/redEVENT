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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Joomla Redevent Component Model
 *
 * @package		Redevent
 * @since 2.0
 */
class RedeventModelCustomfields extends FOFModel 
{
	protected function onBeforeDelete(&$id, &$table)
	{
		if (!parent::onBeforeDelete($id, $table)) {
			return false;
		}
		
		$query = ' SELECT object_key ' 
		       . ' FROM #__redevent_fields ' 
		       . ' WHERE id = ' . $this->_db->Quote($id);
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();
		
		switch($res)
		{
			case 'redevent.event':
				$tablename = '#__redevent_events';
				break;
			case 'redevent.xref':
				$tablename = '#__redevent_event_venue_xref';
				break;
			default:
				continue;
		}
		$query = ' ALTER TABLE '.$tablename.' DROP custom'.$id;
		$this->_db->setQuery($query);
		$res = $this->_db->query();
		return true;
	}

	protected function onAfterSave(&$row)
	{
		parent::onAfterSave($row);
		
		// add the field to the object table
		switch ($row->object_key)
		{
			case 'redevent.event':
				$table = '#__redevent_events';
				break;
			case 'redevent.xref':
				$table = '#__redevent_event_venue_xref';
				break;
			default:
				JError::raiseWarning(0, 'undefined custom field object_key');
				break;
		}
		$tables = $this->_db->getTableFields(array($table), false);
		$cols = $tables[$table];

		if (!array_key_exists('custom'.$row->id, $cols))
		{
			switch ($row->type)
			{
				default: // for now, let's not restrict the type...
					$columntype = 'TEXT';
			}
			$q = 'ALTER IGNORE TABLE '.$table.' ADD COLUMN custom'.$row->id.' '.$columntype;
			$this->_db->setQuery($q);
			if (!$this->_db->query()) {
				JError::raiseWarning(0, 'failed adding custom field to table');
			}
		}
		return true;
	}
	
	/**
	 * export
	 *
	 * @return array
	 */
	public function export()
	{
		$db = &JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('t.id, t.name, t.tag, t.type, t.tips, t.searchable');
		$query->select('t.in_lists, t.frontend_edit, t.required, t.object_key');
		$query->select('t.options, t.min, t.max, t.ordering, t.published, t.language');
		$query->from('#__redevent_fields AS t');
		$db->setQuery($query);
		$res = $db->loadAssocList();
		return $res;
	}
	
	/**
	 * import in database
	 * 
	 * @param array $records
	 * @param boolean $replace existing events with same id
	 * @return boolean true on success
	 */
	public function import($records, $replace = 0)
	{
		$count = array('added' => 0, 'updated' => 0);

		$tables = $this->_db->getTableFields(array('#__redevent_events', '#__redevent_event_venue_xref'), false);
	  
		$current = null; // current event for sessions
		foreach ($records as $r)
		{
			$row = $this->getTable();
			$row->bind($r);
			if (!$replace) {
				$row->id = null;
				$update = 0;
			}
			else if ($row->id) {
				$update = 1;
			}
			// store !
			if (!$row->check()) {
				JError::raiseWarning(0, JText::_('COM_REDEVENT_IMPORT_ERROR').': '.$row->getError());
				continue;
			}
			if (!$row->store()) {
				JError::raiseWarning(0, JText::_('COM_REDEVENT_IMPORT_ERROR').': '.$row->getError());
				continue;
			}

			// add the field to the object table
			switch ($row->object_key)
			{
				case 'redevent.event':
					$table = '#__redevent_events';
					break;
				case 'redevent.xref':
					$table = '#__redevent_event_venue_xref';
					break;
				default:
					JError::raiseWarning(0, 'undefined custom field object_key');
					break;
			}
			$cols = $tables[$table];
			 
			if (!array_key_exists('custom'.$row->id, $cols))
			{
				switch ($row->type)
				{
					default: // for now, let's not restrict the type...
						$columntype = 'TEXT';
				}
				$q = 'ALTER IGNORE TABLE '.$table.' ADD COLUMN custom'.$row->id.' '.$columntype;
				$this->_db->setQuery($q);
				if (!$this->_db->query()) {
					JError::raiseWarning(0, 'failed adding custom field to table');
				}
			}
		}
		return $count;
	}
}
