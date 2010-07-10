<?php
/**
 * @version 1.0 $Id: eventlist.php 1027 2009-09-27 21:50:56Z julien $
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
 * redEVENT Component Attendees Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		2.0
 */
class RedEventModelAttendees extends JModel
{
	function getReminderEvents($days = 14)
	{
		$app = &JFactory::getApplication();
		$params = $app->getParams('com_redevent');
		
		$query = ' SELECT x.id, e.title '
		       . ' FROM #__redevent_events AS e '
		       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
		       . ' WHERE DATEDIFF(x.dates, NOW()) = '.$days
//		       . '   AND (e.reminder = 2'.($params->get('reminder_default', 1) == 1 ? ' OR e.reminder = 0 ' : '' ).') '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;			
	}
	
	function getAttendeesEmails($xref)
	{		
		$query = ' SELECT r.sid '
		       . ' FROM #__redevent_register AS r '
		       . ' WHERE r.xref = '.$xref;
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadResultArray();
		
		if (!count($res)) {
			return false;
		}
		
		$emails = array();
		$rfcore = new RedFormCore();
		$answers = $rfcore->getSidsFieldsAnswers($res);
		foreach ($answers as $a)
		{
			foreach ($a as $field)
			{
				if ($field->fieldtype == 'email')
				{
					$emails[] = $field->answer;
					break;
				}
			}
		}
		return $emails;
	}
}