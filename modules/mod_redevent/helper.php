<?php
/**
 * @version 0.9 $Id$
 * @package Joomla
 * @subpackage RedEvent
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENCE.php
 * RedEvent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * RedEvent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with RedEvent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'route.php');

/**
 * RedEvent Module helper
 *
 * @package Joomla
 * @subpackage RedEvent Module
 * @since		0.9
 */
class modRedEventHelper
{

	/**
	 * Method to get the events
	 *
	 * @access public
	 * @return array
	 */
	public function getList(&$params)
	{
		$mainframe = &JFactory::getApplication();

		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		$user_gid	= (int) max($user->getAuthorisedViewLevels());	
		
		switch ($params->get('ordering', 0))
		{
			case 5:
				$order = ' ORDER BY a.title DESC, x.title DESC';
				break;
			case 4:
				$order = ' ORDER BY a.title ASC, x.title ASC';
				break;
			case 3:
				$order = ' ORDER BY x.id DESC';
				break;
			case 2:
				$order = ' ORDER BY x.id ASC';
				break;
			case 1:
				$order = ' ORDER BY x.dates DESC, x.times DESC ';
				break;
			default:
			case 0:
				$order = ' ORDER BY x.dates ASC, x.times ASC ';
			break;
		}


		$where = array();

		$where[] = 'c.access <= '.$user_gid;
		
		$type = $params->get( 'type', '0' );
		if ($type == 0) // published
		{
			$where[] = 'x.published = 1';
		} 
		else if ($type == 1) // upcoming
		{
			$offset = (int) $params->get( 'dayoffset', '0' );
			$date = $offset ? 'now +'.$offset.' days' : 'now';
			$ref = strftime('%Y-%m-%d %H:%M', strtotime($date));
			$where[] = 'x.published = 1 AND (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > '.$db->Quote($ref);
		} 
		else if ($type == 2) // archived
		{
			$where[] = 'x.published = -1';
		} 
		else if ($type == 3) // open dates
		{
			$where[] = 'x.dates = 0';
		}
		else if ($type == 4) // just passed dates
		{
			$date = $offset ? 'now -'.$offset.' days' : 'now';
			$ref = strftime('%Y-%m-%d %H:%M', strtotime($date));
			$where[] = 'x.published = 1 AND (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) < '.$db->Quote($ref);
		}

		$catid 	= trim( $params->get('catid') );
		$venid 	= trim( $params->get('venid') );

		if ($catid)
		{
			$ids = explode( ',', $catid );
			JArrayHelper::toInteger( $ids );
			$where[] = ' c.id IN (' . implode( ',', $ids ) . ')';
		}
		if ($venid)
		{
			$ids = explode( ',', $venid );
			JArrayHelper::toInteger( $ids );
			$where[] = ' l.id IN (' . implode( ',', $ids ) . ')';
		}
		
		if ($params->get('featuredonly', 0) == 1) {
			$where[] = ' x.featured = 1 ';			
		}
		
		if ($params->get('showrecurring', 1) == 0) {
			$where[] = ' r.count = 0 ';			
		}
		
		if ($params->get('showsessions', 1) == 0) {
			$groupby = ' GROUP BY a.id ';		
		}
		else {
			$groupby = ' GROUP BY x.id ';		
		}

		//get $params->get( 'count', '2' ) nr of datasets
		$query = 'SELECT x.*, x.id AS xref, a.*, l.venue, l.city, l.url, l.state, '
		    . ' CONCAT_WS(",", c.image) AS categories_images,'
		    . ' CASE WHEN CHAR_LENGTH(x.title) THEN x.title ELSE a.title END as session_title, '
		    . ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title, '
        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
        . ' CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug, '
		    . ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug '
				. ' FROM #__redevent_event_venue_xref AS x'
				. ' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid'
				. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
        . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
        . ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
        . ' LEFT JOIN #__redevent_repeats AS r ON r.xref_id = x.id '
				. ' WHERE '.implode(' AND ', $where)
				. $groupby
				. $order
				.' LIMIT '.(int)$params->get( 'count', '2' )
				;

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$rows = self::_categories($rows);
		
		$i		= 0;
		$lists	= array();
		$title_length = $params->get('cuttitle', '18');
		switch ($params->get('title_type', 0))
		{
			case 1:
				$title_type = 'session_title';
				break;
			case 2:
				$title_type = 'full_title';
				break;
			case 0:
			default:
				$title_type = 'title';
				break;
		}
		
		foreach ( $rows as $k => $row )
		{
			$rowtitle = $row->$title_type;
			//cut title
			$length = mb_strlen( $rowtitle, 'UTF-8' );
			if ($title_length && $length > $title_length) {
				$rows[$k]->title_short = mb_substr($rowtitle, 0, $title_length, 'UTF-8').'...';
			}
			else {
				$rows[$k]->title_short = $rowtitle;
			}
			// cut venue name
      $length = mb_strlen($row->venue, 'UTF-8');
      if ($title_length && $length > $title_length) {
        $rows[$k]->venue_short = mb_substr($row->venue, 0, $title_length, 'UTF-8').'...';
      }
      else {
        $rows[$k]->venue_short = $row->venue;
      }
      
			$rows[$k]->link		= JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref));
//			$rows[$k]->link		= JRoute::_('index.php?option=com_redevent&view=details&id='. $row->slug .'&xref='.$row->xref);
			$rows[$k]->dateinfo 	= modRedEventHelper::_builddateinfo($row, $params);
			$rows[$k]->city		= htmlspecialchars( $row->city, ENT_COMPAT, 'UTF-8' );
			$rows[$k]->venueurl 	= !empty( $row->url ) ? modRedEventHelper::_format_url($row->url) : JRoute::_(RedeventHelperRoute::getVenueEventsRoute($row->venueslug) , false);
		}

		return $rows;
	}

	/**
	 * Method to a formated and structured string of date infos
	 *
	 * @access public
	 * @return string
	 */
	protected function _builddateinfo($row, &$params)
	{
		if (!redEVENTHelper::isValidDate($row->dates)) {
			return JText::_('MOD_REDEVENT_OPEN_DATE');
		}
		$date 		= modRedEventHelper::_format_date($row->dates, $row->times, $params->get('formatdate', '%d.%m.%Y'));
		$enddate 	= redEVENTHelper::isValidDate($row->enddates) ? modRedEventHelper::_format_date($row->enddates, $row->endtimes, $params->get('formatdate', '%d.%m.%Y')) : null;
		$time		= ($row->times && $row->times != '00:00:00') ? modRedEventHelper::_format_date($row->dates, $row->times, $params->get('formattime', '%H:%M')) : null;
		$dateinfo	= '<span class="event-start">'.$date.'</span>';

		if ( isset($enddate) && $params->get('show_enddate', 1) && $row->dates != $row->enddates) {
			$dateinfo .= ' - <span class="event-end">'.$enddate.'</span>';
		}

		if ( isset($time) && $params->get('show_time', 1) ) {
			$dateinfo .= ' <span class="event-time">'.$time.'</span>';
		}

		return $dateinfo;
	}

	/**
	 * Method to get a valid url
	 *
	 * @access public
	 * @return string
	 */
	function _format_url($url)
	{
		if(!empty($url) && strtolower(substr($url, 0, 7)) != "http://") {
        	$url = 'http://'.$url;
        }
		return $url;
	}

	/**
	 * Method to format date information
	 *
	 * @access public
	 * @return string
	 */
	function _format_date($date, $time, $format)
	{
		//format date
		$date = strftime($format, strtotime( $date.' '.$time ));

		return $date;
	}
	

	/**
   * adds categories property to event rows
   *
   * @param array $rows of events
   * @return array
   */
  function _categories($rows)
  {
  	$db = &Jfactory::getDBO();
		$acl = &UserAcl::getInstance();		
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);
		
    for ($i=0, $n=count($rows); $i < $n; $i++) 
    {
      $query =  ' SELECT c.id, c.catname, c.color, '
              . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug '
              . ' FROM #__redevent_categories as c '
              . ' INNER JOIN #__redevent_event_category_xref as x ON x.category_id = c.id '
	            . '  LEFT JOIN #__redevent_groups_categories AS gc ON gc.category_id = c.id '
              . ' WHERE c.published = 1 '
              . '   AND x.event_id = ' . $db->Quote($rows[$i]->id)
              . '   AND (c.private = 0 OR gc.group_id IN ('.$gids.')) '
              . ' GROUP BY c.id '
              . ' ORDER BY c.ordering'
              ;
      $db->setQuery( $query );

      $rows[$i]->categories = $db->loadObjectList();
    }

    return $rows;   
  }
  
  /**
   * returns code for list of cats separated by comma
   * 
   * @param array $categories
   * @return string html
   */
  public function displayCats($categories)
  {
  	$res = array();
  	foreach ($categories as $c) {
  		$res[] = $c->catname;
  	}
  	return implode(", ", $res);
  }
  
  /**
   * return custom fields indexed by id
   * 
   * @return array
   */
  public function getCustomFields()
  {
  	$db = &Jfactory::getDBO();
  	$query = ' SELECT f.id, f.* FROM #__redevent_fields AS f';
  	$db->setQuery($query);
  	return $db->loadObjectList('id');
  }
}