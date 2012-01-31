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
defined('_JEXEC') or die ('Restricted access');

jimport('joomla.application.component.model');

/**
 * redevent Component calendar Model
 *
 * @package Joomla
 * @subpackage redevent
 * @since		0.9
 */
class RedeventModelCalendar extends JModel
{
    /**
     * Events data array
     *
     * @var array
     */
    var $_data = null;

    /**
     * Tree categories data array
     *
     * @var array
     */
    var $_categories = null;
    
    var $_topcat = null;

    /**
     * Events total
     *
     * @var integer
     */
    var $_total = null;

    /**
     * The reference date
     *
     * @var int unix timestamp
     */
    var $_date = 0;

    /**
     * Constructor
     *
     * @since 0.9
     */
    function __construct()
    {
        parent::__construct();

        $app = & JFactory::getApplication();

        $this->setdate(time());
    }

    function setdate($date)
    {
        $this->_date = $date;
    }

    /**
     * Method to get the events
     *
     * @access public
     * @return array
     */
    function &getData()
    {
      // Lets load the content if it doesn't already exist
      if ( empty($this->_data))
      {
        $query = $this->_buildQuery();
        $this->_data = $this->_getList( $query );
        
        // we have the events happening this month. We have to create occurences for each day for multiple day events.
        $multi = array();        
        foreach($this->_data AS $item)
        {
          $item->categories = $this->getCategories($item->id);
          
          if(!is_null($item->enddates) ) 
          {
            if( $item->enddates != $item->dates) 
            {              	
              $day = $item->start_day;

              for ($counter = 0; $counter <= $item->datediff-1; $counter++)
              {
                $day++;

                //next day:
                $nextday = mktime(0, 0, 0, $item->start_month, $day, $item->start_year);
                	
                //ensure we only generate days of current month in this loop
                if (strftime('%m', $this->_date) == strftime('%m', $nextday)) {
                  $multi[$counter] = clone $item;
                  $multi[$counter]->dates = strftime('%Y-%m-%d', $nextday);

                  //add generated days to data
                  $this->_data = array_merge($this->_data, $multi);
                }
                //unset temp array holding generated days before working on the next multiday event
                unset($multi);
              }
            }
          }

          //remove events without categories (users have no access to them)
          if (empty($item->categories)) {
            unset($item);
            continue;
          }

          //remove event with a start date from previous months
          if ( strftime('%m', strtotime($item->dates)) != strftime('%m', $this->_date) ) {
            array_shift($this->_data);
          }
        }
      }

      return $this->_data;
    }

    /**
     * Build the query
     *
     * @access private
     * @return string
     */
    function _buildQuery()
    {
			$acl = UserAcl::getInstance();
			
			$gids = $acl->getUserGroupsIds();
			if (!is_array($gids) || !count($gids)) {
				$gids = array(0);
			}
			$gids = implode(',', $gids);
		
			// Get the WHERE clauses for the query
			$where = $this->_buildWhere();

			//Get Events from Database
			$query = ' SELECT DATEDIFF(x.enddates, x.dates) AS datediff, a.id, x.id AS xref, x.dates, x.enddates, x.times, x.endtimes, '
			       . ' a.title, x.venueid as locid, a.datdescription, a.created, l.venue, l.city, l.state, l.url, l.street, l.country, x.featured, '
			       . ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title, '
			       . ' DAYOFMONTH(x.dates) AS start_day, YEAR(x.dates) AS start_year, MONTH(x.dates) AS start_month,'
			       . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug,'
			       . ' CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug, '
			       . ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug'
			       . ' FROM #__redevent_events AS a'
			       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id '
			       . ' INNER JOIN #__redevent_venues AS l ON l.id = x.venueid'
		         . ' LEFT JOIN #__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id'
		         . ' LEFT JOIN #__redevent_venues_categories AS vc ON xvcat.category_id = vc.id'
			       . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
			       . ' INNER JOIN #__redevent_categories AS cat ON cat.id = xcat.category_id'
			       . ' LEFT JOIN #__redevent_groups_venues AS gv ON gv.venue_id = l.id AND gv.group_id IN ('.$gids.')'
			       . ' LEFT JOIN #__redevent_groups_venues_categories AS gvc ON gvc.category_id = vc.id AND gvc.group_id IN ('.$gids.')'
			       . ' LEFT JOIN #__redevent_groups_categories AS gc ON gc.category_id = cat.id AND gc.group_id IN ('.$gids.')'
			       . $where
			       . ' GROUP BY x.id '
			       . ' ORDER BY x.dates, x.times'
			;

			return $query;
    }

    /**
     * Method to build the WHERE clause
     *
     * @access private
     * @return array
     */
    function _buildWhere()
    {
        $app = & JFactory::getApplication();

        // Get the paramaters of the active menu item
        $params = & $app->getParams();
        
        $task = JRequest::getWord('task');

        $where = array();
        // First thing we need to do is to select only the published events
        if ($task == 'archive')
        {
            $where[] = ' x.published = -1 ';
        } else
        {
            $where[] = ' x.published = 1 ';
        }
        
        // category must be published too
        $where[] = ' cat.published = 1 ';
        

        // only select events within specified dates. (chosen month)
        $monthstart = mktime(0, 0, 1, strftime('%m', $this->_date), 1, strftime('%Y', $this->_date));
        $monthend = mktime(0, 0, -1, strftime('%m', $this->_date)+1, 1, strftime('%Y', $this->_date));
        
        $where[] = ' ((x.dates BETWEEN (\''.strftime('%Y-%m-%d', $monthstart).'\') AND (\''.strftime('%Y-%m-%d', $monthend).'\'))'
                 . ' OR (x.enddates BETWEEN (\''.strftime('%Y-%m-%d', $monthstart).'\') AND (\''.strftime('%Y-%m-%d', $monthend).'\')))';
    
        // check if a category is specified
        $topcat = $params->get('topcat', '');
        if (is_numeric($topcat) && $topcat) 
        {
          // get children categories
          $query = ' SELECT lft, rgt FROM #__redevent_categories WHERE id = '. $this->_db->Quote($topcat);
          $this->_db->setQuery($query);
          $obj = $this->_db->loadObject();
          if ($obj) {
            $query = ' SELECT id FROM #__redevent_categories '
                   . ' WHERE lft >= '. $this->_db->Quote($obj->lft)
                   . ' AND rgt <= '. $this->_db->Quote($obj->rgt)
                   ;
            $this->_db->setQuery($query);
            $cats = $this->_db->loadResultArray();
            if ($cats) {
               $where[] = ' xcat.category_id IN ('. implode(', ', $cats) .')';
            }            
          }
          else {
            JError::raiseWarning(0, JText::_('COM_REDEVENT_CATEGORY_NOT_FOUND'));
          }
        }
        
        // acl
				$where[] = ' (l.private = 0 OR gv.id IS NOT NULL) ';
		    $where[] = ' (cat.private = 0 OR gc.id IS NOT NULL) ';
		    $where[] = ' (vc.private = 0 OR vc.private IS NULL OR gvc.id IS NOT NULL) ';

        return ' WHERE '.implode(' AND ', $where);
    }

    /**
     * Method to get the Categories
     *
     * @access public
     * @return integer
     */
    function getCategories($id)
    {
      $query =  ' SELECT c.id, c.catname, c.color, '
            . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug '
            . ' FROM #__redevent_categories as c '
            . ' INNER JOIN #__redevent_event_category_xref as x ON x.category_id = c.id '
            . ' WHERE c.published = 1 '
            . '   AND x.event_id = ' . $this->_db->Quote((int)$id)
            . ' ORDER BY c.ordering'
            ;
      $this->_db->setQuery( $query );
      
      $this->_categories = $this->_db->loadObjectList();

      return $this->_categories;
    }
}
