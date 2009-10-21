<?php
/**
 * @version 1.0 $Id: cleanup.php 298 2009-06-24 07:42:35Z julien $
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
 * Redevent Component import Model
 *
 * @package Joomla
 * @subpackage redevent
 * @since		2.0
 */
class RedEventModelImport extends JModel
{
	
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();
	}
  
	function importeventlist()
	{
	  // make sure redevent db is empty
	  $query = ' SELECT COUNT(*) FROM #__redevent_events ';
    $this->_db->setQuery($query);
	  $count = $this->_db->loadResult();
    if ($count) {
      $this->setError(JText::_('EVENTLIST IMPORT DB NOT EMPTY'));
      return false;
    }
    $query = ' SELECT COUNT(*) FROM #__redevent_categories ';
    $this->_db->setQuery($query);
    $count = $this->_db->loadResult();
    if ($count) {
      $this->setError(JText::_('EVENTLIST IMPORT DB NOT EMPTY'));
      return false;
    }
    $query = ' SELECT COUNT(*) FROM #__redevent_venues ';
    $this->_db->setQuery($query);
    $count = $this->_db->loadResult();
    if ($count) {
      $this->setError(JText::_('EVENTLIST IMPORT DB NOT EMPTY'));
      return false;
    }
	  
	  // import venues
	  $query = ' INSERT IGNORE INTO #__redevent_venues (id, venue, alias, url, plz, published, state, street, city, country, locdescription, locimage, map, meta_description, meta_keywords)'
	         . ' SELECT id, venue, alias, url, plz, published, state, street, city, country, locdescription, locimage, map, meta_description, meta_keywords FROM #__eventlist_venues '
	         ;
	  $this->_db->setQuery($query);
	  if (!$this->_db->query()) {
	    $this->setError(JText::_('EVENTLIST ERROR IMPORTING VENUES'));
	    return false;
	  }
	  $nb_venues = $this->_db->getAffectedRows();
	  
    // import categories
    $query = ' INSERT IGNORE INTO #__redevent_categories (id, catname, alias, published, catdescription, image, meta_description, meta_keywords) '
           . ' SELECT id, catname, alias, published, catdescription, image, meta_description, meta_keywords FROM #__eventlist_categories '
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError(JText::_('EVENTLIST ERROR IMPORTING CATEGORIES'));
      return false;
    }
    $nb_cats = $this->_db->getAffectedRows();
    
    // then import events....
    $query = ' INSERT IGNORE INTO #__redevent_events (id, title, alias, published, datdescription, datimage, meta_description, meta_keywords) '
           . ' SELECT id, title, alias, published, datdescription, datimage, meta_description, meta_keywords FROM #__eventlist_events '
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError(JText::_('EVENTLIST ERROR IMPORTING EVENTS'));
      return false;
    }
    $nb_events = $this->_db->getAffectedRows();
    
    // corresponding xrefs
    $query = ' INSERT IGNORE INTO #__redevent_event_venue_xref (eventid, venueid, dates, enddates, times, endtimes, published) '
           . ' SELECT id AS eventid, locid AS venueid, dates, enddates, times, endtimes, published FROM #__eventlist_events '
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError(JText::_('EVENTLIST ERROR IMPORTING EVENTS VENUESDATES'));
      return false;
    }
    
    // corresponding category
    $query = ' INSERT IGNORE INTO #__redevent_event_category_xref (event_id, category_id) '
           . ' SELECT id AS eventid, catsid AS category_id FROM #__eventlist_events '
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError(JText::_('EVENTLIST ERROR IMPORTING EVENTS CATEGORIES'));
      return false;
    }    
    
	  $result = array('events' => $nb_events, 'venues' => $nb_venues, 'categories' => $nb_cats,);
	         
	  return $result;
	}
}
?>