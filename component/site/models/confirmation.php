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

require_once JPATH_COMPONENT.DS.'classes'.DS.'attendee.class.php';

/**
 * EventList Component Details Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelConfirmation extends JModel
{
	public function getDetails() {
		$db = JFactory::getDBO();
		
		/* Load registration details */
		$q = "SELECT *
			FROM #__redevent_register r
			LEFT JOIN #__redevent_event_venue_xref x
			ON r.xref = x.id
			LEFT JOIN #__redevent_events e
			ON e.id = x.eventid
			WHERE submit_key = ".$db->Quote(JRequest::getVar('submit_key'));
		$db->setQuery($q);
		$details['event'] = $db->loadObject();
		
		/* Load venue details */
		$q = "SELECT *
			FROM #__redevent_venues
			WHERE id = (SELECT venueid FROM #__redevent_event_venue_xref WHERE id = ".$details['event']->xref.")";
		$db->setQuery($q);
		$details['venue'] = $db->loadObject();
		
		/* Load all subscribers */
		$q = "SELECT *
			FROM #__rwf_submitters
			WHERE submit_key = ".$db->Quote(JRequest::getVar('submit_key'));
		$db->setQuery($q);
		$details['submitters'] = $db->loadObjectList();
		
		/* Load the fields */
		$q = "SELECT field, REPLACE(LOWER(field), ' ', '') AS rawfield
			FROM #__rwf_fields
			WHERE form_id = ".$details['event']->redform_id;
		$db->setQuery($q);
		$details['fields'] = $db->loadObjectList('rawfield');
		
		/* Load all the answers */
		foreach ($details['submitters'] AS $key => $submitter) {
			$q = "SELECT *
				FROM #__rwf_forms_".$submitter->form_id."
				WHERE id = ".$submitter->answer_id;
			$db->setQuery($q);
			$details['answers'][$key] = $db->loadObject();
		}
		
		return $details;
	}
		
	/**
	 * Check the submit key
	 */
	public function getCheckSubmitKey() 
	{
		$db = JFactory::getDBO();
		$submit_key = JRequest::getVar('submit_key');
		
		/* Load registration details */
		$q = "SELECT submit_key
			FROM #__redevent_register
			WHERE submit_key = ".$db->Quote($submit_key);
		$db->setQuery($q);
		$result = $db->loadResult();
		
		if ($result == $submit_key) {
			return true;
		}
    if ($result == null) {
      RedeventHelperLog::simpleLog('Confirm submit_key Query error: ' . $db->getErrorMsg());  
      return false;  
    }
		else {
			RedeventHelperLog::simpleLog('No registration found for key ' . $submit_key);
			return false;
		}
	}
}
?>