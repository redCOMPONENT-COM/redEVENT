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

jimport('joomla.application.component.modeladmin');

/**
 * EventList Component Event Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		0.9
*/
class RedEventModelEvent extends JModelAdmin
{
	/**
	 * Event id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Event data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Categories data array
	 *
	 * @var array
	 */
	var $_categories = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		$this->setId($cid[0]);
	}

	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int event identifier
	 */
	function setId($id)
	{
		// Set event id and wipe data
		$this->_id	    = $id;
		$this->_data	= null;
	}

	/**
	 * Logic for the event edit screen
	 *
	 */
	function &getData()
	{

		if ($this->_loadData())
		{

		}
		else  $this->_initData();

		return $this->_data;
	}

	/**
	 * Method to load content event data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			if (!$this->_id) {
				return false;
			}
			$db = &JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('e.*, v.venue');
			$query->from('#__redevent_events AS e');
			$query->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = e.id');
			$query->join('LEFT', '#__redevent_venues AS v ON v.id = x.venueid');
			$query->where('e.id = '.$this->_id);

			$db->setQuery($query);
			$this->_data = $db->loadObject();

			if ($this->_data) {
				$categories = & $this->getEventCategories();
				$this->_data->categories_ids = array_keys($categories);
				$this->_data->attachments = REAttach::getAttachments('event'.$this->_data->id);
			}
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to get the category data
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function &getEventCategories()
	{
		$query = ' SELECT c.id, c.catname '
		. ' FROM #__redevent_categories as c '
		. ' INNER JOIN #__redevent_event_category_xref as x ON x.category_id = c.id '
		. ' WHERE x.event_id = ' . $this->_db->Quote($this->_id)
		;
		$this->_db->setQuery( $query );

		$this->_categories = $this->_db->loadObjectList('id');

		return $this->_categories;
	}

	/**
	 * Get a option list of all categories
	 */
	public function getCategories()
	{
		return ELAdmin::getCategoriesOptions();
	}

	/**
	 * Method to initialise the event data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$params = &JComponentHelper::getParams( 'com_redevent' );

			if ($params->get('default_content', 0))
			{
				$id = (int) $params->get('default_content', 0);
				$query = 'SELECT e.* '
				. ' FROM #__redevent_events AS e'
				. ' WHERE e.id = '.$id
				;
				$this->_db->setQuery($query);
				$event = $this->_db->loadObject();
				if (!empty($event))
				{
					$event->id              = 0;
					$event->title						= null;
					$event->alias						= null;
					$event->categories			= null;
					$event->categories_ids  = null;
					$event->created						= null;
					$event->author_ip					= null;
					$event->created_by					= null;
					$this->_data = $event;
					return (boolean) $this->_data;
				}
			}

			$event = new stdClass();
			$event->id							= 0;
			$event->locid						= 0;
			$event->categories			= null;
			$event->categories_ids      = null;
			$event->dates						= null;
			$event->enddates					= null;
			$event->times						= null;
			$event->endtimes					= null;
			$event->title						= null;
			$event->alias						= null;
			$event->created						= null;
			$event->author_ip					= null;
			$event->created_by					= null;
			$event->published					= 1;
			$event->registra					= 1;
			$event->unregistra					= 0;
			$event->summary           = null;
			$event->datdescription    = null;
			$event->meta_keywords				= null;
			$event->meta_description			= null;
			$event->datimage					= null;
			$event->venue						= JText::_('COM_REDEVENT_SELECTVENUE');
			$event->maxattendees				= 0;
			$event->maxwaitinglist				= 0;
			$event->notify_on_list_subject 		= null;
			$event->notify_on_list_body 		= null;
			$event->notify_off_list_subject	 	= null;
			$event->notify_off_list_body 		= null;
			$event->notify_confirm_subject 		= null;
			$event->notify_confirm_body 		= null;
			$event->juser						= false;
			$event->notify						= false;
			$event->notify_subject 				= null;
			$event->notify_body 				= null;
			$event->review_message 				= null;
			$event->confirmation_message 		= null;
			$event->redform_id					= null;
			$event->activate					= null;
			$event->show_names					= 0;
			$event->showfields					= '';
			$event->course_credit				= 0;
			$event->course_code					= 0;
			$event->submission_types			= null;
			$event->submission_type_email		= null;
			$event->submission_type_external	= null;
			$event->submission_type_phone		= null;
			$event->max_multi_signup			= 1;
			$event->submission_type_formal_offer				= null;
			$event->submission_type_formal_offer_subject		= null;
			$event->submission_type_formal_offer_body		= null;
			$event->submission_type_email_body		= null;
			$event->submission_type_email_pdf		= null;
			$event->submission_type_formal_offer_pdf = null;
			$event->submission_type_webform = null;
			$event->submission_type_email_subject = null;
			$event->submission_type_webform_formal_offer = null;
			$event->show_submission_type_webform_formal_offer = 0;
			$event->send_pdf_form = 0;
			$event->pdf_form_data = 0;
			$event->paymentaccepted = null;
			$event->paymentprocessing = null;
			$event->attachments = array();
			$event->details_layout = 0;
			$event->enable_ical = 0;
			$this->_data						= $event;
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to checkin/unlock the item
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function checkin()
	{
		if ($this->_id)
		{
			$event = & JTable::getInstance('redevent_events', '');
			return $event->checkin($this->_id);
		}
		return false;
	}

	/**
	 * Method to checkout/lock the item
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the item out
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function checkout($uid = null)
	{
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the event with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$event = & JTable::getInstance('redevent_events', '');
			return $event->checkout($uid, $this->_id);
		}
		return false;
	}

	/**
	 * Tests if the event is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	0.9
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_loadData())
		{
			if ($uid) {
				return ($this->_data->checked_out && $this->_data->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		} elseif ($this->_id < 1) {
			return false;
		} else {
			RedeventError::raiseWarning( 0, 'Unable to Load Data');
			return false;
		}
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
		REAttach::store('event'.$row->id);

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
		$db = JFactory::getDBO();
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
		$db = JFactory::getDBO();
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
		$db = JFactory::getDBO();
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
		$db = JFactory::getDBO();
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
			$field = redEVENTHelper::getCustomField($c->type);
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

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'redevent_events', $prefix = '', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_redevent.event', 'event',
		array('load_data' => $loadData) );
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.7
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_redevent.edit.event.data', array());
		if (empty($data))
		{
			$data = $this->getData();
		}
		return $data;
	}
}
