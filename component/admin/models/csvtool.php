<?php
/**
 * @version 1.0 $Id: group.php 298 2009-06-24 07:42:35Z julien $
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

//no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * redEvent Component csvtool Model
 *
 * @package Joomla
 * @subpackage redEvent
 * @since		2.0
 */
class RedEventModelcsvtool extends JModel
{
	
	/**
	 * Return forms asssigned to redevents as options
	 * 
	 * @return array
	 */
	function getFormOptions()
	{
		$query = ' SELECT DISTINCT f.id AS value, f.formname AS text ' 
		       . ' FROM #__redevent_events AS e ' 
		       . ' INNER JOIN #__rwf_forms AS f ON f.id = e.redform_id ' 
		       . ' ORDER BY f.formname ASC '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}
	
	/**
	 * get events as options according to filters
	 * 
	 * @param int category_id
	 * @param int venue_id
	 * @return array
	 */
	function getEventOptions($category_id = 0, $venue_id = 0)
	{
		$query = ' SELECT e.id, e.title ' 
		       . ' FROM #__redevent_events AS e '
		       . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
		       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
		       ;
		$where = array();
		$where[] = ' e.published > -1 ';
		$where[] = ' x.published > -1 ';
		
		if ($category_id)	{
			$where[] = ' xcat.category_id = '.$category_id;
		}
		if ($venue_id)	{
			$where[] = ' x.venueid = '.$venue_id;
		}
		
		$query .= ' WHERE '.implode(' AND ', $where);
		
		$query .= ' GROUP BY e.id ';
		$query .= ' ORDER BY e.title ';
		
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		
		return $res;
	}
		
	function getFields($form_id)
	{
		$rfcore = new RedFormCore();
		return $rfcore->getFields($form_id); 
	}
	

/**
	 * Method to get the registered users
	 *
	 * @access	public
	 * @return	object
	 * @since	0.9
	 * @todo Complete CB integration
	 */
	function getRegisters($form_id, $events = null, $category_id = 0, $venue_id = 0, $state_filter = 0)
	{	  
		// first, get all submissions			
		$query = ' SELECT e.title, e.course_code, x.id as xref, x.dates, v.venue, '
		        . ' r.*, r.waitinglist, r.confirmed, r.confirmdate, r.submit_key, u.name '
						. ' FROM #__redevent_register AS r '
						. ' INNER JOIN #__rwf_submitters AS s ON s.id = r.sid '
						. ' INNER JOIN #__redevent_event_venue_xref AS x ON x.id = r.xref '
						. ' INNER JOIN #__redevent_events AS e ON e.id = x.eventid '
						. ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
						. ' INNER JOIN #__redevent_venues AS v ON v.id = x.venueid '
						. ' LEFT JOIN #__users AS u ON r.uid = u.id '
						;
		$where = array();
		$where[] = ' r.confirmed = 1 ';
		$where[] = ' e.redform_id = '.$form_id;
		
		if ($events && count($events)) {
			$where[] = 'e.id in ('.implode(',', $events).')';
		}
		if ($category_id)	{
			$where[] = ' xcat.category_id = '.$category_id;
		}
		if ($venue_id)	{
			$where[] = ' x.venueid = '.$venue_id;
		}
		switch ($state_filter)
		{
			case 0:
				$where[] = ' x.published = 1 ';				
				break;
			case 1:
				$where[] = ' x.published = -1 ';				
				break;
			case 2:
				$where[] = ' x.published <> 0 ';				
				break;
		}
		$query .= ' WHERE '.implode(' AND ', $where);
		
		$query .= ' GROUP BY r.id ';
		$query .= ' ORDER BY e.title, x.dates ';
		
		$this->_db->setQuery($query);
		$submitters = $this->_db->loadObjectList();
		
		// get answers
		$sids = array();
		if (count($submitters)) 
		{
			foreach ($submitters as $s) 
			{
				$sids[] = $s->sid;
			}
		}
		
		$rfcore = new RedFormCore();
		$answers = $rfcore->getSidsAnswers($sids);
		
		// add answers to registers
		foreach ($submitters as $k => $s)
		{
			if (isset($answers[$s->sid])) {
				$submitters[$k]->answers = $answers[$s->sid];
			}
			else {
				$submitters[$k]->answers = null;
			}
		}
		return $submitters;
	}
}
?>