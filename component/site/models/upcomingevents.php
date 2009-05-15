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
 * EventList Component Details Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelUpcomingevents extends JModel {
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct() {
		parent::__construct();
	}
	
	public function getUpcomingEvents() {
		global $mainframe;
		
		$db = JFactory::getDBO();
		$params = $mainframe->getParams();
		
		$q = ' SELECT e.*, IF (x.course_credit = 0, "", x.course_credit) AS course_credit, x.course_price, x.id AS xref, '
		   . ' x.dates, x.enddates, x.times, x.endtimes, '
		   . ' v.venue, x.venueid, v.city AS location, v.id AS venueid,	v.country '
		   . ' FROM #__redevent_venues v '
		   . ' LEFT JOIN #__redevent_event_venue_xref x	ON x.venueid = v.id '
		   . ' LEFT JOIN #__redevent_events e	ON x.eventid = e.id '
		   . ' WHERE x.published = 1 '
		   . ' AND (x.dates > NOW() AND x.dates < DATE_ADD(NOW(), INTERVAL '.$params->getValue('upcoming_days_ahead', 30).' DAY) '
		   ;
		if ($params->getValue('show_days_no_date', 0) == 1) $q .= "OR x.dates = '0000-00-00' ";
		$q .= ") ORDER BY x.dates ";
		$q .= "LIMIT ".$params->getValue('show_number_courses', 10);
		$db->setQuery($q);
		return $db->loadObjectList();
	}
}
?>