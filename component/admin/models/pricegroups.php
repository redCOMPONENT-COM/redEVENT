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
class RedeventModelPricegroups extends JModel 
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
    $mainframe = &JFactory::getApplication();
    $option = JRequest::getCmd('option');

    // Get the pagination request variables
    $limit      = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
    $limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );

    $this->setState('limit', $limit);
    $this->setState('limitstart', $limitstart);
    
    // filters and ordering
    $filter_order     = $mainframe->getUserStateFromRequest( 'com_redevent.pricegroups.filter_order', 'filter_order', 'obj.ordering', 'cmd' );
    $filter_order_Dir = $mainframe->getUserStateFromRequest( 'com_redevent.pricegroups.filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
    $search           = $mainframe->getUserStateFromRequest( 'com_redevent.pricegroups.search', 'search', '', 'string' );

    $this->setState('filter_order', $filter_order);
    $this->setState('filter_order_Dir', $filter_order_Dir);
    $this->setState('search', strtolower($search));        

    $language = $mainframe->getUserStateFromRequest('com_redevent.pricegroups.filter.language', 'filter_language', '');
    $this->setState('filter.language', $language);
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
      if (!$this->_data = $this->_getList($query, $pagination->limitstart, $pagination->limit))
      echo $this->_db->getErrorMsg();
    }
    
    return $this->_data;
  }
  
	function _buildQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT obj.*, u.name AS editor, l.title AS language_title '
			. ' FROM #__redevent_pricegroups AS obj '
			. ' LEFT JOIN #__users AS u ON u.id = obj.checked_out '
			. ' LEFT JOIN #__languages AS l ON l.lang_code = obj.language '
			. $where
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$filter_order		  = $this->getState('filter_order');
		$filter_order_Dir	= $this->getState('filter_order_Dir');

		if ($filter_order == 'obj.ordering'){
			$orderby 	= ' ORDER BY obj.ordering '.$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , obj.ordering ';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$search				= $this->getState('search');

		$where = array();

		if ($search) {
			$where[] = 'LOWER(obj.name) LIKE '.$this->_db->Quote('%'.$search.'%');
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language')) {
			$where[] = 'obj.language = '.$this->_db->quote($language);
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
