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

jimport('joomla.application.component.model');

/**
 * Joomla Redevent Component Model
 *
 * @package		Redevent
 * @since 2.0
 */
class RedeventModelCustomfields extends JModel 
{
   /**
   * list data array
   *
   * @var array
   */
  var $_data = null;

  /**
   * total
   *
   * @var integer
   */
  var $_total = null;

  /**
   * Pagination object
   *
   * @var object
   */
  var $_pagination = null;
  
  /**
   * Constructor
   *
   * @since 0.1
   */
  function __construct()
  {
    parent::__construct();
    global $mainframe, $option;

    // Get the pagination request variables
    $limit    = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
    $limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );

    $this->setState('limit', $limit);
    $this->setState('limitstart', $limitstart);
  }
  
  /**
   * Method to get List data
   *
   * @access public
   * @return array
   */
  function getData()
  {
    // Lets load the content if it doesn't already exist
    if (empty($this->_data))
    {
      $query = $this->_buildQuery();
      if (!$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit')))
      echo $this->_db->getErrorMsg();
    }
    
    return $this->_data;
  }
  
	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT obj.*, u.name AS editor '
			. ' FROM #__redevent_fields AS obj '
			. ' LEFT JOIN #__users AS u ON u.id = obj.checked_out '
			. $where
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
		global $mainframe, $option;

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.customfields.filter_order',		'filter_order',		'obj.ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.customfields.filter_order_Dir',	'filter_order_Dir',	'',				'word' );

		if ($filter_order == 'obj.ordering'){
			$orderby 	= ' ORDER BY obj.ordering '.$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , obj.ordering ';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		global $mainframe, $option;

		$filter_state		= $mainframe->getUserStateFromRequest( $option.'.customfields.filter_state',		'filter_state',		'',				'word' );
		$search				= $mainframe->getUserStateFromRequest( $option.'.customfields.search',			'search',			'',				'string' );
		$search				= JString::strtolower( $search );

		$where = array();

		if ($search) {
			$where[] = 'LOWER(obj.name) LIKE '.$this->_db->Quote('%'.$search.'%');
		}
		if ( $filter_state ) {
			if ( $filter_state == 'P' ) {
				$where[] = 'obj.published = 1';
			} else if ($filter_state == 'U' ) {
				$where[] = 'obj.published = 0';
			}
		}

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		return $where;
	}
	
  /**
   * Method to get a pagination object
   *
   * @access public
   * @return integer
   */
  function getPagination()
  {
    // Lets load the content if it doesn't already exist
    if (empty($this->_pagination))
    {
      jimport('joomla.html.pagination');
      $this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
    }

    return $this->_pagination;
  }
  

  /**
   * Total nr of items
   *
   * @access public
   * @return integer
   */
  function getTotal()
  {
    // Lets load the total nr if it doesn't already exist
    if (empty($this->_total))
    {
      $query = $this->_buildQuery();
      $this->_total = $this->_getListCount($query);
    }

    return $this->_total;
  }
  

	/**
	 * export 
   *
	 * @return array
	 */
	public function export()
	{				
		$query = ' SELECT t.id, t.name, t.tag, t.type, t.tips, t.searchable, '
		       . ' t.in_lists, t.frontend_edit, t.required, t.object_key, '
		       . ' t.options, t.min, t.max, t.ordering, t.published  '
		       . ' FROM #__redevent_fields AS t '
		       ;
    $this->_db->setQuery($query);
    
    $results = $this->_db->loadAssocList();
    
    return $results;
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
			$row = Jtable::getInstance('Redevent_customfield', '');	
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
?>
