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
 * EventList Component Categories Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelCategories extends JModel
{
  /**
   * category to use as a base for queries
   * 
   * @var unknown_type
   */
  var $_parent = null;
  
	/**
	 * Categories data array
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

		// Get the paramaters of the active menu item
		$params = & $mainframe->getParams();
	
    if ($params->get('parentcategory', 0)) {
      $this->setParent($params->get('parentcategory', 0));
    }
    
		//get the number of events from database
		$limit			= JRequest::getInt('limit', $params->get('cat_num'));
		$limitstart		= JRequest::getInt('limitstart');

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
		$elsettings = & redEVENTHelper::config();
		
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit') );

			$k = 0;
			$count = count($this->_data);
			for($i = 0; $i < $count; $i++)
			{
				$category =& $this->_data[$i];

				if ($category->image != '') {

					$attribs['width'] = $elsettings->imagewidth;
					$attribs['height'] = $elsettings->imagehight;

					$category->image = JHTML::image('images/stories/'.$category->image, $category->catname, $attribs);
				} else {
					// $category->image = JHTML::image('components/com_redevent/assets/images/noimage.png', $category->catname);
				}
				
				//create target link
				$task 	= JRequest::getWord('task');
				
				$category->linktext = $task == 'archive' ? JText::_( 'SHOW ARCHIVE' ) : JText::_( 'SHOW EVENTS' );

				if ($task == 'archive') {
					$category->linktarget = JRoute::_('index.php?option=com_redevent&view=categoryevents&id='.$category->slug.'&task=archive');
				} else {
					$category->linktarget = JRoute::_('index.php?option=com_redevent&view=categoryevents&id='.$category->slug);
				}

				$k = 1 - $k;
			}

		}

		return $this->_data;
	}

	/**
	 * Total nr of Venues
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
	 * Method to load the Categories
	 *
	 * @access private
	 * @return array
	 */
	function _buildQuery()
	{
		//initialize some vars
    $mainframe = &JFactory::getApplication();
    $params   = & $mainframe->getParams('com_redevent');
		$user		= & JFactory::getUser();
		$gid		= (int) $user->get('aid');

		//check archive task and ensure that only categories get selected if they contain a published/archived event
		$task 	= JRequest::getVar('task', '', '', 'string');
		$eventstate = array();
		if($task == 'archive') {
		  $eventstate[] = 'x.published = -1';
		} else {
      $eventstate[] = 'x.published = 1';
		}
    if ($params->get('display_all_categories', 1)) {
      $eventstate[] = ' x.id IS NULL ';
    }
    $eventstate = ' AND ('.implode(' OR ', $eventstate).')';
		
				
		//get categories
      $query = ' SELECT c.*, COUNT(x.id) AS assignedevents, '
          . '   CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug '
          . ' FROM #__redevent_categories AS c '
          . ' LEFT JOIN #__redevent_categories AS child ON child.lft BETWEEN c.lft AND c.rgt '
          . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.category_id = child.id '
          . ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = xcat.event_id '
          . ' WHERE child.published = 1 '
          . '   AND child.access <= '.$gid
          .     $eventstate
          ;  
      
      if ($this->_parent) {
        $query .= ' AND c.parent_id = '. $this->_db->Quote($this->_parent->id);      
      }
      
      $query .= '   GROUP BY c.id '; 
      
		return $query;
	}
}
?>