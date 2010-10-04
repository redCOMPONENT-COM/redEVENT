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
	
	public function getUpcomingEvents() 
	{
		global $mainframe;
		
		$db = JFactory::getDBO();
		$params = $mainframe->getParams();
		
		$acl = &UserAcl::getInstance();
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);
		
		$q = ' SELECT e.*, IF (x.course_credit = 0, "", x.course_credit) AS course_credit, x.course_price, x.id AS xref, '
		   . ' x.dates, x.enddates, x.times, x.endtimes, '
		   . ' v.venue, x.venueid, v.city AS location, v.id AS venueid,	v.country, '
       . ' CASE WHEN CHAR_LENGTH(e.alias) THEN CONCAT_WS(\':\', e.id, e.alias) ELSE e.id END as slug, '
       . ' CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(\':\', v.id, v.alias) ELSE v.id END as venueslug '
       
		   . ' FROM #__redevent_venues v '
		   . ' LEFT JOIN #__redevent_venue_category_xref AS xvcat ON v.id = xvcat.venue_id'
		   . ' LEFT JOIN #__redevent_venues_categories AS vc ON xvcat.category_id = vc.id'
		   . ' LEFT JOIN #__redevent_event_venue_xref x	ON x.venueid = v.id '
		   . ' LEFT JOIN #__redevent_events e	ON x.eventid = e.id '
		   . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id'
		   . ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id '
		   
		   . ' LEFT JOIN #__redevent_groups_venues AS gv ON gv.venue_id = v.id AND gv.group_id IN ('.$gids.')'
		   . ' LEFT JOIN #__redevent_groups_venues_categories AS gvc ON gvc.category_id = vc.id AND gvc.group_id IN ('.$gids.')'
		   . ' LEFT JOIN #__redevent_groups_categories AS gc ON gc.category_id = c.id AND gc.group_id IN ('.$gids.')'
		   
		   . ' WHERE x.published = 1 '
		   . '   AND ((CASE WHEN x.times THEN CONCAT(x.dates, " ", x.times) ELSE x.dates END) > NOW() AND x.dates < DATE_ADD(NOW(), INTERVAL '.$params->getValue('upcoming_days_ahead', 30).' DAY) '
		   . '   AND (v.private = 0 OR gv.id IS NOT NULL) '
		   . '   AND (c.private = 0 OR gc.id IS NOT NULL) '
		   . '   AND (vc.private = 0 OR vc.private IS NULL OR gvc.id IS NOT NULL) '
		   ;
		if ($params->getValue('show_days_no_date', 0) == 1) $q .= "OR x.dates = '0000-00-00' ";
		$q .= ") ORDER BY x.dates ";
		$q .= "LIMIT ".$params->getValue('show_number_courses', 10);
		$db->setQuery($q);
		return $db->loadObjectList();
	}
}
?>