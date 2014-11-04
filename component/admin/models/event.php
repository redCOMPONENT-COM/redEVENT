<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Event
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventModelEvent extends RModelAdmin
{
	/**
	 * Method to get a single record.
	 *
	 * @param   int  $pk  Record Id
	 *
	 * @return  mixed
	 */
	public function getItem($pk = null)
	{
		$result = parent::getItem($pk);

		if ($result)
		{
			$helper = new RedeventHelperAttachment;
			$files = $helper->getAttachments('event' . $result->id);
			$result->attachments = $files;

			$categories = $this->getEventCategories($result->id);
			$result->categories = array_keys($categories);
		}

		return $result;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{
		$result = parent::save($data);

		if ($result)
		{
			// Attachments
			$helper = new RedeventHelperAttachment;
			$helper->store('event' . $this->getState($this->getName() . '.id'));
		}

		return $result;
	}

	/**
	 * Method to get the category data
	 *
	 * @param   int  $eventId  event id
	 *
	 * @return array
	 */
	private function getEventCategories($eventId)
	{
		$query = $this->_db->getQuery(true);

		$query->select('c.id, c.name')
			->from('#__redevent_categories as c')
			->join('INNER', '#__redevent_event_category_xref as x ON x.category_id = c.id')
			->where('x.event_id = ' . (int) $eventId);

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList('id');

		return $res;
	}

	/**
	 * Method to store the event
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		$mainframe = &JFactory::getApplication();

		// triggers for smart search
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('finder');

		$elsettings = JComponentHelper::getParams('com_redevent');
		$user		= & JFactory::getUser();

		$tzoffset 	= $mainframe->getCfg('offset');

		$row =& JTable::getInstance('redevent_events', '');


		// Bind the form fields to the table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// Check/sanitize the metatags
		$row->meta_description = htmlspecialchars(trim(addslashes($row->meta_description)));
		if (JString::strlen($row->meta_description) > 255) {
			$row->meta_description = JString::substr($row->meta_description, 0, 254);
		}

		$row->meta_keywords = htmlspecialchars(trim(addslashes($row->meta_keywords)));
		if (JString::strlen($row->meta_keywords) > 200) {
			$row->meta_keywords = JString::substr($row->meta_keywords, 0, 199);
		}

		//Check if image was selected
		jimport('joomla.filesystem.file');
		if ($row->datimage)
		{
			$format 	= strtolower(JFile::getExt($row->datimage));

			$allowable 	= array ('gif', 'jpg', 'png');
			if (in_array($format, $allowable)) {
				$row->datimage = $row->datimage;
			} else {
				$mainframe->enqueueMessage(JText::_('COM_REDEVENT_IMAGE_FORMAT_NOT_ALLOWED').': '.$format);
				$row->datimage = '';
			}
		}

		// sanitise id field
		$row->id = (int) $row->id;

		$nullDate	= $this->_db->getNullDate();

		// Are we saving from an item edit?
		if ($row->id) {
			$row->modified 		= gmdate('Y-m-d H:i:s');
			$row->modified_by 	= $user->get('id');
			$row->created_by		= $row->created_by ? $row->created_by : $user->get('id');
			$isNew = false;
		} else {
			$row->modified 		= $nullDate;
			$row->modified_by 	= '';

			//get IP, time and userid
			$row->created 			= gmdate('Y-m-d H:i:s');

			$row->author_ip 		= $elsettings->get('storeip', '1') ? getenv('REMOTE_ADDR') : 'DISABLED';
			$row->created_by		= $row->created_by ? $row->created_by : $user->get('id');
			$isNew = true;
		}

		// Make sure the data is valid
		if (!$row->check($elsettings)) {
			$this->setError($row->getError());
			return false;
		}

		// Trigger the onFinderBeforeSave event.
		$results = $dispatcher->trigger('onFinderBeforeSave', array($this->option . '.' . $this->name, $row, $isNew));

		// Store the table to the database
		if (!$row->store(true)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// update the event category xref
		// first, delete current rows for this event
		$query = ' DELETE FROM #__redevent_event_category_xref WHERE event_id = ' . $this->_db->Quote($row->id);
		$this->_db->setQuery($query);
		if (!$this->_db->query()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// insert new ref
		foreach ((array) $data['categories'] as $cat_id) {
			$query = ' INSERT INTO #__redevent_event_category_xref (event_id, category_id) VALUES (' . $this->_db->Quote($row->id) . ', '. $this->_db->Quote($cat_id) . ')';
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		// attachments
		RedeventHelperAttachment::store('event'.$row->id);

		// Trigger the onFinderAfterSave event.
		$results = $dispatcher->trigger('onFinderAfterSave', array($this->option . '.' . $this->name, $row, $isNew));

		return $row->id;
	}

	/**
	 * Check if redFORM is installed
	 */
	public function getCheckredFORM()
	{
		return JComponentHelper::isEnabled('com_redform', true);
	}

	/**
	 * Function to retrieve the form fields
	 */
	function getFormFields()
	{
		$db = $this->_db;
		$q = "SELECT id, field
		, CASE WHEN (CHAR_LENGTH(field_header) > 0) THEN field_header ELSE field END AS field_header
		FROM #__rwf_fields
		WHERE form_id = ".$this->_data->redform_id."
		AND published = 1
		ORDER BY ordering";
		$db->setQuery($q);
		if ($db->query()) return $db->loadObjectList('id');
		else return false;
	}

	/**
	 * Function to retrieve the redFORM forms
	 */
	function getRedForms()
	{
		$db = $this->_db;
		$q = "SELECT id, formname
		FROM #__rwf_forms
		ORDER BY formname";
		$db->setQuery($q);
		if ($db->query()) return $db->loadObjectList('id');
		else return false;
	}

	/**
	 * Retrieve a list of venues
	 */
	public function getVenues()
	{
		$db = $this->_db;
		$q = "SELECT id, venue
		FROM #__redevent_venues
		ORDER BY venue";
		$db->setQuery($q);
		return $db->loadObjectList();
	}

	/**
	 * Retrieve a list of events, venues and times
	 */
	public function getEventVenue()
	{
		$db = $this->_db;
		$q = "SELECT x.*
		FROM #__redevent_event_venue_xref x
		WHERE eventid = ".$this->_id."
		ORDER BY dates";
		$db->setQuery($q);
		$datetimes = $db->loadObjectList();
		$ardatetimes = array();
		foreach ($datetimes as $key => $datetime) {
			$ardatetimes[$datetime->venueid][] = $datetime;
		}
		return $ardatetimes;
	}

	/**
	 * Retrieve a list of events, venues and times
	 */
	public function getXrefs()
	{
		if (!$this->_id) {
			return false;
		}
		$db = & $this->_db;
		$q = ' SELECT x.*, v.venue '
		. ' FROM #__redevent_event_venue_xref AS x '
		. ' INNER JOIN #__redevent_venues AS v ON x.venueid = v.id '
		. ' WHERE eventid = '.$this->_id
		. ' ORDER BY dates';
		$db->setQuery($q);
		return $db->loadObjectList();
	}

	/**
	 * get custom fields
	 *
	 * @return objects array
	 */
	public function getCustomfields()
	{
		$query = ' SELECT f.* '
		. ' FROM #__redevent_fields AS f '
		. ' WHERE f.object_key = '. $this->_db->Quote("redevent.event")
		. ' ORDER BY f.ordering '
		;
		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();

		if (!$result)
		{
			return array();
		}

		$fields = array();
		$data = $this->getData();

		foreach ($result as $c)
		{
			$field = RedeventFactoryCustomfield::getField($c->type);
			$field->bind($c);
			$prop = 'custom'.$c->id;

			if (isset($data->$prop))
			{
				$field->value = $data->$prop;
			}

			$fields[] = $field;
		}
		return $fields;
	}

	/**
	 * check whether there are attendees registered to any session of this event
	 *
	 * @return boolean
	 */
	function hasAttendees()
	{
		if (!$this->_id) {
			return false;
		}
		$query = ' SELECT r.id '
		. ' FROM #__redevent_register AS r '
		. ' INNER JOIN #__redevent_event_venue_xref AS x ON r.xref = x.id '
		. ' INNER JOIN #__redevent_events AS e ON e.id = x.eventid '
		. ' WHERE e.id = ' . $this->_db->Quote($this->_id);
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();
		return $res ? true : false;
	}
}
