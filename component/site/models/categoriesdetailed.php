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
 * EventList Component Categoriesdetailed Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelCategoriesdetailed extends JModel
{
	/**
	 * Event data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Categories total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Categories data array
	 *
	 * @var integer
	 */
	var $_categories = null;

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

		global $mainframe;

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams('com_redevent');

		//get the number of events from database
		$limit			= JRequest::getInt('limit', $params->get('cat_num'));
		$limitstart		= JRequest::getInt('limitstart');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
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

			$k = 0;
			$count = count($this->_categories);
			for($i = 0; $i < $count; $i++)
			{
				$category =& $this->_categories[$i];

				//Generate description
				if (empty ($category->catdescription)) {
					$category->catdescription = JText::_( 'NO DESCRIPTION' );
				} else {
					//execute plugins
					$category->text		= $category->catdescription;
					$category->title 	= $category->catname;
					JPluginHelper::importPlugin('content');
					$results = $mainframe->triggerEvent( 'onPrepareContent', array( &$category, &$params, 0 ));
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
					$category->linktarget = JRoute::_('index.php?view=categoryevents&id='.$category->slug.'&task=archive');
				} else {
					$category->linktarget = JRoute::_('index.php?view=categoryevents&id='.$category->slug);
				}

				$k = 1 - $k;
			}

		}

		return $this->_categories;
	}

	/**
	 * Total nr of Categories
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
	 * Method to get a pagination object for the events
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
	 * Method to get the Categories events
	 *
	 * @access public
	 * @return array
	 */
	function &getEventdata( $id )
	{
		global $mainframe;

		$params 	= & $mainframe->getParams('com_redevent');

		// Lets load the content
		$query = $this->_buildDataQuery( $id );
		$this->_data = $this->_getList( $query, 0, $params->get('detcat_nr') );
    $this->_data = $this->_getEventsCategories($this->_data);

		return $this->_data;
	}

	/**
	 * Method get the event query
	 *
	 * @access private
	 * @return array
	 */
	function _buildDataQuery( $id )
	{
		$user		= & JFactory::getUser();
		$aid		= (int) $user->get('aid');
		$id			= (int) $id;
		
		$task 		= JRequest::getWord('task');

		$where = ' WHERE top.id = ' . $this->_db->Quote($id) . ' AND c.lft BETWEEN top.lft AND top.rgt ';
		// First thing we need to do is to select only the requested events
		if ($task == 'archive') {
			$where .= ' AND a.published = -1 ';
		} else {
			$where .= ' AND a.published = 1 ';
		}

		//Get Events from Category
		$query = 'SELECT a.*, a.id as event_id, x.*, l.venue, l.city, l.state, l.url, c.catname, c.id AS catid,'
        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
        . ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug, '
        . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug '
				. ' FROM #__redevent_categories AS top, #__redevent_events AS a '
				. ' LEFT JOIN #__redevent_event_venue_xref AS x on x.eventid = a.id'
				. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
        . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
        . ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
				. $where
				. ' AND c.access <= '.$aid
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
		$user		= & JFactory::getUser();
		$gid 		= (int) $user->get('aid');
		
		//check archive task and ensure that only categories get selected if they contain a published/archived event
		$task 	= JRequest::getWord('task');
		if($task == 'archive') {
			$eventstate = ' AND a.published = -1';
		} else {
			$eventstate = ' AND a.published = 1';
		}

    //get categories
    // TODO: it works, but maybe there could be a simpler solution...
    $query = 'SELECT top.*, COUNT( sub.catid ) AS assignedevents,'
        . ' CASE WHEN CHAR_LENGTH(top.alias) THEN CONCAT_WS(\':\', top.id, top.alias) ELSE top.id END as slug'
        . ' FROM #__redevent_categories AS top'
        . ' INNER JOIN ( '
        . '   SELECT DISTINCT top.id as catid, x.id '
        . '   FROM #__redevent_categories AS top'
        . '   INNER JOIN #__redevent_categories AS c ON c.lft BETWEEN top.lft AND top.rgt'
        . '   INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.category_id = c.id'
        . '   INNER JOIN #__redevent_events AS a ON xcat.event_id = a.id'
        . '   INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id'
        . '   WHERE c.published = 1'
        . '   AND c.access <= '.$gid
        .     $eventstate
        . ' ) AS sub ON sub.catid = top.id' // itself and descendants
        . ' GROUP BY top.id'
        . ' ORDER BY top.ordering'
        ;

		return $query;
	}
	
 /**
   * adds categories property to event rows
   *
   * @param array $rows of events
   * @return array
   */
  function _getEventsCategories($rows)
  {
    for ($i=0, $n=count($rows); $i < $n; $i++) {
      $query =  ' SELECT c.id, c.catname, '
              . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug '
              . ' FROM #__redevent_categories as c '
              . ' INNER JOIN #__redevent_event_category_xref as x ON x.category_id = c.id '
              . ' WHERE c.published = 1 '
              . '   AND x.event_id = ' . $this->_db->Quote($rows[$i]->event_id)
              . ' ORDER BY c.ordering'
              ;
      $this->_db->setQuery( $query );

      $rows[$i]->categories = $this->_db->loadObjectList();
    }

    return $rows;   
  }
}
?>