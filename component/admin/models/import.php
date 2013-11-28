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
	private $_cats   = null;
	private $_venues = null;
	private $_pgs    = null;
	private $_customsimport = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();
	}

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
    $query = ' INSERT IGNORE INTO #__redevent_categories (id, catname, alias, published, catdescription, image, meta_description, meta_keywords) '
           . ' SELECT id, catname, alias, published, catdescription, concat("images/redevent/categories/", image) AS image, meta_description, meta_keywords FROM #__eventlist_categories '
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
                 id, parent_id, catname, alias, published, catdescription, image, ordering,
                 meta_description, meta_keywords) '
           . ' SELECT id, parent_id, catname, alias, published, catdescription, concat("images/redevent/categories/", image) AS image, ordering,
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

	/**
	 * insert events/sessions in database
	 *
	 * @param array $records
	 * @param string $duplicate_method method for handling duplicate record (ignore, create_new, update)
	 * @return boolean true on success
	 */
	public function eventsimport($records, $duplicate_method = 'ignore')
	{
		$app = JFactory::getApplication();
		$count = array('added' => 0, 'updated' => 0, 'ignored' => 0);

		$current = null; // current event for sessions
		foreach ($records as $r)
		{
			$this->_replaceCustoms($r);

			// first import the event if new event
			if (!empty($r->title)) // new event
			{
				$ev = $this->getTable('RedEvent_events', '');
				if (isset($r->id) && $r->id)
				{
					// load existing data
					$found = $ev->load($r->id);

					// discard if set to ignore duplicate
					if ($found && $duplicate_method == 'ignore') {
						$count['ignored']++;
						continue;
					}
				}
				// bind submitted data
				$ev->bind($r);
				if ($duplicate_method == 'update' && $found) {
					$updating = 1;
				}
				else {
					$ev->id = null; // to be sure to create a new record
					$updating = 0;
				}

				// store !
				if (!$ev->check()) {
					$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR').': '.$ev->getError(), 'error');
					continue;
				}
				if (!$ev->store()) {
					$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR').': '.$ev->getError(), 'error');
					continue;
				}

				// Trigger plugins
				JPluginHelper::importPlugin('redevent');
				$dispatcher =& JDispatcher::getInstance();
				$res = $dispatcher->trigger('onAfterEventSaved', array($ev->id));

				// categories relations
				$cats = explode('#!#', $r->categories_names);
				$cats_ids = array();
				foreach ($cats as $c)
				{
					$cats_ids[] = $this->_getCatId($c);
				}
				$ev->setCats($cats_ids);

				($updating ? $count['updated']++ : $count['added']++);
				$current = $ev;
			}

			// import session part
			if (isset($r->xref) && $r->xref)
			{
				$venueid = $this->_getVenueId($r->venue, $r->city);

				$session = $this->getTable('RedEvent_eventvenuexref', '');
				$session->bind($r);
				$session->id = null;
				$session->eventid = $current->id;
				$session->venueid = $venueid;
				// renamed fields
				if (isset($r->session_title)) {
					$session->title = $r->session_title;
				}
				if (isset($r->session_alias)) {
					$session->alias = $r->session_alias;
				}
				if (isset($r->session_note)) {
					$session->note = $r->session_note;
				}
				if (isset($r->session_details)) {
					$session->details = $r->session_details;
				}
				if (isset($r->session_icaldetails)) {
					$session->icaldetails = $r->session_icaldetails;
				}
				if (isset($r->session_published)) {
					$session->published = $r->session_published;
				}

				// store !
				if (!$session->check()) {
					$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR').': '.$session->getError(), 'error');
					continue;
				}
				if (!$session->store()) {
					$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR').': '.$session->getError(), 'error');
					continue;
				}

				// Trigger plugins
				JPluginHelper::importPlugin('redevent');
				$dispatcher =& JDispatcher::getInstance();
				$res = $dispatcher->trigger('onAfterSessionSaved', array($session->id));

				// import pricegroups
				$pgs = explode('#!#', $r->pricegroups_names);
				$prices = explode('#!#', $r->prices);
				$currencies = explode('#!#', $r->currencies);
				$pricegroups = array();
				foreach ($pgs as $k => $v)
				{
					if (empty($v)) {
						continue;
					}
					$price = new stdclass();
					$price->pricegroup_id    = $this->_getPgId($v);
					$price->price = $prices[$k];
					$price->currency = $prices[$k];
					$pricegroups[] = $price;
				}
				$session->setPrices($pricegroups);
			}
		}
		return $count;
	}

	/**
	 * Return cat id matching name, creating if needed
	 *
	 * @param string $name
	 * @return id cat id
	 */
	private function _getCatId($name)
	{
		$id = array_search($name, $this->_getCats());
		if ($id === false) // doesn't exist, create it
		{
			$new = JTable::getInstance('RedEvent_categories', '');
			$new->catname = $name;
			$new->store();
			$id = $new->id;
			$this->_cats[$id] = $name;
		}
		return $id;
	}

	/**
	 * returns array of current cats names indexed by ids
	 *
	 * @return array
	 */
	private function _getCats()
	{
		if (empty($this->_cats))
		{
			$this->_cats = array();
			$query = ' SELECT id, catname FROM #__redevent_categories ';
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();
			foreach ((array) $res as $r)
			{
				$this->_cats[$r->id] = $r->catname;
			}
		}
		return $this->_cats;
	}

	/**
	 * Return price group id matching name, creating if needed
	 *
	 * @param string $name
	 * @return id cat id
	 */
	private function _getPgId($name)
	{
		$id = array_search($name, $this->_getPricegroups());
		if ($id === false) // doesn't exist, create it
		{
			$new = JTable::getInstance('RedEvent_pricegroups', '');
			$new->name = $name;
			$new->store();
			$id = $new->id;
			$this->_pgs[$id] = $name;
		}
		return $id;
	}

	/**
	 * returns array of current cats names indexed by ids
	 *
	 * @return array
	 */
	private function _getPricegroups()
	{
		if (empty($this->_pgs))
		{
			$this->_pgs = array();
			$query = ' SELECT id, name FROM #__redevent_pricegroups ';
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();
			foreach ((array) $res as $r)
			{
				$this->_pgs[$r->id] = $r->name;
			}
		}
		return $this->_pgs;
	}

	/**
	 * Return venue id matching name, creating if needed
	 *
	 * @param string $name
	 * @return id cat id
	 */
	private function _getVenueId($name, $city)
	{
		if (empty($name)) {
			return 0;
		}
		$id = 0;
		foreach ($this->_getVenues() as $k => $v)
		{
			if ($name == $v->venue && $city == $v->city) {
				$id = $k;
				break;
			}
		}
		if (!$id) // doesn't exist, create it
		{
			$new = JTable::getInstance('RedEvent_venues', '');
			$new->venue = $name;
			$new->city  = $city;
			$new->store();
			$id = $new->id;
			$this->_venues[$id] = $new;
		}
		return $id;
	}

	/**
	 * returns array of current cats names indexed by ids
	 *
	 * @return array
	 */
	private function _getVenues()
	{
		if (empty($this->_venues))
		{
			$this->_venues = array();
			$query = ' SELECT id, venue, city FROM #__redevent_venues ';
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();
			foreach ((array) $res as $r)
			{
				$this->_venues[$r->id] = $r;
			}
		}
		return $this->_venues;
	}

	/**
	 * returns event custom fields of array as an array
	 *
	 * @param array $row
	 * @return array
	 */
	private function _replaceCustoms(&$row)
	{
		$fields = $this->_getCustoms();

		$res = array();
		foreach ($row as $col => $val)
		{
			if ($name = array_search($col, $fields)) {
				$row->$name = $row->$col;
			}
		}
		return $row;
	}

	/**
	 * return csv header names for event tags
	 *
	 * @return array
	 */
	function _getCustoms()
	{
		if (empty($this->_customsimport))
		{
			$query = ' SELECT CONCAT("custom", id) as col, CONCAT("custom_", name, "#", tag) as csvcol'
			       . ' FROM #__redevent_fields '
			       ;
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();
			$result = array();
			foreach ($res as $r) {
				$result[$r->col] = $r->csvcol;
			}
			$this->_customsimport = $result;
		}
		return $this->_customsimport;
	}
}
