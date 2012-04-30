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
 * EventList Component Events Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		0.9
 */
class RedEventModelEvents extends JModel
{
	/**
	 * Events data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Events total
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

    $limit      = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( $option.'.events.limitstart', 'limitstart', 0, '', 'int' );
		
		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}

	/**
	 * Method to get event item data
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
			$this->_data = $this->_getList($query, $pagination->limitstart, $pagination->limit);
      $this->_data = $this->_categories($this->_data);
		}

		return $this->_data;
	}

	/**
	 * Total nr of events
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
	 * Build the query
	 *
	 * @access private
	 * @return string
	 */
	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT a.*, cat.checked_out AS cchecked_out, cat.catname, u.email, u.name AS author, u2.name as editor, x.id AS xref'
					. ' FROM #__redevent_events AS a'
          . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
					. ' LEFT JOIN #__redevent_categories AS cat ON cat.id = xcat.category_id'
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id'
					. ' LEFT JOIN #__redevent_venues AS loc ON loc.id = x.venueid'
					. ' LEFT JOIN #__users AS u ON u.id = a.created_by'
          . ' LEFT JOIN #__users AS u2 ON u2.id = a.modified_by'
					. $where
					. ' GROUP BY a.id'
					. $orderby
					;
		return $query;
	}

	/**
	 * Build the order clause
	 *
	 * @access private
	 * @return string
	 */
	function _buildContentOrderBy()
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.events.filter_order', 'filter_order', 'a.title', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.events.filter_order_Dir', 'filter_order_Dir', '', 'word' );

		$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.', a.title';

		return $orderby;
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	function _buildContentWhere()
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$filter_state = $mainframe->getUserStateFromRequest( $option.'.filter_state', 'filter_state', '', 'word' );
		$filter       = $mainframe->getUserStateFromRequest( $option.'.filter', 'filter', '', 'int' );
		$search       = $mainframe->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
		$search       = $this->_db->getEscaped( trim(JString::strtolower( $search ) ) );

		$where = array();

		if ($filter_state) 
		{
			if ($filter_state == 'P') {
				$where[] = 'a.published = 1';
			} else if ($filter_state == 'U') {
				$where[] = 'a.published = 0';
			} else {
				$where[] = 'a.published >= 0';
			}
		} else {
			$where[] = 'a.published >= 0';
		}

		if ($search && $filter == 1) {
			$where[] = ' LOWER(a.title) LIKE \'%'.$search.'%\' ';
		}

		if ($search && $filter == 2) {
			$where[] = ' LOWER(loc.venue) LIKE \'%'.$search.'%\' ';
		}

		if ($search && $filter == 3) {
			$where[] = ' LOWER(loc.city) LIKE \'%'.$search.'%\' ';
		}

		if ($search && $filter == 4) {
			$where[] = ' LOWER(cat.catname) LIKE \'%'.$search.'%\' ';
		}

		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

		return $where;
	}
	
	/**
	 * adds categories property to event rows
	 *
	 * @param array $rows of events
	 * @return array
	 */
	function _categories($rows)
	{
		for ($i=0, $n=count($rows); $i < $n; $i++) {
			$query =  ' SELECT c.id, c.catname, c.checked_out '
							. ' FROM #__redevent_categories as c '
							. ' INNER JOIN #__redevent_event_category_xref as x ON x.category_id = c.id '
							. ' WHERE c.published = 1 '
							. '   AND x.event_id = ' . $this->_db->Quote($rows[$i]->id)
							. ' ORDER BY c.ordering'
							;
			$this->_db->setQuery( $query );

			$rows[$i]->categories = $this->_db->loadObjectList();
		}

    return $rows;		
	}

	/**
	 * Method to (un)publish a event
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user 	=& JFactory::getUser();
		$userid = (int) $user->get('id');

		if (count( $cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__redevent_events'
				. ' SET published = '. (int) $publish
				. ' WHERE id IN ('. $cids .')'
				. ' AND ( checked_out = 0 OR ( checked_out = ' .$userid. ' ) )'
			;

			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			else {
				$query = 'UPDATE #__redevent_event_venue_xref'
					. ' SET published = '. (int) $publish
					. ' WHERE eventid IN ('. $cids .')'
					. '   AND published > -1 ' // do not change state of archived session
				;
				$this->_db->setQuery( $query );

				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}
	}
	
	/**
	 * archive past xrefs
	 * 
	 * @param $event_ids
	 * @return unknown_type
	 */
	function archive($event_ids = array())
	{
		if (!count($event_ids)) {
			return true;
		}

		$db = & $this->_db;
		
    $nulldate = '0000-00-00';
      
		// update xref to archive
		$query = ' UPDATE #__redevent_event_venue_xref AS x '
		. ' SET x.published = -1 '
		. ' WHERE DATE_SUB(NOW(), INTERVAL 1 DAY) > (IF (x.enddates <> '.$nulldate.', x.enddates, x.dates))'
		. '   AND x.eventid IN (' . implode(', ', $event_ids) . ')'
		;
		$db->SetQuery( $query );
		$db->Query();

		// update events to archive (if no more published xref)
		$query = ' UPDATE #__redevent_events AS e '
		. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id AND x.published <> -1 '
		. ' SET e.published = -1 '
		. ' WHERE x.id IS NULL '
		. '   AND e.id IN (' . implode(', ', $event_ids) . ')'
		;
		$db->SetQuery( $query );
		$db->Query();
		return true;
	}

	/**
	 * Method to remove a event
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count( $cid ))
		{
			// first, we don't delete events that have attendees, to preserve records integrity. admin should delete attendees separately first
			$cids = implode( ',', $cid );
			
			$query = ' SELECT e.id, e.title ' 
			       . ' FROM #__redevent_events AS e '
			       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
			       . ' INNER JOIN #__redevent_register AS r ON r.xref = x.id ' 
			       . ' WHERE e.id IN ('. $cids .')'
			       ;
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();
			if ($res || count($res)) 
			{
				// can't delete
				$this->setError(Jtext::_('COM_REDEVENT_ERROR_EVENT_REMOVE_EVENT_HAS_ATTENDEES'));
				return false;
			}
			
			$query = ' DELETE e.*, xcat.*, x.*, rp.*, r.*, sr.*, spg.* '
			       . ' FROM #__redevent_events AS e '
			       . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
			       . ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
			       . ' LEFT JOIN #__redevent_repeats AS rp on rp.xref_id = x.id '
			       . ' LEFT JOIN #__redevent_recurrences AS r on r.id = rp.recurrence_id '
			       . ' LEFT JOIN #__redevent_sessions_roles AS sr on sr.xref = x.id '
			       . ' LEFT JOIN #__redevent_sessions_pricegroups AS spg on spg.xref = x.id '
					   . ' WHERE e.id IN ('. $cids .')'
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
	 * Retrieve a list of events, venues and times
	 */
	public function getEventVenues() 
	{
	  $events_id = array();
	  foreach ((array) $this->getData() as $e) {
	    $events_id[] = $e->id;
	  }
	  if (empty($events_id)) {
	    return false;
	  }
	  
		$db = JFactory::getDBO();
		$q = ' SELECT x.eventid, COUNT(*) AS total, SUM(CASE WHEN x.published = 1 THEN 1 ELSE 0 END) as published,   '
		   . ' SUM(CASE WHEN x.published = 0 THEN 1 ELSE 0 END) as unpublished,'
		   . ' SUM(CASE WHEN x.published = -1 THEN 1 ELSE 0 END) as archived,'
		   . ' SUM(CASE WHEN x.featured = 1 THEN 1 ELSE 0 END) as featured'
		   . ' FROM #__redevent_event_venue_xref AS x '
       . ' WHERE x.eventid IN ('. implode(', ', $events_id) .')'
       . ' GROUP BY x.eventid '
       ;
		$db->setQuery($q);
		$datetimes = $db->loadObjectList();
		$ardatetimes = array();
		foreach ((array) $datetimes as $key => $datetime) {
			$ardatetimes[$datetime->eventid] = $datetime;
		}
		
		return $ardatetimes;
	}
	

	/**
	 * Get a option list of all categories
	 */
	public function getCategoriesOptions() 
	{
		return ELAdmin::getCategoriesOptions();
	}
	
	/**
	 * Get a option list of all categories
	 */
	public function getVenuesOptions() 
	{
	 $query = ' SELECT v.id, v.venue '
           . ' FROM #__redevent_venues AS v '
           . ' ORDER BY v.venue'
           ;
    $this->_db->setQuery($query);
    
    $results = $this->_db->loadObjectList();
    
    $options = array();
    foreach((array) $results as $r)
    {
      $options[] = JHTML::_('select.option', $r->id, $r->venue);
    }
		return $options;
	}
	
	public function exportEvents($categories = null, $venues = null)
	{
		$where = array();
		
		if ($categories) {
			$where[] = " (xc.category_id = ". implode(" OR xc.category_id = ", $categories).') ';
		}
		if ($venues) {
			$where[] = " (x.venueid = ". implode(" OR x.venueid = ", $venues).') ';
		}
		
		if (count($where)) {
			$where = ' WHERE '.implode(' AND ', $where);
		}
		else {
			$where = '';
		}
		
		// custom fields
		$customs = array();
		$xcustoms = array();
		$fields = $this->_getEventsCustomFieldsColumns();
		$replace = array();
		foreach ((array) $fields AS $f)
		{
			$customs[] = 'e.'.$f->col;
			$replace[$f->name.'#'.$f->tag] = $f->col;
		}
		$fields = $this->_getSessionsCustomFieldsColumns();
		foreach ((array) $fields AS $f)
		{
			$xcustoms[] = 'x.'.$f->col;
			$replace[$f->name.'#'.$f->tag] = $f->col;
		}
		
		$query = ' SELECT e.id, e.title, e.alias, '
		       . '    e.summary, e.datdescription, e.details_layout, e.meta_description, e.meta_keywords, '
		       . '    e.datimage, e.published, e.registra, e.unregistra, '
		       . '    e.notify, e.notify_subject, e.notify_body, e.redform_id, e.juser, '
		       . '    e.notify_on_list_body, e.notify_off_list_body, e.notify_on_list_subject, e.notify_off_list_subject, '
		       . '    e.show_names, e.notify_confirm_subject, e.notify_confirm_body, '
		       . '    e.review_message, e.confirmation_message, e.activate, e.showfields, '
		       . '    e.submission_types, e.course_code, e.submission_type_email, e.submission_type_external, '
		       . '    e.submission_type_phone, e.submission_type_webform, e.show_submission_type_webform_formal_offer, '
		       . '    e.submission_type_webform_formal_offer, e.max_multi_signup, e.submission_type_formal_offer, '
		       . '    e.submission_type_formal_offer_subject, e.submission_type_formal_offer_body, '
		       . '    e.submission_type_email_body, e.submission_type_email_subject, e.submission_type_email_pdf, '
		       . '    e.submission_type_formal_offer_pdf, e.send_pdf_form, e.pdf_form_data, e.paymentaccepted, '
		       . '    e.paymentprocessing, e.enable_ical,  '
		       . (count($customs) ? implode(', ', $customs).', ' : '' )
		       . '    x.title as session_title, x.alias as session_alias, x.id AS xref, '
		       . '    x.dates, x.enddates, x.times, x.endtimes, x.registrationend, '
		       . '    x.note AS session_note, x.details AS session_details, x.icaldetails AS session_icaldetails, x.maxattendees, x.maxwaitinglist, x.course_credit, '
		       . '    x.featured, x.external_registration_url, x.published as session_published, '
		       . '    u.name as creator_name, u.email AS creator_email, '
		       . (count($xcustoms) ? implode(', ', $xcustoms).', ' : '' )
		       . '    v.venue, v.city '
		       . ' FROM #__redevent_events AS e '
		       . ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
		       . ' LEFT JOIN #__redevent_venues AS v ON v.id = x.venueid '
		       . ' LEFT JOIN #__redevent_event_category_xref AS xc ON xc.event_id = e.id '
		       . ' LEFT JOIN #__users AS u ON e.created_by = u.id '
		       . $where
		       . ' GROUP BY x.id, e.id '
		       . ' ORDER BY e.id, x.dates '
		       ;
    $this->_db->setQuery($query);
    
    $results = $this->_db->loadAssocList();
    
    $query = ' SELECT xc.event_id, GROUP_CONCAT(c.catname SEPARATOR "#!#") AS categories_names '
		      . ' FROM #__redevent_event_category_xref AS xc '
		      . ' LEFT JOIN #__redevent_categories AS c ON c.id = xc.category_id '
		      . ' GROUP BY xc.event_id '
		      ;
    $this->_db->setQuery($query);
    
    $cats = $this->_db->loadObjectList('event_id');
    
    $query = ' SELECT spg.xref, '
           . ' GROUP_CONCAT(spg.price SEPARATOR "#!#") AS prices, '
           . ' GROUP_CONCAT(pg.name SEPARATOR "#!#") AS pricegroups_names '
		       . ' FROM #__redevent_sessions_pricegroups AS spg '
		       . ' INNER JOIN #__redevent_pricegroups AS pg ON pg.id = spg.pricegroup_id '
		       . ' GROUP BY spg.xref '
		       ;
    $this->_db->setQuery($query);
    
    $pgs = $this->_db->loadObjectList('xref');

    foreach ($results as $k => $r)
    {
    	if (isset($cats[$r['id']])) 
    	{
    		$results[$k]['categories_names'] = $cats[$r['id']]->categories_names;
    	}
    	else
    	{
    		$results[$k]['categories_names'] = null;    		
    	}
    	
    	if ($r['xref'] && isset($pgs[$r['xref']])) 
    	{
    		$results[$k]['prices'] = $pgs[$r['xref']]->prices;
    		$results[$k]['pricegroups_names'] = $pgs[$r['xref']]->pricegroups_names;
    	}
    	else
    	{
    		$results[$k]['prices'] = null;
    		$results[$k]['pricegroups_names'] = null;    		
    	}
    	
    	foreach ($r as $col => $val)
    	{
	    	if ($tag = array_search($col, $replace)) {
	    		$results[$k]['custom_'.$tag] = $results[$k][$col];
	    		unset($results[$k][$col]);
	    	}
    	}
    }
    
    return $results;
	}
		
	function _getEventsCustomFieldsColumns()
	{
		$query = ' SELECT CONCAT("custom", id) as col, name, tag ' 
		       . ' FROM #__redevent_fields ' 
		       . ' WHERE object_key = ' . $this->_db->Quote('redevent.event');
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}
		
	function _getSessionsCustomFieldsColumns()
	{
		$query = ' SELECT CONCAT("custom", id) as col, name, tag ' 
		       . ' FROM #__redevent_fields ' 
		       . ' WHERE object_key = ' . $this->_db->Quote('redevent.xref');
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}
}//Class end