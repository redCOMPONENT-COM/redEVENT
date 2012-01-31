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

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * EventList Component Category Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedEventModelTextLibrary extends JModel
{
	/**
	 * Text id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Category data array
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
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');
		
		// Get the pagination request variables
    $limit      = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
    $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
 
		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
    $this->setState('limit', $limit);
    $this->setState('limitstart', $limitstart);
    
    // filters and ordering
    $filter_order     = $mainframe->getUserStateFromRequest( 'com_redevent.textlibrary.filter_order', 'filter_order', 'obj.text_name', 'cmd' );
    $filter_order_Dir = $mainframe->getUserStateFromRequest( 'com_redevent.textlibrary.filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );    
        
    $this->setState('filter_order',      $filter_order);
    $this->setState('filter_order_Dir',  $filter_order_Dir);
    
		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
		
	}

	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int category identifier
	 */
	function setId($id)
	{
		// Set category id and wipe data
		$this->_id	    = $id;
		$this->_data	= null;
	}

	/**
	 * Method to get content category data
	 *
	 * @access	public
	 * @return	array
	 */
	function getData() 
	{
		if (empty($this->_data))
		{			
      $query = $this->_buildQuery();
      $pagination = $this->getPagination();
      $this->_data = $this->_getList($query, $pagination->limitstart, $pagination->limit);
		}
		return $this->_data;
	}
	
	protected function _buildQuery()
	{
		$filter_order		  = $this->getState('filter_order');
		$filter_order_Dir	= $this->getState('filter_order_Dir');

		if ($filter_order == 'obj.text_name'){
			$orderby 	= ' ORDER BY obj.text_name '.$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , obj.text_name ';
		}
		
		$query = 'SELECT obj.* '
				. ' FROM #__redevent_textlibrary AS obj '
				. $orderby
				;
		return $query;
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
    * Retrieve a field to edit
    */
   function getText() {
      $row = $this->getTable();
      $my = JFactory::getUser();
      $id = JRequest::getVar('cid');

      /* load the row from the db table */
      $row->load($id[0]);

      if ($id[0]) {
         // do stuff for existing records
         $result = $row->checkout( $my->id );
      } else {
         // do stuff for new records
         $row->published    = 1;
      }
      return $row;
   }
	
	/**
	 * Method to store the tag
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data) 
	{
		$row  = $this->getTable();

		// bind it to the table
		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		// Make sure the data is valid
		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}

		// Store it in the db
		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}

		return $row->id;
	}
	
 /**
   * Method to remove a text libray element
   *
   * @access  public
   * @return  boolean True on success
   * @since 0.9
   */
  function delete($cid = array())
  {
    if (count( $cid ))
    {
      $cids = implode( ',', $cid );

      $query = 'DELETE FROM #__redevent_textlibrary'
          . ' WHERE id IN ('. $cids .')'
          ;

      $this->_db->setQuery( $query );
      
      if(!$this->_db->query()) {
        $this->setError($this->_db->getErrorMsg());
        return false;
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
		$query = ' SELECT t.id, t.text_name, t.text_description, t.text_field  '
		       . ' FROM #__redevent_textlibrary AS t '
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
		
		$current = null; // current event for sessions
		foreach ($records as $r)
		{			
			$v = Jtable::getInstance('Textlibrary', 'Table');	
			$v->bind($r);
			if (!$replace) {
				$v->id = null;
				$update = 0;
			}
			else if ($v->id) {
				$update = 1;
			}
			// store !
			if (!$v->check()) {
				JError::raiseWarning(0, JText::_('COM_REDEVENT_IMPORT_ERROR').': '.$v->getError());
				continue;
			}
			if (!$v->store()) {
				JError::raiseWarning(0, JText::_('COM_REDEVENT_IMPORT_ERROR').': '.$v->getError());
				continue;
			}
		}
		return $count;
	}
}
