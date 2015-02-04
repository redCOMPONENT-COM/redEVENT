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
class RedEventModelImport extends JModelLegacy
{
	private $_cats   = null;
	private $_venues = null;
	private $_pgs    = null;
	private $_customsimport = null;

	/**
	 * import categories, venues and events from eventlist
	 */
	public function importeventlist()
	{
    // find out eventlist version !
    // is eventlist installed ?
    if (file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_eventlist'.DS.'eventlist.xml') && 0)
    {
			$data = JApplicationHelper::parseXMLInstallFile(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_eventlist'.DS.'eventlist.xml');
			$version = $data['version'];
    }
		else // not installed, but are there eventlist table ?
		{
			$tables = $this->_db->getTableList();
			if (in_array($this->_db->getPrefix().'eventlist_cats_event_relations', $tables)) {
				$version = '1.1';
			}
			else if (in_array($this->_db->getPrefix().'eventlist_events', $tables)) {
				$version = '1.0';
			}
			else {
				$this->setError(JText::_('COM_REDEVENT_EVENTLIST_NOT_FOUND'));
				return false;
			}
		}

	  // make sure redevent db is empty
	  $query = ' SELECT COUNT(*) FROM #__redevent_events ';
    $this->_db->setQuery($query);
	  $count = $this->_db->loadResult();
    if ($count) {
      $this->setError(JText::_('COM_REDEVENT_EVENTLIST_IMPORT_DB_NOT_EMPTY'));
      return false;
    }
    $query = ' SELECT COUNT(*) FROM #__redevent_categories ';
    $this->_db->setQuery($query);
    $count = $this->_db->loadResult();
    if ($count) {
      $this->setError(JText::_('COM_REDEVENT_EVENTLIST_IMPORT_DB_NOT_EMPTY'));
      return false;
    }
    $query = ' SELECT COUNT(*) FROM #__redevent_venues ';
    $this->_db->setQuery($query);
    $count = $this->_db->loadResult();
    if ($count) {
      $this->setError(JText::_('COM_REDEVENT_EVENTLIST_IMPORT_DB_NOT_EMPTY'));
      return false;
    }

    if (version_compare($version, '1.1.a') > 0) {
    	return self::_importeventlist11();
    }
    else {
    	return self::_importeventlist10();
    }
	}

	/**
	 * import from eventlist 1.0 structure
	 */
	protected function _importeventlist10()
	{
	  // import venues
	  $query = ' INSERT IGNORE INTO #__redevent_venues (id, venue, alias, url, plz, published, state, street, city, country, locdescription, locimage, map, meta_description, meta_keywords)'
	         . ' SELECT id, venue, alias, url, plz, published, state, street, city, country, locdescription, concat("images/redevent/venues/", locimage) AS locimage, map, meta_description, meta_keywords FROM #__eventlist_venues '
	         ;
	  $this->_db->setQuery($query);
	  if (!$this->_db->query()) {
	    $this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_VENUES'));
	    return false;
	  }
	  $nb_venues = $this->_db->getAffectedRows();

    // import categories
    $query = ' INSERT IGNORE INTO #__redevent_categories (id, name, alias, published, catdescription, image, meta_description, meta_keywords) '
           . ' SELECT id, name AS catname, alias, published, catdescription, concat("images/redevent/categories/", image) AS image, meta_description, meta_keywords FROM #__eventlist_categories '
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_CATEGORIES'));
      return false;
    }
    $nb_cats = $this->_db->getAffectedRows();
    // we need to rebuild the category tree
    $table = JTable::getInstance('RedEvent_categories', '');
    $table->rebuildTree();

    // then import events.... We add a [eventlist_import] tag to the description, so that people don't have to manually edit each description
    $query = ' INSERT IGNORE INTO #__redevent_events (id, title, alias, published, datdescription, datimage, meta_description, meta_keywords) '
           . ' SELECT id, title, alias, published, CONCAT("[eventlist_import]",datdescription), concat("images/redevent/events/", datimage) AS datimage, meta_description, meta_keywords FROM #__eventlist_events '
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_EVENTS'));
      return false;
    }
    $nb_events = $this->_db->getAffectedRows();
    $this->addLibraryTag();

    // corresponding xrefs
    $query = ' INSERT IGNORE INTO #__redevent_event_venue_xref (eventid, venueid, dates, enddates, times, endtimes, published) '
           . ' SELECT id AS eventid, locid AS venueid, dates, enddates, times, endtimes, published FROM #__eventlist_events '
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_EVENTS_VENUESDATES'));
      return false;
    }

    // corresponding category
    $query = ' INSERT IGNORE INTO #__redevent_event_category_xref (event_id, category_id) '
           . ' SELECT id AS eventid, catsid AS category_id FROM #__eventlist_events '
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_EVENTS_CATEGORIES'));
      return false;
    }

	  $result = array('events' => $nb_events, 'venues' => $nb_venues, 'categories' => $nb_cats,);

	  return $result;
	}

	/*
	 * import from eventlist 1.1 db structure
	 */
	protected function _importeventlist11()
	{
	  // import venues
	  $query = ' INSERT IGNORE INTO #__redevent_venues (
	               id, venue, alias, url, plz, published, state, street, city, country,
	               ordering,
	               locdescription, locimage, map, meta_description, meta_keywords
	               )'
	         . ' SELECT id, venue, alias, url, plz, published, state, street, city, country,
	               ordering,
	               locdescription, concat("images/redevent/venues/", locimage) AS locimage, map, meta_description, meta_keywords
	               FROM #__eventlist_venues '
	         ;
	  $this->_db->setQuery($query);
	  if (!$this->_db->query()) {
	    $this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_VENUES'));
	    return false;
	  }
	  $nb_venues = $this->_db->getAffectedRows();

    // import categories
    $query = ' INSERT IGNORE INTO #__redevent_categories (
                 id, parent_id, name AS catname, alias, published, catdescription, image, ordering,
                 meta_description, meta_keywords) '
           . ' SELECT id, parent_id, name AS catname, alias, published, catdescription, concat("images/redevent/categories/", image) AS image, ordering,
                 meta_description, meta_keywords FROM #__eventlist_categories '
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_CATEGORIES'));
      return false;
    }
    $nb_cats = $this->_db->getAffectedRows();
    // we need to rebuild the category tree
    $table = JTable::getInstance('RedEvent_categories', '');
    $table->rebuildTree();

    // then import events.... We add a [eventlist_import] tag to the description, so that people don't have to manually edit each description
    $query = ' INSERT IGNORE INTO #__redevent_events (id, title, alias, published, datdescription, summary, datimage, meta_description, meta_keywords) '
           . ' SELECT id, title, alias, published, CONCAT("[eventlist_import]",datdescription), datdescription, concat("images/redevent/events/", datimage) AS datimage, meta_description, meta_keywords FROM #__eventlist_events '
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_EVENTS'));
      return false;
    }
    $nb_events = $this->_db->getAffectedRows();
    $this->addLibraryTag();

    // corresponding xrefs
    $query = ' INSERT IGNORE INTO #__redevent_event_venue_xref (eventid, venueid, dates, enddates, times, endtimes, published) '
           . ' SELECT id AS eventid, locid AS venueid, dates, enddates, times, endtimes, published FROM #__eventlist_events '
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_EVENTS_VENUESDATES'));
      return false;
    }

    // corresponding category
    $query = ' INSERT IGNORE INTO #__redevent_event_category_xref (event_id, category_id) '
           . ' SELECT itemid AS eventid, catid AS category_id FROM #__eventlist_cats_event_relations '
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_EVENTS_CATEGORIES'));
      return false;
    }

	  $result = array('events' => $nb_events, 'venues' => $nb_venues, 'categories' => $nb_cats,);

	  return $result;
	}

	public function addLibraryTag()
	{
		$query = ' SELECT id '
		       . ' FROM #__redevent_textlibrary '
		       . ' WHERE text_name = ' . $this->_db->Quote(eventlist_import);
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();

		if ($res) { // already present
			return true;
		}
    $table = JTable::getInstance('TextLibrary', 'Table');
    $table->text_name        = 'eventlist_import';
    $table->text_description = JText::_('COM_REDEVENT_IMPORT_EVENTLIST_ADDED_TAG');
    $table->text_field = JText::_('COM_REDEVENT_IMPORT_EVENTLIST_ADDED_TAG_VALUE');
    if ($table->check() && $table->store()) {
	    return true;
    }
    else {
    	JError::raiseWarning(0, Jtext::_('COM_REDEVENT_IMPORT_EVENTLIST_ADDED_TAG_FAILED'));
    	return false;
    }
	}
}
