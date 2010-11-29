<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redFORM
 * @copyright redFORM (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
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
defined( '_JEXEC' ) or die( 'Restricted access' );
 
// Import library dependencies
jimport('joomla.plugin');

// load language file for frontend
JPlugin::loadLanguage( 'plg_redform_integration_redevent', JPATH_ADMINISTRATOR );

class plgRedform_integrationRedevent extends JPlugin {
 	
	private $_db = null;
	
	public function plgRedform_integrationRedevent(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
		
		$this->_db = &Jfactory::getDBO();
	}

	/**
	 * returns a title for the object reference in redform
	 * 
	 * @param string object_key should be 'redevent' for this plugin to do something
	 * @param int submitter_id
	 * @param string title string to update
	 */
	public function getRFSubmissionTitle($object_key, $submitter_id, &$title)
	{
		if (!$object_key === 'redevent') {
			return false;
		}
		
		$query = ' SELECT e.title, x.dates, x.enddates, x.times, x.endtimes, '
		       . ' v.venue ' 
		       . ' FROM #__redevent_event_venue_xref AS x ' 
		       . ' INNER JOIN #__redevent_events AS e ON e.id = x.eventid '
		       . ' LEFT JOIN #__redevent_venues AS v ON v.id = x.venueid '
		       . ' INNER JOIN #__redevent_register AS r ON r.xref = x.id '
		       . ' WHERE r.sid = ' . $this->_db->Quote($submitter_id);
		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();
		
		if (!$res) {
			$title = 'redevent-'.$reference;
		}
		else 
		{
			if ($res->dates && strtotime($res->dates))
			{
				if ($res->times && $res->times != '00:00:00') {
					$date = strftime('%c', strtotime($res->dates.' '.$res->times));
				}
				else {
					$date = strftime('%x', strtotime($res->dates));
				}
			}
			else {
				$date = JText::_('PLG_REDFORM_INTEGRATION_REDFORM_OPEN_DATE');
			}
			$title = $res->title.' @ '.$res->venue.' '.$date;
		}
		return true;
	}
}
?>
