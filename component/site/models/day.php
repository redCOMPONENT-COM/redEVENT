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
 * EventList Component Day Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelDay extends RedeventModelBaseEventList
{
	

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();
			
		$rawday = JRequest::getInt('id', 0, 'request');
		$this->setDate($rawday);
	}

	/**
	 * Method to set the date
	 *
	 * @access	public
	 * @param	string
	 */
	function setDate($date)
	{
		$mainframe = & JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams('com_redevent');
		
		//0 means we have a direct request from a menuitem and without any parameters (eg: calendar module)
		if ($date == 0) {
			
			$dayoffset	= $params->get('days');
			$timestamp	= mktime(0, 0, 0, date("m"), date("d") + $dayoffset, date("Y"));
			$date		= strftime('%Y-%m-%d', $timestamp);
			
		//a valid date  has 8 characters
		} elseif (strlen($date) == 8) {
			
			$year 	= substr($date, 0, -4);
			$month	= substr($date, 4, -2);
			$tag	= substr($date, 6);
			
			//check if date is valid
			if (checkdate($month, $tag, $year)) {
				
				$date = $year.'-'.$month.'-'.$tag;
				
			} else {
				
				//date isn't valid raise notice and use current date
				$date = date('Ymd');
				JError::raiseNotice( 'SOME_ERROR_CODE', JText::_('INVALID DATE REQUESTED USING CURRENT') );
				
			}
			
		} else {
			//date isn't valid raise notice and use current date
			$date = date('Ymd');
			JError::raiseNotice( 'SOME_ERROR_CODE', JText::_('INVALID DATE REQUESTED USING CURRENT') );
			
		}

		$this->_date = $date;
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	function _buildEventListWhere()
	{
		global $mainframe;

		$user		= & JFactory::getUser();
		$gid		= (int) $user->get('aid');
		$nulldate 	= '0000-00-00';

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams();

		// First thing we need to do is to select only published events
		$where = ' WHERE x.published = 1';

		// Second is to only select events assigned to category the user has access to
		$where .= ' AND c.access <= '.$gid;
		
		// Third is to only select events of the specified day
		$where .= ' AND (\''.$this->_date.'\' BETWEEN (x.dates) AND (IF (x.enddates >= now(), x.enddates, \''.$nulldate.'\')) OR \''.$this->_date.'\' = x.dates)';

		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		 * for the filter onto the WHERE clause of the content item query.
		 */
		if ($params->get('filter'))
		{
      $filter     = $mainframe->getUserStateFromRequest('com_redevent.day.filter', 'filter', '', 'string');
      $filter_type  = $mainframe->getUserStateFromRequest('com_redevent.day.filter_type', 'filter_type', '', 'string');

			if ($filter)
			{
				// clean filter variables
				$filter 		= JString::strtolower($filter);
				$filter			= $this->_db->Quote( '%'.$this->_db->getEscaped( $filter, true ).'%', false );
				$filter_type 	= JString::strtolower($filter_type);

				switch ($filter_type)
				{
					case 'title' :
						$where .= ' AND LOWER( a.title ) LIKE '.$filter;
						break;

					case 'venue' :
						$where .= ' AND LOWER( l.venue ) LIKE '.$filter;
						break;

					case 'city' :
						$where .= ' AND LOWER( l.city ) LIKE '.$filter;
						break;
						
					case 'type' :
						$where .= ' AND LOWER( c.catname ) LIKE '.$filter;
						break;
				}
			}
		}
		return $where;
	}
	
	/**
	 * Return date
	 *
	 * @access public
	 * @return string
	 */
	function getDay()
	{
		return $this->_date;
	}
}
?>