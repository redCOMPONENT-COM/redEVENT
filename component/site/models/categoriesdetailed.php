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

require_once('baseeventslist.php');

/**
 * EventList Component Categoriesdetailed Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelCategoriesdetailed extends RedeventModelBaseEventList
{	
  /**
   * Top category for the view.
   * 
   * @var object
   */
  var $_parent = null;
  
	/**
	 * Categories data array
	 *
	 * @var integer
	 */
	var $_categories = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$mainframe = &JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams('com_redevent');
		
		if ($params->get('parentcategory', 0)) {
		  $this->setParent($params->get('parentcategory', 0));
		}

		//get the number of events from database
		$limit			= $params->get('cat_num');
    $limitstart = JRequest::getVar('limitstart', 0, '', 'int');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * set the parent category id
	 * 
	 * @param int id
	 * @return boolean
	 */
	function setParent($id)
	{	  
    $sub = ' SELECT id, lft, rgt FROM #__redevent_categories WHERE id = '. $this->_db->Quote((int) $id);
    $this->_db->setQuery($sub);
    $obj = $this->_db->loadObject();
    if (!$obj) {
      JError::raiseWarning(0, JText::_('PARENT CATEGORY NOT FOUND'));
    }
    else {
  	  $this->_parent = $obj;
  	  $this->_categories = null;
    }
	  return true;
	}
	
	/**
	 * Method to get the Categories
	 *
	 * @access public
	 * @return array
	 */
	function &getData( )
	{
		global $mainframe;

		$params 	= & $mainframe->getParams();
		$elsettings = & redEVENTHelper::config();

		// Lets load the content if it doesn't already exist
		if (empty($this->_categories))
		{
			$query = $this->_buildQuery();
			$this->_categories = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit') );

			$count = count($this->_categories);
			for($i = 0; $i < $count; $i++)
			{
				$category =& $this->_categories[$i];
				$category->events = $this->_getEvents($category);
        $category->assignedevents = $this->_getEventsTotal($category);
        
				//Generate description
				if (empty ($category->catdescription)) {
					$category->catdescription = JText::_( 'NO DESCRIPTION' );
				} else {
					//execute plugins
					$category->text		= $category->catdescription;
					$category->title 	= $category->catname;
					JPluginHelper::importPlugin('content');
					$results = $mainframe->triggerEvent( 'onPrepareContent', array( &$category, array(), 0 ));
					$category->catdescription = $category->text;
				}

				if ($category->image != '') {

					$attribs['width'] = $elsettings->imagewidth;
					$attribs['height'] = $elsettings->imagehight;

					$category->image = JHTML::image('images/stories/'.$category->image, $category->catname, $attribs);
				} else {
					$category->image = JHTML::image('components/com_redevent/assets/images/noimage.png', $category->catname);
				}
				
				//create target link
				$task 	= JRequest::getWord('task');
				
				$category->linktext = $task == 'archive' ? JText::_( 'SHOW ARCHIVE' ) : JText::_( 'SHOW EVENTS' );

				if ($task == 'archive') {
					$category->linktarget = JRoute::_('index.php?option=com_redevent&view=categoryevents&id='.$category->slug.'&task=archive');
				} else {
					$category->linktarget = JRoute::_('index.php?option=com_redevent&view=categoryevents&id='.$category->slug);
				}
				
			}

		}

		return $this->_categories;
	}

	/**
	 * Method to get the Categories events
	 *
	 * @access public
	 * @return array
	 */
	function &_getEvents( &$category )
	{
		$mainframe = &JFactory::getApplication();

		$params 	= & $mainframe->getParams('com_redevent');

		// Lets load the content
		$query = $this->_buildDataQuery( $category );
		$this->_data = $this->_getList( $query, 0, $params->get('detcat_nr') );
    $this->_data = $this->_categories($this->_data);
    $this->_data = $this->_getPlacesLeft($this->_data);
    $this->_data = $this->_getEventsCustoms($this->_data);

		return $this->_data;
	}

  /**
   * Method to get the Categories events
   *
   * @access public
   * @return array
   */
  function _getEventsTotal( &$category )
  {
    // Lets load the content
    $query = $this->_buildDataQuery( $category );
    return $this->_getListCount( $query, 0, 0 );
  }
  
	/**
	 * Method get the event query
	 *
	 * @access private
	 * @return array
	 */
	function _buildDataQuery( &$category )
	{
		$user		= & JFactory::getUser();
		$aid		= (int) $user->get('aid');
		
		$task 		= JRequest::getWord('task');

		$where = ' WHERE c.lft BETWEEN '. $this->_db->Quote($category->lft) .' AND '. $this->_db->Quote($category->rgt);
		// First thing we need to do is to select only the requested events
		if ($task == 'archive') {
			$where .= ' AND x.published = -1 ';
		} else {
			$where .= ' AND x.published = 1 ';
		}

		//Get Events from Category				
    $query = 'SELECT a.id, a.datimage, x.venueid, x.dates, x.enddates, x.times, x.endtimes, x.id AS xref, x.registrationend, x.id AS xref, x.maxattendees, x.maxwaitinglist, '
        . ' a.title, a.registra, l.venue, l.city, l.state, l.url, c.catname, c.id AS catid, '
        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
        . ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug, '
        . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug '
        . ' FROM #__redevent_events AS a'
        . ' INNER JOIN #__redevent_event_venue_xref AS x on x.eventid = a.id'
        . ' INNER JOIN #__redevent_venues AS l ON l.id = x.venueid'
        . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
        . ' INNER JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
        . $where
        . ' GROUP BY (x.id) '
        . ' ORDER BY x.dates, x.times'
        ;
		return $query;
	}

	/**
	 * Method get the categories query
	 *
	 * @access private
	 * @return array
	 */
	function _buildQuery( )
	{
    $mainframe = &JFactory::getApplication();
    $params   = & $mainframe->getParams('com_redevent');
		$user		= & JFactory::getUser();
		$gid 		= (int) $user->get('aid');

    //get categories
    if ($params->get('display_all_categories', 1)) 
    {
      $query = ' SELECT c.*, '
            . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug'
            . ' FROM #__redevent_categories AS c '
            . ' WHERE c.published = 1 '
            ;        
      if ($this->_parent) {
        $query .= ' AND c.parent_id = '. $this->_db->Quote($this->_parent->id);     
      }
    }
    else
    {   
      //check archive task and ensure that only categories get selected if they contain a published/archived event
      $task   = JRequest::getWord('task');
      if($task == 'archive') {
        $eventstate = ' AND x.published = -1';
      } else {
        $eventstate = ' AND x.published = 1';
      }
      
      $query = ' SELECT DISTINCT c.*,  '
          . '   CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug '
          . ' FROM #__redevent_categories AS c '
          . ' INNER JOIN #__redevent_categories AS child ON child.lft BETWEEN c.lft AND c.rgt '
          . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.category_id = child.id '
          . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = xcat.event_id '
          . ' WHERE child.published = 1 '
          . '   AND child.access <= '.$gid
          .     $eventstate
          ;  
      
      if ($this->_parent) {
        $query .= ' AND c.parent_id = '. $this->_db->Quote($this->_parent->id);      
      }
      
      $query .= '   GROUP BY c.id ';      
    }		     
		
		$query .= ' ORDER BY c.ordering ASC ';
		
		return $query;
	}
	
}
?>