<?php
/**
 * @version 2.0
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008,2009,2010,2011 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
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
class RedeventModelSelectUser extends JModel 
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
    $mainframe = JFactory::getApplication();
    $option = 'com_redevent';

    // Get the pagination request variables
    $limit      = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
    $limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );

    $this->setState('limit', $limit);
    $this->setState('limitstart', $limitstart);
    
    // filters and ordering
    $filter_order     = $mainframe->getUserStateFromRequest( 'com_redevent.sessions.filter_order', 'filter_order', 'a.name', 'cmd' );
    $filter_order_Dir = $mainframe->getUserStateFromRequest( 'com_redevent.sessions.filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
    $search           = $mainframe->getUserStateFromRequest( 'com_redevent.sessions.search', 'search', '', 'string' );
    
    $this->setState('filter_order',      $filter_order);
    $this->setState('filter_order_Dir',  $filter_order_Dir);
    $this->setState('search',            strtolower($search));
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
      $pagination = $this->getPagination();
      $res = $this->_getList($query, $pagination->limitstart, $pagination->limit);

      if (!$res) {
   	  	echo $this->_db->getErrorMsg();
   	  	return false;
  		}
  		
  		$this->_data = $res;
    }
    return $this->_data;
  }
  
	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT a.* '
			. ' FROM #__users AS a '
			. $where
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
		global $mainframe, $option;

		$filter_order		  = $this->getState('filter_order');
		$filter_order_Dir	= $this->getState('filter_order_Dir');

		if ($filter_order == 'a.name'){
			$orderby 	= ' ORDER BY a.name '.$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' ,a.name ';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		global $mainframe, $option;

		$search				= $this->getState('search');

		$where = array();
		
		if ($search) {
			$where[] = '( LOWER(a.name) LIKE '.$this->_db->Quote('%'.$search.'%')
			         . ' OR LOWER(a.username) LIKE '.$this->_db->Quote('%'.$search.'%').')';
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
}
?>
