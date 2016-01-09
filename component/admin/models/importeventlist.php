<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model import eventlist
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventModelImporteventlist extends RModel
{
	/**
	 * import categories, venues and events from eventlist
	 *
	 * @return array
	 */
	public function importeventlist()
	{
		// Find out eventlist version !
		if (file_exists(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_eventlist' . DS . 'eventlist.xml') && 0)
		{
			$data = JApplicationHelper::parseXMLInstallFile(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_eventlist' . DS . 'eventlist.xml');
			$version = $data['version'];
		}
		else
		{
			// Not installed, but are there eventlist table ?
			$tables = $this->_db->getTableList();

			if (in_array($this->_db->getPrefix() . 'eventlist_cats_event_relations', $tables))
			{
				$version = '1.1';
			}
			elseif (in_array($this->_db->getPrefix() . 'eventlist_events', $tables))
			{
				$version = '1.0';
			}
			else
			{
				$this->setError(JText::_('COM_REDEVENT_EVENTLIST_NOT_FOUND'));

				return false;
			}
		}

		// Make sure redevent db is empty
		$query = ' SELECT COUNT(*) FROM #__redevent_events ';
		$this->_db->setQuery($query);
		$count = $this->_db->loadResult();

		if ($count)
		{
			$this->setError(JText::_('COM_REDEVENT_EVENTLIST_IMPORT_DB_NOT_EMPTY'));

			return false;
		}

		$query = ' SELECT COUNT(*) FROM #__redevent_categories ';
		$this->_db->setQuery($query);
		$count = $this->_db->loadResult();

		if ($count)
		{
			$this->setError(JText::_('COM_REDEVENT_EVENTLIST_IMPORT_DB_NOT_EMPTY'));

			return false;
		}

		$query = ' SELECT COUNT(*) FROM #__redevent_venues ';
		$this->_db->setQuery($query);
		$count = $this->_db->loadResult();

		if ($count)
		{
			$this->setError(JText::_('COM_REDEVENT_EVENTLIST_IMPORT_DB_NOT_EMPTY'));

			return false;
		}

		if (version_compare($version, '1.1.a') > 0)
		{
			return self::_importeventlist11();
		}
		else
		{
			return self::_importeventlist10();
		}
	}

	/**
	 * import from eventlist 1.0 structure
	 *
	 * @return array
	 */
	protected function _importeventlist10()
	{
		// Import venues
		$query = ' INSERT IGNORE INTO #__redevent_venues (id, venue, alias, url, plz, published, state, street, city, '
			. ' country, locdescription, locimage, map, meta_description, meta_keywords)'
			. ' SELECT id, venue, alias, url, plz, published, state, street, city, '
			. ' country, locdescription, concat("images/redevent/venues/", locimage) AS locimage, map, meta_description, meta_keywords'
			. ' FROM #__eventlist_venues ';
		$this->_db->setQuery($query);

		if (!$this->_db->query())
		{
			$this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_VENUES'));

			return false;
		}

		$nb_venues = $this->_db->getAffectedRows();

		// Import categories
		$query = ' INSERT IGNORE INTO #__redevent_categories (id, name, alias, published, description, '
			. ' image, meta_description, meta_keywords) '
			. ' SELECT id, name, alias, published, catdescription, '
			. ' concat("images/redevent/categories/", image) AS image, meta_description, meta_keywords FROM #__eventlist_categories ';
		$this->_db->setQuery($query);

		if (!$this->_db->query())
		{
			$this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_CATEGORIES'));

			return false;
		}

		$nb_cats = $this->_db->getAffectedRows();

		// We need to rebuild the category tree
		$table = RTable::getAdminInstance('Category');
		$table->rebuildTree();

		// Then import events.... We add a [eventlist_import] tag to the description, so that people don't have to manually edit each description
		$query = ' INSERT IGNORE INTO #__redevent_events (id, title, alias, published, datdescription, '
			. ' datimage, meta_description, meta_keywords) '
			. ' SELECT id, title, alias, published, CONCAT("[eventlist_import]",datdescription), '
			. ' concat("images/redevent/events/", datimage) AS datimage, meta_description, meta_keywords FROM #__eventlist_events ';
		$this->_db->setQuery($query);

		if (!$this->_db->query())
		{
			$this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_EVENTS'));

			return false;
		}

		$nb_events = $this->_db->getAffectedRows();
		$this->addLibraryTag();

		// Corresponding xrefs
		$query = ' INSERT IGNORE INTO #__redevent_event_venue_xref (eventid, venueid, dates, enddates, times, endtimes, published) '
			. ' SELECT id AS eventid, locid AS venueid, dates, enddates, times, endtimes, published FROM #__eventlist_events ';
		$this->_db->setQuery($query);

		if (!$this->_db->query())
		{
			$this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_EVENTS_VENUESDATES'));

			return false;
		}

		// Corresponding category
		$query = ' INSERT IGNORE INTO #__redevent_event_category_xref (event_id, category_id) '
			. ' SELECT id AS eventid, catsid AS category_id FROM #__eventlist_events ';
		$this->_db->setQuery($query);

		if (!$this->_db->query())
		{
			$this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_EVENTS_CATEGORIES'));

			return false;
		}

		$result = array('events' => $nb_events, 'venues' => $nb_venues, 'categories' => $nb_cats,);

		return $result;
	}

	/**
	 * import from eventlist 1.1 db structure
	 *
	 * @return array
	 */
	protected function _importeventlist11()
	{
		// Import venues
		$query = ' INSERT IGNORE INTO #__redevent_venues (
	               id, venue, alias, url, plz, published, state, street, city, country,
	               ordering,
	               locdescription, locimage, map, meta_description, meta_keywords
	               )'
			. ' SELECT id, venue, alias, url, plz, published, state, street, city, country,
	               ordering,
	               locdescription, concat("images/redevent/venues/", locimage) AS locimage, map, meta_description, meta_keywords
	               FROM #__eventlist_venues ';
		$this->_db->setQuery($query);

		if (!$this->_db->query())
		{
			$this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_VENUES'));

			return false;
		}

		$nb_venues = $this->_db->getAffectedRows();

		// Import categories
		$query = ' INSERT IGNORE INTO #__redevent_categories (
                 id, parent_id, name, alias, published, description, image, ordering,
                 meta_description, meta_keywords) '
			. ' SELECT id, parent_id, name, alias, published, catdescription, concat("images/redevent/categories/", image) AS image,
                 ordering, meta_description, meta_keywords FROM #__eventlist_categories ';
		$this->_db->setQuery($query);

		if (!$this->_db->query())
		{
			$this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_CATEGORIES'));

			return false;
		}

		$nb_cats = $this->_db->getAffectedRows();

		// We need to rebuild the category tree
		$table = JTable::getInstance('RedEvent_categories', '');
		$table->rebuildTree();

		// Then import events.... We add a [eventlist_import] tag to the description, so that people don't have to manually edit each description
		$query = ' INSERT IGNORE INTO #__redevent_events (id, title, alias, published, datdescription, summary, '
			. ' datimage, meta_description, meta_keywords) '
			. ' SELECT id, title, alias, published, CONCAT("[eventlist_import]",datdescription), datdescription, '
			. ' concat("images/redevent/events/", datimage) AS datimage, meta_description, meta_keywords FROM #__eventlist_events ';
		$this->_db->setQuery($query);

		if (!$this->_db->query())
		{
			$this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_EVENTS'));

			return false;
		}

		$nb_events = $this->_db->getAffectedRows();
		$this->addLibraryTag();

		// Corresponding xrefs
		$query = ' INSERT IGNORE INTO #__redevent_event_venue_xref (eventid, venueid, dates, enddates, times, endtimes, published) '
			. ' SELECT id AS eventid, locid AS venueid, dates, enddates, times, endtimes, published FROM #__eventlist_events ';
		$this->_db->setQuery($query);

		if (!$this->_db->query())
		{
			$this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_EVENTS_VENUESDATES'));

			return false;
		}

		// Corresponding category
		$query = ' INSERT IGNORE INTO #__redevent_event_category_xref (event_id, category_id) '
			. ' SELECT itemid AS eventid, catid AS category_id FROM #__eventlist_cats_event_relations ';
		$this->_db->setQuery($query);

		if (!$this->_db->query())
		{
			$this->setError(JText::_('COM_REDEVENT_EVENTLIST_ERROR_IMPORTING_EVENTS_CATEGORIES'));

			return false;
		}

		$result = array('events' => $nb_events, 'venues' => $nb_venues, 'categories' => $nb_cats,);

		return $result;
	}

	/**
	 * Add a tag for eventlist imports
	 *
	 * @return bool
	 */
	private function addLibraryTag()
	{
		$query = ' SELECT id '
			. ' FROM #__redevent_textlibrary '
			. ' WHERE text_name = ' . $this->_db->Quote(eventlist_import);
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();

		if ($res)
		{
			// Already present
			return true;
		}

		$table = RTable::getAdminInstance('Textsnippet');
		$table->text_name = 'eventlist_import';
		$table->text_description = JText::_('COM_REDEVENT_IMPORT_EVENTLIST_ADDED_TAG');
		$table->text_field = JText::_('COM_REDEVENT_IMPORT_EVENTLIST_ADDED_TAG_VALUE');

		if ($table->check() && $table->store())
		{
			return true;
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(Jtext::_('COM_REDEVENT_IMPORT_EVENTLIST_ADDED_TAG_FAILED'), 'notice');

			return false;
		}
	}
}
